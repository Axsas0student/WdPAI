<?php

require_once 'Database.php';

class StatsRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getUserStats(int $userId): array
    {
        // XP + completed
        $stmt = $this->db->prepare('
            SELECT
                COALESCE(SUM(xp), 0) AS xp,
                COUNT(*) AS completed
            FROM quiz_attempts
            WHERE user_id = :uid
        ');
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['xp' => 0, 'completed' => 0];

        // streak liczony jako ci¹g dni zakoñczony DZISIAJ (jeœli dziœ nic nie zrobione -> streak = 0)
        $daysStmt = $this->db->prepare('
            SELECT DISTINCT (finished_at::date) AS day
            FROM quiz_attempts
            WHERE user_id = :uid
            ORDER BY day DESC
        ');
        $daysStmt->execute(['uid' => $userId]);
        $days = $daysStmt->fetchAll(PDO::FETCH_COLUMN);

        $streak = 0;
        $expected = new DateTimeImmutable('today');

        if (!empty($days) && $days[0] === $expected->format('Y-m-d')) {
            foreach ($days as $d) {
                if ($d === $expected->format('Y-m-d')) {
                    $streak++;
                    $expected = $expected->modify('-1 day');
                } else {
                    break;
                }
            }
        }

        return [
            'xp' => (int)$row['xp'],
            'completed' => (int)$row['completed'],
            'streak' => (int)$streak
        ];
    }
}

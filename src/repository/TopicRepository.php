<?php

require_once 'Database.php';

class TopicRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getAllTopicsForUser(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                t.id,
                t.name,
                t.icon,

                -- progres per user
                COALESCE(utp.progress, 0) AS progress,

                -- ile pytań ma temat
                COUNT(q.id) AS questions_count,

                -- BLOKADA: jeśli brak pytań -> locked
                (COUNT(q.id) = 0) AS is_locked_effective,

                t.sort_order
            FROM topics t
            LEFT JOIN user_topic_progress utp
                ON utp.topic_id = t.id AND utp.user_id = :uid
            LEFT JOIN questions q
                ON q.topic_id = t.id
            GROUP BY t.id, utp.progress
            ORDER BY
                -- unlocked (false) najpierw, locked (true) na końcu
                (COUNT(q.id) = 0) ASC,
                t.sort_order ASC,
                t.id ASC
        ');

        $stmt->execute(['uid' => $userId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['questions_count'] = (int)$r['questions_count'];
            $locked = $r['is_locked_effective'];
            $r['is_locked'] = ($locked === true || $locked === 1 || $locked === 't' || $locked === 'true');
            unset($r['is_locked_effective']);
        }

        return $rows;
    }
}

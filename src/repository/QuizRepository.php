<?php

require_once 'Database.php';

class QuizRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getTopic(int $topicId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name FROM topics WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $topicId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getQuestionsForTopic(int $topicId, int $limit = 10): array
    {
        $stmt = $this->db->prepare('
            SELECT id, content
            FROM questions
            WHERE topic_id = :topic_id
            ORDER BY id ASC
            LIMIT :limit
        ');
        $stmt->bindValue(':topic_id', $topicId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAnswersForQuestions(array $questionIds): array
    {
        if (empty($questionIds)) return [];

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $stmt = $this->db->prepare("
            SELECT id, question_id, content
            FROM answers
            WHERE question_id IN ($placeholders)
            ORDER BY question_id ASC, id ASC
        ");
        $stmt->execute($questionIds);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($rows as $r) {
            $qid = (int)$r['question_id'];
            if (!isset($grouped[$qid])) $grouped[$qid] = [];
            $grouped[$qid][] = $r;
        }
        return $grouped;
    }

    public function getCorrectAnswerIdsForQuestions(array $questionIds): array
    {
        if (empty($questionIds)) return [];

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $stmt = $this->db->prepare("
            SELECT question_id, id
            FROM answers
            WHERE question_id IN ($placeholders) AND is_correct = TRUE
        ");
        $stmt->execute($questionIds);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $correct = [];
        foreach ($rows as $r) {
            $correct[(int)$r['question_id']] = (int)$r['id'];
        }
        return $correct;
    }

    public function saveAttempt(int $userId, int $topicId, int $score, int $total, int $xp): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO quiz_attempts (user_id, topic_id, score, total, xp)
            VALUES (:uid, :tid, :score, :total, :xp)
            RETURNING id
        ');
        $stmt->execute([
            'uid' => $userId,
            'tid' => $topicId,
            'score' => $score,
            'total' => $total,
            'xp' => $xp
        ]);

        return (int)$stmt->fetchColumn();
    }

    public function upsertUserTopicProgress(int $userId, int $topicId, int $progress): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO user_topic_progress (user_id, topic_id, progress)
            VALUES (:uid, :tid, :prog)
            ON CONFLICT (user_id, topic_id)
            DO UPDATE SET
                progress = GREATEST(user_topic_progress.progress, EXCLUDED.progress),
                updated_at = NOW()
        ');
        $stmt->execute([
            'uid' => $userId,
            'tid' => $topicId,
            'prog' => $progress
        ]);
    }

    public function getAttemptForUser(int $attemptId, int $userId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT qa.id, qa.score, qa.total, qa.xp, qa.finished_at, t.name AS topic
            FROM quiz_attempts qa
            JOIN topics t ON t.id = qa.topic_id
            WHERE qa.id = :aid AND qa.user_id = :uid
            LIMIT 1
        ');
        $stmt->execute(['aid' => $attemptId, 'uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}

<?php

require_once 'Database.php';

class AdminRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getAllTopics(): array
    {
        $stmt = $this->db->prepare('
            SELECT id, name, sort_order
            FROM topics
            ORDER BY sort_order ASC, id ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchTopics(string $q, int $limit = 20): array
    {
        $q = trim($q);
        if ($q === '') {
            return $this->getAllTopics();
        }

        $stmt = $this->db->prepare('
            SELECT id, name, sort_order
            FROM topics
            WHERE name ILIKE :q
            ORDER BY sort_order ASC, id ASC
            LIMIT :lim
        ');
        $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTopic(string $name, int $sortOrder = 0): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO topics (name, icon, is_locked, progress, sort_order)
            VALUES (:name, :icon, FALSE, 0, :sort_order)
        ');
        $stmt->execute([
            'name' => $name,
            'icon' => 'book',
            'sort_order' => $sortOrder
        ]);
    }

    public function addQuestionWithAnswers(
        int $topicId,
        string $questionContent,
        array $answers,
        int $correctIndex
    ): void {
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare('
                INSERT INTO questions (topic_id, content)
                VALUES (:tid, :content)
                RETURNING id
            ');
            $q->execute(['tid' => $topicId, 'content' => $questionContent]);
            $questionId = (int)$q->fetchColumn();

            $a = $this->db->prepare('
                INSERT INTO answers (question_id, content, is_correct)
                VALUES (:qid, :content, :is_correct)
            ');

            for ($i = 0; $i < 4; $i++) {
                $isCorrect = ($i === $correctIndex);

                $a->bindValue(':qid', $questionId, PDO::PARAM_INT);
                $a->bindValue(':content', $answers[$i], PDO::PARAM_STR);
                $a->bindValue(':is_correct', $isCorrect, PDO::PARAM_BOOL);
                $a->execute();
            }

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

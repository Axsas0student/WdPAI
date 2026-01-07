<?php

require_once 'Database.php';

class TopicRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getAllTopics(): array
    {
        $stmt = $this->db->prepare('
            SELECT id, name, icon, is_locked, progress, sort_order
            FROM topics
            ORDER BY sort_order ASC, id ASC
        ');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

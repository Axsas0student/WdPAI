<?php

require_once 'AppController.php';
require_once 'src/repository/TopicRepository.php';

class TopicController extends AppController
{
    private TopicRepository $topicRepository;

    public function __construct()
    {
        $this->topicRepository = new TopicRepository();
    }

    public function index()
    {
        $this->requireLogin();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $topics = $this->topicRepository->getAllTopicsForUser($userId);

        $this->render('topics', [
            'topics' => $topics
        ]);
    }
}

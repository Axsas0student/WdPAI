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
        //$this->requireLogin(); // opcjonalnie: jeœli topics ma byæ tylko po zalogowaniu

        $topics = $this->topicRepository->getAllTopics();
        $this->render('topics', ['topics' => $topics]);
    }
}

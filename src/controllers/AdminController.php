<?php

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../repository/AdminRepository.php';

class AdminController extends AppController
{
    private AdminRepository $adminRepository;

    public function __construct()
    {
        $this->adminRepository = new AdminRepository();
    }

    public function index()
    {
        $this->requireAdmin();
        $this->render('admin');
    }

    public function topics()
    {
        $this->requireAdmin();

        if ($this->isPost()) {
            $name = trim($_POST['name'] ?? '');
            $sortOrder = (int)($_POST['sort_order'] ?? 0);

            if ($name === '') {
                $topics = $this->adminRepository->getAllTopics();
                $this->render('admin-topics', ['topics' => $topics, 'message' => 'Podaj nazwê tematu']);
                return;
            }

            $this->adminRepository->addTopic($name, $sortOrder);
            header('Location: /admin-topics');
            exit();
        }

        $topics = $this->adminRepository->getAllTopics();
        $this->render('admin-topics', ['topics' => $topics]);
    }

    public function questions()
    {
        $this->requireAdmin();

        if ($this->isPost()) {
            $topicId = (int)($_POST['topic_id'] ?? 0);
            $question = trim($_POST['question'] ?? '');

            $a1 = trim($_POST['a1'] ?? '');
            $a2 = trim($_POST['a2'] ?? '');
            $a3 = trim($_POST['a3'] ?? '');
            $a4 = trim($_POST['a4'] ?? '');
            $correctRaw = $_POST['correct'] ?? null;
            if ($correctRaw === null || $correctRaw === '') {
                $correct = -1;
            } else {
                $correct = (int)$correctRaw;
            }


            $topics = $this->adminRepository->getAllTopics();

            if ($topicId <= 0 || $question === '' || $a1 === '' || $a2 === '' || $a3 === '' || $a4 === '' || $correct < 0 || $correct > 3) {
                $this->render('admin-questions', [
                    'topics' => $topics,
                    'message' => 'Uzupe³nij wszystkie pola i wybierz poprawn¹ odpowiedŸ'
                ]);
                return;
            }

            $this->adminRepository->addQuestionWithAnswers(
                $topicId,
                $question,
                [$a1, $a2, $a3, $a4],
                $correct
            );

            header('Location: /admin-questions');
            exit();
        }

        $topics = $this->adminRepository->getAllTopics();
        $this->render('admin-questions', ['topics' => $topics]);
    }
}

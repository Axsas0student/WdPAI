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
        $this->ensureSession();

        $this->render('admin', [
            'csrf' => $this->csrfToken()
        ]);
    }

    public function topics()
    {
        $this->requireAdmin();
        $this->ensureSession();

        if ($this->isPost()) {
            $this->requireCsrf();

            $name = trim($_POST['name'] ?? '');
            $sortOrder = (int)($_POST['sort_order'] ?? 0);

            $topics = $this->adminRepository->getAllTopics();

            if (strlen($name) > 120) {
                $this->render('admin-topics', [
                    'topics' => $topics,
                    'message' => 'Nazwa tematu jest za d³uga',
                    'csrf' => $this->csrfToken()
                ]);
                return;
            }

            if ($name === '') {
                $this->render('admin-topics', [
                    'topics' => $topics,
                    'message' => 'Podaj nazwê tematu',
                    'csrf' => $this->csrfToken()
                ]);
                return;
            }

            $this->adminRepository->addTopic($name, $sortOrder);
            header('Location: /admin-topics');
            exit();
        }

        $topics = $this->adminRepository->getAllTopics();
        $this->render('admin-topics', [
            'topics' => $topics,
            'csrf' => $this->csrfToken()
        ]);
    }

    public function topicsSearch()
    {
        $this->requireAdmin();
        $this->ensureSession();

        $q = trim($_GET['q'] ?? '');

        if (strlen($q) > 60) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Query too long']);
            exit();
        }

        $topics = $this->adminRepository->searchTopics($q, 30);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($topics);
        exit();
    }

    public function questions()
    {
        $this->requireAdmin();
        $this->ensureSession();

        if ($this->isPost()) {
            $this->requireCsrf();

            $topicId = (int)($_POST['topic_id'] ?? 0);
            $question = trim($_POST['question'] ?? '');

            $a1 = trim($_POST['a1'] ?? '');
            $a2 = trim($_POST['a2'] ?? '');
            $a3 = trim($_POST['a3'] ?? '');
            $a4 = trim($_POST['a4'] ?? '');

            $correctRaw = $_POST['correct'] ?? null;
            $correct = ($correctRaw === null || $correctRaw === '') ? -1 : (int)$correctRaw;

            $topics = $this->adminRepository->getAllTopics();

            if (
                strlen($question) > 500 ||
                strlen($a1) > 200 ||
                strlen($a2) > 200 ||
                strlen($a3) > 200 ||
                strlen($a4) > 200
            ) {
                $this->render('admin-questions', [
                    'topics' => $topics,
                    'message' => 'Tekst jest za d³ugi (skróæ pytanie/odpowiedzi)',
                    'csrf' => $this->csrfToken()
                ]);
                return;
            }

            if (
                $topicId <= 0 ||
                $question === '' ||
                $a1 === '' || $a2 === '' || $a3 === '' || $a4 === '' ||
                $correct < 0 || $correct > 3
            ) {
                $this->render('admin-questions', [
                    'topics' => $topics,
                    'message' => 'Uzupe³nij wszystkie pola i wybierz poprawn¹ odpowiedŸ',
                    'csrf' => $this->csrfToken()
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
        $this->render('admin-questions', [
            'topics' => $topics,
            'csrf' => $this->csrfToken()
        ]);
    }
}
<?php

require_once 'AppController.php';
require_once 'src/repository/QuizRepository.php';

class QuizController extends AppController
{
    private QuizRepository $quizRepository;

    public function __construct()
    {
        $this->quizRepository = new QuizRepository();
    }

    public function start()
    {
        // na razie mo¿esz zostawiæ bez logowania
        // $this->requireLogin();

        if ($this->isGet()) {
            $topicId = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;
            if ($topicId <= 0) {
                $this->render('quiz', ['error' => 'Missing topic id. Open quiz from /topics.']);
                return;
            }

            $topic = $this->quizRepository->getTopic($topicId);
            if (!$topic) {
                $this->render('quiz', ['error' => 'Topic not found.']);
                return;
            }

            $questions = $this->quizRepository->getQuestionsForTopic($topicId, 10);
            $questionIds = array_map(fn($q) => (int)$q['id'], $questions);
            $answersByQuestion = $this->quizRepository->getAnswersForQuestions($questionIds);

            $this->render('quiz', [
                'topic' => $topic,
                'questions' => $questions,
                'answersByQuestion' => $answersByQuestion
            ]);
            return;
        }

        // POST - liczenie wyniku
        $topicId = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;
        $selected = isset($_POST['answers']) && is_array($_POST['answers']) ? $_POST['answers'] : [];

        $topic = $this->quizRepository->getTopic($topicId);
        if (!$topic) {
            $this->render('quiz', ['error' => 'Topic not found.']);
            return;
        }

        $questions = $this->quizRepository->getQuestionsForTopic($topicId, 10);
        $questionIds = array_map(fn($q) => (int)$q['id'], $questions);
        $correct = $this->quizRepository->getCorrectAnswerIdsForQuestions($questionIds);

        $score = 0;
        foreach ($questionIds as $qid) {
            $picked = isset($selected[$qid]) ? (int)$selected[$qid] : 0;
            if ($picked !== 0 && isset($correct[$qid]) && $picked === $correct[$qid]) {
                $score++;
            }
        }

        $total = count($questionIds);
        $xp = $total > 0 ? $score * 5 : 0;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['last_result'] = [
            'topic' => $topic['name'],
            'score' => $score,
            'total' => $total,
            'xp' => $xp
        ];

        header("Location: /results");
        exit();
    }

    public function results()
    {
        //$this->requireLogin();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $result = $_SESSION['last_result'] ?? null;

        $this->render('results', ['result' => $result]);
    }
}

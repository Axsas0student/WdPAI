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
        $this->requireLogin();
        $this->ensureSession();

        if ($this->isGet()) {
            $topicId = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;
            if ($topicId <= 0) {
                $this->render('quiz', [
                    'error' => 'Missing topic id. Open quiz from /topics.',
                    'csrf' => $this->csrfToken()
                ]);
                return;
            }

            $topic = $this->quizRepository->getTopic($topicId);
            if (!$topic) {
                $this->render('quiz', [
                    'error' => 'Topic not found.',
                    'csrf' => $this->csrfToken()
                ]);
                return;
            }

            $questions = $this->quizRepository->getQuestionsForTopic($topicId, 10);

            if (empty($questions)) {
                $this->render('quiz', [
                    'error' => 'No questions for this topic yet.',
                    'topic' => $topic,
                    'questions' => [],
                    'answersByQuestion' => [],
                    'csrf' => $this->csrfToken()
                ]);
                return;
            }

            $questionIds = array_map(fn($q) => (int)$q['id'], $questions);
            $answersByQuestion = $this->quizRepository->getAnswersForQuestions($questionIds);

            $this->render('quiz', [
                'topic' => $topic,
                'questions' => $questions,
                'answersByQuestion' => $answersByQuestion,
                'csrf' => $this->csrfToken()
            ]);
            return;
        }

        $this->requireCsrf();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $topicId = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;
        $selected = (isset($_POST['answers']) && is_array($_POST['answers'])) ? $_POST['answers'] : [];

        if ($userId <= 0 || $topicId <= 0) {
            $this->render('quiz', [
                'error' => 'Invalid request.',
                'csrf' => $this->csrfToken()
            ]);
            return;
        }

        if (count($selected) > 50) {
            $this->render('quiz', [
                'error' => 'Invalid request.',
                'csrf' => $this->csrfToken()
            ]);
            return;
        }

        $topic = $this->quizRepository->getTopic($topicId);
        if (!$topic) {
            $this->render('quiz', [
                'error' => 'Invalid request.',
                'csrf' => $this->csrfToken()
            ]);
            return;
        }

        $questions = $this->quizRepository->getQuestionsForTopic($topicId, 10);
        if (empty($questions)) {
            $this->render('quiz', [
                'error' => 'No questions for this topic yet.',
                'csrf' => $this->csrfToken()
            ]);
            return;
        }

        $questionIds = array_map(fn($q) => (int)$q['id'], $questions);
        $correct = $this->quizRepository->getCorrectAnswerIdsForQuestions($questionIds);

        $score = 0;
        foreach ($questionIds as $qid) {
            $picked = isset($selected[$qid]) ? (int)$selected[$qid] : 0;
            if ($picked !== 0 && isset($correct[$qid]) && $picked === (int)$correct[$qid]) {
                $score++;
            }
        }

        $total = count($questionIds);
        $xp = $score * 5;

        $attemptId = $this->quizRepository->saveAttempt($userId, $topicId, $score, $total, $xp);

        $progress = $total > 0 ? (int)round(($score / $total) * 100) : 0;
        $this->quizRepository->upsertUserTopicProgress($userId, $topicId, $progress);

        header("Location: /results?attempt=" . (int)$attemptId);
        exit();
    }

    public function results()
    {
        $this->requireLogin();
        $this->ensureSession();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $attemptId = isset($_GET['attempt']) ? (int)$_GET['attempt'] : 0;

        $attempt = null;
        if ($attemptId > 0) {
            $attempt = $this->quizRepository->getAttemptForUser($attemptId, $userId);
        }

        $this->render('results', [
            'result' => $attempt
        ]);
    }
}

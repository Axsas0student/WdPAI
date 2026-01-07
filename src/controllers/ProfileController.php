<?php

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../repository/StatsRepository.php';

class ProfileController extends AppController
{
    private StatsRepository $statsRepository;

    public function __construct()
    {
        $this->statsRepository = new StatsRepository();
    }

    public function index()
    {
        $this->requireLogin();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $firstName = $_SESSION['user_firstname'] ?? '';

        $stats = $this->statsRepository->getUserStats($userId);

        $this->render('profile', [
            'firstName' => $firstName,
            'stats' => $stats
        ]);
    }
}

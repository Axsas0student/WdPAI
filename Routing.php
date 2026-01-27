<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/TopicController.php';
require_once 'src/controllers/QuizController.php';
require_once 'src/controllers/ProfileController.php';
require_once 'src/controllers/AdminController.php';

class Routing
{
    public static $routes = [
        "login" => [
            "controller" => "SecurityController", 
            "action" => "login"
        ],
        "logout" => [
            "controller" => "SecurityController", 
            "action" => "logout"
        ],
        "register" => [
            "controller" => "SecurityController", 
            "action" => "register"
        ],
        "dashboard" => [
            "controller" => "DashboardController", 
            "action" => "index"
        ],
        "search-cards" => [
            "controller" => "DashboardController", 
            "action" => "search"
        ],

        "topics" => [
            "controller" => "TopicController", 
            "action" => "index"
        ],
        "quiz" => [
            "controller" => "QuizController", 
            "action" => "start"
        ],
        "results" => [
            "controller" => "QuizController", 
            "action" => "results"
        ],
        "profile" => [
            "controller" => "ProfileController", 
            "action" => "index"
        ],
        "admin" => [
            "controller" => "AdminController", 
            "action" => "index"
        ],
        "admin-topics" => [
            "controller" => "AdminController", 
            "action" => "topics"
        ],
        "admin-topics-search" => [
            "controller" => "AdminController", 
            "action" => "topicsSearch"
        ],
        "admin-questions" => [
            "controller" => "AdminController", 
            "action" => "questions"
        ]
    ];

    public static function run(string $path)
    {
        $path = parse_url($path, PHP_URL_PATH) ?? '';
        $path = trim($path, "/");

        if ($path === '') {
            header("Location: /topics");
            exit();
        }

        if (!isset(self::$routes[$path])) {
            http_response_code(404);
            include 'public/views/404.html';
            return;
        }

        $controller = self::$routes[$path]["controller"];
        $action = self::$routes[$path]["action"];

        $controllerObj = new $controller();
        $controllerObj->$action();
    }
}

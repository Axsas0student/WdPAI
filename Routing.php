<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/TopicController.php';
require_once 'src/controllers/QuizController.php';
require_once 'src/controllers/ProfileController.php';

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
        ]

    ];

    public static function run(string $path)
    {
        if ($path === '') {
            $path = 'login';
        }

        // brak trasy -> 404
        if (!isset(self::$routes[$path])) {
            include 'public/views/404.html';
            return;
        }

        $controller = self::$routes[$path]['controller'];
        $action = self::$routes[$path]['action'];

        // bezpieczeñstwo: kontroler nie istnieje
        if (!class_exists($controller)) {
            include 'public/views/404.html';
            return;
        }

        $controllerObj = new $controller();

        // bezpieczeñstwo: akcja nie istnieje
        if (!method_exists($controllerObj, $action)) {
            include 'public/views/404.html';
            return;
        }

        $controllerObj->$action();
    }
}

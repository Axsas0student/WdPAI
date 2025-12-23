<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';

class Routing{

	public static $routes = [
		"login" => [
			"controller" => "SecurityController",
			"action" => "login"
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
			"action" => "seatch"
		]
	];

	public static function run(string $path) {
		switch($path){
			case 'dashboard':
			case 'login':
			case 'register':
			case 'search-cards':
				$controller = Routing::$routes[$path]["controller"];
				$action = Routing::$routes[$path]["action"];
				$id=0;

				$controllerObj = new $controller;
				$controllerObj->$action();
				break;
			default:
				include 'public/views/404.html';
				break;
		}
	}
}
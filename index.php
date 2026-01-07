<?php

require_once 'Routing.php';

session_start();
//$path = trim($_SERVER['REQUEST_URI'], '/');
//$path = parse_url($path, PHP_URL_PATH);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

Routing::run($path);
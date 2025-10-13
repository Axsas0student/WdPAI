<?php

class Routing{
	public static function run(string $path): void{
		switch($path){
			case 'login':
				include 'public/views/index.html';
				break;
			case 'dashboard':
				include 'public/views/dashboard.html';
				break;
			default:
				include 'public/views/404.html';
				break;
}
	}
}
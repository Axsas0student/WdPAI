<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';

class SecurityController extends AppController{

    private $userRepository;

    public function __construct(){
        $this->userRepository = new UserRepository();
    }

	public function login(){

		if (!$this->isPost()) {
            return $this->render('login');
        }

			#TODO get data from login form
			#check if user is in Database
			#render dashboard after succesfull authentication

		$email = $_POST['email'] ?? '';
		$password = $_POST['password'] ?? '';

		 if (empty($email) || empty($password)) {
            return $this->render('login', ['messages' => 'Fill all fields']);
        }

		$this->userRepository->getUserByEmail($email);

        if (!$userRow) {
            return $this->render('login', ['messages' => 'User not found']);
        }

         if (!password_verify($password, $userRow['password'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }

        //TODO create user session, cookie, token jvt

		$url = "http://$_SERVER[HTTP_HOST]";
		header("Location: {$url}/dashboard");
	}

	public function register() {
        if (!$this->isPost()) {
            return $this->render('register');
        }

        //var_dump($_POST);

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';
        $password2 = $_POST["password2"] ?? '';
        $firstname = $_POST["firstName"] ?? '';
        $lastname = $_POST["lastName"] ?? '';

        if ($password !== $password2){
            return $this->render("register", ['messages' => 'Passwords should be the same']);
        }

        //TODO check if user with this email already exists

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userRepository->createUser(
            $email, $hashedPassword, $firstname, $lastname
        );

        return $this->render("login", ['messages' => 'User registered succesfully, please login']);
    }
};
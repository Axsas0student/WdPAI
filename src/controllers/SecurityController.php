<?php

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController extends AppController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function login()
    {
        if (!$this->isPost()) {
            return $this->render("login");
        }

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($email === "" || $password === "") {
            return $this->render("login", ["messages" => "Fill all fields"]);
        }

        $user = $this->userRepository->getUserByEmail($email);

        if (!$user) {
            return $this->render("login", ["messages" => "User not found"]);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->render("login", ["messages" => "Wrong password"]);
        }

        // sesja
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_firstname'] = $user['firstname'] ?? null;
        $_SESSION['is_logged_in'] = true;

        header("Location: /topics");
        exit();
    }

    public function register()
    {
        if (!$this->isPost()) {
            return $this->render("register");
        }

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";
        $repeatPassword = $_POST["repeatPassword"] ?? "";
        $firstName = trim($_POST["userName"] ?? "");
        $lastName = trim($_POST["surname"] ?? "");

        if ($email === "" || $password === "" || $repeatPassword === "" || $firstName === "" || $lastName === "") {
            return $this->render("register", ["messages" => "Fill all fields"]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render("register", ["messages" => "Invalid email"]);
        }

        if ($password !== $repeatPassword) {
            return $this->render("register", ["messages" => "Passwords should be the same!"]);
        }

        // czy email ju¿ istnieje
        $existing = $this->userRepository->getUserByEmail($email);
        if ($existing) {
            return $this->render("register", ["messages" => "Email already registered"]);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userRepository->createUser(
            $email,
            $hashedPassword,
            $firstName,
            $lastName
        );

        // lepiej przekierowaæ na login (unikasz powtórzenia POST)
        header("Location: /login");
        exit();
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: /login");
        exit();
    }
}

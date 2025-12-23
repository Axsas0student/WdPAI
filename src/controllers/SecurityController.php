<?php

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController extends AppController {
    private $userRepository;

    public function __construct(){
        $this->userRepository = new UserRepository();
    }
 
    private static array $users = [
        [
            'email' => 'anna@example.com',
            'password' => '$2y$10$wz2g9JrHYcF8bLGBbDkEXuJQAnl4uO9RV6cWJKcf.6uAEkhFZpU0i', // test123
            'first_name' => 'Anna'
        ],
        [
            'email' => 'bartek@example.com',
            'password' => '$2y$10$fK9rLobZK2C6rJq6B/9I6u6Udaez9CaRu7eC/0zT3pGq5piVDsElW', // haslo456
            'first_name' => 'Bartek'
        ],
        [
            'email' => 'celina@example.com',
            'password' => '$2y$10$Cq1J6YMGzRKR6XzTb3fDF.6sC6CShm8kFgEv7jJdtyWkhC1GuazJa', // qwerty
            'first_name' => 'Celina'
        ],
    ];

    public function login() {
        
        if (!$this->isPost()) {
            return $this->render("login");
        }

        $email = $_POST["email"] ?? "";
        $password = $_POST["password"];

        if (empty($email) || empty($password)) {
            return $this->render("login", ["messages" =>"fill all fields"]);
        }
        
        $user = $this->userRepository->getUserByEmail($email);

        if (!$user) {
            return $this->render('login', ['messages' => 'User not found']);
        }
        
        if (!password_verify($password, $user['password'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }

        // return $this->render("user-page");

        // create user session, cookie, token JWT

        // Tworzymy sesjê u¿ytkownika
        session_regenerate_id(true); // nowy identyfikator sesji (bezpieczeñstwo)

        $_SESSION['user_id'] = $user['id'];          // zak³adam, ¿e w tablicy $user jest klucz 'id'
        $_SESSION['user_email'] = $user['email'];    // zapamiêtujemy np. e-mail
        $_SESSION['user_firstname'] = $user['firstname'] ?? null;

        // ewentualnie mo¿esz dodaæ prost¹ flagê:
        $_SESSION['is_logged_in'] = true;

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/user-page");
    }

    public function register() {
        if (!$this->isPost()) {
            return $this->render("register");
        }
        
        $email = $_POST["email"] ?? "";
        $password = $_POST["password"] ?? "";
        $repeatPassword = $_POST["repeatPassword"] ?? "";
        $userName = $_POST["userName"] ?? "";
        $surname = $_POST["surname"] ?? "";

        // TODO CHECK IF EMAIL ALREADY EXISTS
        
        if ($password !== $repeatPassword) {
            return $this->render('register', ['messages' => 'Passwords should be the same!']);
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userRepository->createUser(
            $email, $hashedPassword, $userName, $surname
        );

        return $this->render("login", ['messages' => 'User registered successfully, please login!']);
    }

    public function logout() {
    // upewniamy siê, ¿e sesja jest uruchomiona
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // czyœcimy wszystkie dane sesji
        $_SESSION = [];

        // opcjonalnie, kasujemy ciasteczko sesji po stronie przegl¹darki
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

    // niszczymy sesjê
        session_destroy();

        // przekierowanie np. na ekran logowania
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login");
    }
}
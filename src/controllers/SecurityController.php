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
            $this->ensureSession();

            return $this->render("login", [
                'csrf' => $this->csrfToken()
            ]);
        }

        $this->ensureSession();
        $this->requireCsrf();

        $_SESSION['login_failures'] = $_SESSION['login_failures'] ?? 0;
        $_SESSION['login_lock_until'] = $_SESSION['login_lock_until'] ?? 0;

        if (time() < (int)$_SESSION['login_lock_until']) {
            return $this->render("login", [
                "messages" => "Too many attempts. Try again in a moment.",
                "csrf" => $this->csrfToken()
            ]);
        }

        $email = trim($_POST["email"] ?? "");
        $password = (string)($_POST["password"] ?? "");

        if (strlen($email) > 150 || strlen($password) > 300) {
            return $this->render("login", [
                "messages" => "Invalid email or password",
                "csrf" => $this->csrfToken()
            ]);
        }

        if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
            return $this->render("login", [
                "messages" => "Invalid email or password",
                "csrf" => $this->csrfToken()
            ]);
        }

        $user = $this->userRepository->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['login_failures'] = (int)$_SESSION['login_failures'] + 1;

            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            error_log("Failed login for email={$email} from IP={$ip}");

            if ((int)$_SESSION['login_failures'] >= 5) {
                $_SESSION['login_lock_until'] = time() + 10; // sekundy
            }

            return $this->render("login", [
                "messages" => "Invalid email or password",
                "csrf" => $this->csrfToken()
            ]);
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_firstname'] = $user['firstname'] ?? null;
        $_SESSION['is_logged_in'] = true;

        $_SESSION['is_admin'] = (bool)($user['is_admin'] ?? false);

        $_SESSION['login_failures'] = 0;
        $_SESSION['login_lock_until'] = 0;

        header("Location: /topics");
        exit();
    }

    public function register()
    {
        if (!$this->isPost()) {
            $this->ensureSession();

            return $this->render("register", [
                'csrf' => $this->csrfToken()
            ]);
        }

        $this->ensureSession();
        $this->requireCsrf();

        $email = trim($_POST["email"] ?? "");
        $password = (string)($_POST["password"] ?? "");
        $repeatPassword = (string)($_POST["repeatPassword"] ?? "");
        $firstName = trim($_POST["userName"] ?? "");
        $lastName = trim($_POST["surname"] ?? "");

        if (
            strlen($email) > 150 ||
            strlen($password) > 300 ||
            strlen($repeatPassword) > 300 ||
            strlen($firstName) > 100 ||
            strlen($lastName) > 100
        ) {
            return $this->render("register", [
                "messages" => "Invalid input",
                "csrf" => $this->csrfToken()
            ]);
        }

        if ($email === "" || $password === "" || $repeatPassword === "" || $firstName === "" || $lastName === "") {
            return $this->render("register", [
                "messages" => "Invalid input",
                "csrf" => $this->csrfToken()
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render("register", [
                "messages" => "Invalid input",
                "csrf" => $this->csrfToken()
            ]);
        }

        if (strlen($password) < 8) {
            return $this->render("register", [
                "messages" => "Password too weak (min 8 chars)",
                "csrf" => $this->csrfToken()
            ]);
        }

        if ($password !== $repeatPassword) {
            return $this->render("register", [
                "messages" => "Passwords should be the same!",
                "csrf" => $this->csrfToken()
            ]);
        }

        $exists = method_exists($this->userRepository, 'emailExists')
            ? $this->userRepository->emailExists($email)
            : (bool)$this->userRepository->getUserByEmail($email);

        if ($exists) {
            return $this->render("register", [
                "messages" => "Invalid input",
                "csrf" => $this->csrfToken()
            ]);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userRepository->createUser(
            $email,
            $hashedPassword,
            $firstName,
            $lastName
        );

        header("Location: /login");
        exit();
    }

    public function logout()
    {
        $this->ensureSession();

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"] ?? '/',
                $params["domain"] ?? '',
                $params["secure"] ?? false,
                $params["httponly"] ?? true
            );
        }

        session_destroy();

        header("Location: /login");
        exit();
    }
}

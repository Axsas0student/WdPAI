<?php


class AppController {

    protected function isGet(): bool{
        return $_SERVER["REQUEST_METHOD"] == 'GET';
    }

    protected function isPost(): bool{
        return $_SERVER["REQUEST_METHOD"] == 'POST';
    }

    protected function render(string $template = null, array $variables = [])
    {
        $templatePath = 'public/views/'. $template.'.html';
        $templatePath404 = 'public/views/404.html';
        $output = "";
                 
        if(file_exists($templatePath)){
            // ["message" => "B³êdne has³o!"]
            extract($variables);
            // $message = "B³êdne has³o!"
            //echo $message
            
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        } else {
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        echo $output;
    }
    protected function requireLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }
    }

    protected function requireAdmin() {
        $this->requireLogin();

        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        if (empty($_SESSION['is_admin'])) {
            http_response_code(403);
            echo "403 Forbidden";
            exit();
        }
    }

    protected function ensureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Cookie params (C3/D3/E3) - w DEV secure=false, w PROD ustaw true po HTTPS
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

            session_set_cookie_params([
                'httponly' => true,           // C3
                'samesite' => 'Lax',          // E3 (Lax jest bezpieczne i nie psuje logowania)
                'secure'   => $isHttps        // D3 (w³¹czone dopiero na https)
            ]);

            session_start();
        }
    }

    protected function csrfToken(): string {
        $this->ensureSession();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    protected function requireCsrf(): void {
        $this->ensureSession();
        $posted = $_POST['csrf'] ?? '';
        $session = $_SESSION['csrf'] ?? '';
        if (!$posted || !$session || !hash_equals($session, $posted)) {
            http_response_code(400); // A5
            die('Invalid request');  // nie zdradzamy szczegó³ów
        }
    }
}
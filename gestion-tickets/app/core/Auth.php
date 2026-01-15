<?php
// app/core/Auth.php
require_once __DIR__ . '/../models/User.php';

class Auth {
    public static function attempt($email, $password) {
        require_once __DIR__ . '/Logger.php';
        
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        Logger::log("Auth::attempt({$email}): user_found=" . ($user ? 'yes' : 'no'));
        
        if ($user) {
            Logger::log("  user active=" . ($user['active'] ?? 'unknown'));
            Logger::log("  password verify: " . ($userModel->verifyPassword($password, $user['password']) ? 'OK' : 'FAIL'));
        }

        if ($user && ($user['active'] ?? true) && $userModel->verifyPassword($password, $user['password'])) {
            // Regeneramos ID de sesión por seguridad
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_department_id'] = $user['department_id'] ?? null;
            
            Logger::log("Auth OK: set session for user_id=" . $user['id']);
            return true;
        }
        Logger::log("Auth FAIL for {$email}");
        return false;
    }

    public static function check() {
        return isset($_SESSION['user_id']);
    }

    public static function user() {
        if (self::check()) {
            $userModel = new User();
            return $userModel->find($_SESSION['user_id']);
        }
        return null;
    }

    public static function role() {
        return $_SESSION['user_role'] ?? 'guest';
    }

    public static function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    // Middleware rápido para proteger rutas
    public static function requireRole($roles = []) {
        if (!self::check()) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            header('Location: /gestion-tickets/login');
            exit;
        }

        if (!empty($roles) && !in_array(self::role(), (array)$roles)) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Acceso denegado'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            http_response_code(403);
            echo 'Acceso denegado';
            exit;
        }
    }
}

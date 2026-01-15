<?php
// app/controllers/AuthController.php

class AuthController extends Controller
{
    public function showLogin()
    {
        // Si ya está logueado, lo mandamos al dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        // Solo muestra la vista (tu pana ya la tiene)
        $this->view('auth/login');
    }

    public function login()
    {
        $this->onlyJson(); // Fuerza que solo sea por AJAX

        require_once __DIR__ . '/../core/Logger.php';

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            Logger::log("Login CSRF fail: token='" . substr($token, 0, 10) . "...' vs session='" . (isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 10) . "..." : "NONE") . "'");
            $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Logger::log("Login fail: empty email or password");
            $this->json(['success' => false, 'error' => 'Faltan datos']);
        }

        if (Auth::attempt($email, $password)) {
            $role = $_SESSION['user_role'];
    
            Logger::log("Login OK: {$email} ({$role})");

            // Redirección según rol (incluir base path /gestion-tickets)
            $redirects = [
                'admin' => '/gestion-tickets/admin/users',
                'agent' => '/gestion-tickets/tickets',
                'user'  => '/gestion-tickets/tickets/my-tickets'
            ];
    
            $redirect = $redirects[$role] ?? '/gestion-tickets/dashboard';

            $this->json([
                'success'  => true,
                'message'  => 'Login correcto',
                'redirect' => $redirect,
                'user' => [
                    'name' => $_SESSION['user_name'],
                    'role' => $role
                ]
            ]);

        } else {
            Logger::log("Login FAIL: {$email} (credenciales incorrectas)");
            $this->json(['success' => false, 'error' => 'Credenciales incorrectas'], 401);
        }
    }

    public function logout()
    {
        Auth::logout();
        // Si la petición fue AJAX respondemos JSON, si no redirigimos al login
        if ($this->isAjax()) {
            $this->json(['success' => true, 'redirect' => '/login']);
        } else {
            $this->redirect('/login');
        }
    }

    // Ruta rápida para verificar sesión (útil en frontend)
    public function check()
    {
        $this->onlyJson();
        $this->json([
            'logged' => Auth::check(),
            'user'   => Auth::check() ? Auth::user() : null
        ]);
    }
    public function showChangePassword()
    {
        if (Auth::check()) {
            $this->view('auth/change-password');
        } else {
            $this->redirect('/login');
        }
    }

    // Procesar cambio de contraseña
    public function changePassword()
    {
        $this->onlyJson();

        if (!Auth::check()) {
            return $this->json(['success' => false, 'error' => 'No autenticado'], 401);
        }

        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            return $this->json(['success' => false, 'error' => 'Todos los campos son obligatorios']);
        }

        if ($new !== $confirm) {
            return $this->json(['success' => false, 'error' => 'Las nuevas contraseñas no coinciden']);
        }

        if (strlen($new) < 6) {
            return $this->json(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']);
        }

        $user = (new User())->find($_SESSION['user_id']);
        if (!password_verify($current, $user['password'])) {
            return $this->json(['success' => false, 'error' => 'Contraseña actual incorrecta']);
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $success = $stmt->execute([$hash, $_SESSION['user_id']]);

        $success
            ? $this->json(['success' => true, 'message' => 'Contraseña cambiada correctamente'])
            : $this->json(['success' => false, 'error' => 'Error al cambiar contraseña'], 500);
    }
}
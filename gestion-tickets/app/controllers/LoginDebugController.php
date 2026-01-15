<?php
// app/controllers/LoginDebugController.php
// Temporal: para diagnosticar problemas de sesión y CSRF

class LoginDebugController extends Controller
{
    public function debug()
    {
        $this->json([
            'session_id' => session_id(),
            'csrf_token_in_session' => isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 10) . '...' : 'NONE',
            'csrf_token_from_helper' => substr((function() {
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                return $_SESSION['csrf_token'];
            })(), 0, 10) . '...',
            'all_session_vars' => array_keys($_SESSION),
            'php_version' => phpversion(),
            'session_status' => session_status(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

<?php
// app/helpers/csrf.php
// Helper CSRF: define funciones de token y verificación.
// NOTA: session_start() ya se llama en index.php, no lo hacemos aquí

if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('validate_csrf')) {
    function validate_csrf($token) {
        return hash_equals($_SESSION['csrf_token'] ?? '', (string)$token);
    }
}
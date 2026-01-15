<?php
/**
 * Helper para URLs y BASE_URL
 */

if (!function_exists('base_url')) {
    /**
     * Obtiene la URL base
     * @return string
     */
    function base_url() {
        if (defined('BASE_URL')) {
            return rtrim(BASE_URL, '/');
        }
        return '/gestion-tickets';
    }
}

if (!function_exists('get_csrf_token')) {
    /**
     * Obtiene el token CSRF de forma segura
     * @return string Token CSRF o string vacío si no existe
     */
    function get_csrf_token() {
        if (!empty($_SESSION['csrf_token'])) {
            return htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
        }
        return '';
    }
}
?>

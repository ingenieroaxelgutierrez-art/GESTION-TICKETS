<?php
// app/core/Controller.php

// Cargar dependencias comunes
require_once __DIR__ . '/Auth.php';

class Controller {
    protected $db;

    public function __construct() {
        // Cargar Database.php si no está cargado
        if (!class_exists('Database')) {
            require_once __DIR__ . '/Database.php';
        }
        
        $this->db = Database::getInstance()->getConnection();
    }
    // Cargar una vista
    protected function view($view, $data = []) {
        extract($data);
        
        $viewFile = dirname(dirname(__DIR__)) . "/views/{$view}.php";
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Vista no encontrada: {$view} (buscada en: {$viewFile})");
        }
    }

    // Redirigir a otra URL
    protected function redirect($url) {
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = ($basePath === '\\') ? '' : rtrim($basePath, '/');
        header("Location: {$basePath}{$url}");
        exit;
    }

    // Respuesta JSON
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validar CSRF para peticiones PUT, DELETE, PATCH
    protected function validateCsrf() {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                 $_POST['csrf_token'] ?? 
                 $_GET['csrf_token'] ?? '';
        
        if (!validate_csrf($token)) {
            $this->json(['error' => 'Token inválido', 'success' => false], 403);
        }
    }

    // Verificar si el usuario está autenticado
    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    // Verificar si el usuario es admin
    protected function requireAdmin() {
        $this->requireAuth();
        if ($_SESSION['role'] !== 'admin') {
            $this->json(['error' => 'Acceso denegado'], 403);
        }
    }

    // Verificar si la solicitud es AJAX
    protected function onlyJson() {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!$isAjax) {
            http_response_code(405);
            $this->json(['error' => 'Método no permitido. Esta ruta solo acepta peticiones AJAX'], 405);
        }
    }
    
    // Comprueba si la petición es AJAX (sin forzar respuesta)
    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    protected function parseRequestBody() {
    $method = $_SERVER['REQUEST_METHOD'];
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    // Si viene JSON
    if (strpos($contentType, 'application/json') !== false) {
        $input = file_get_contents("php://input");
        return json_decode($input, true) ?? [];
    }

    // Si viene PUT / DELETE / PATCH
    if (in_array($method, ['PUT', 'DELETE', 'PATCH'])) {
        $input = file_get_contents("php://input");
        
        // Si es multipart/form-data (FormData de JavaScript)
        if (strpos($contentType, 'multipart/form-data') !== false) {
            $boundary = substr($contentType, strpos($contentType, 'boundary=') + 9);
            $data = [];
            $parts = array_filter(array_map('trim', explode('--' . $boundary, $input)));
            
            foreach ($parts as $part) {
                if (empty($part) || $part === '--') continue;
                
                // Separar headers del contenido
                $parts_split = explode("\r\n\r\n", $part, 2);
                if (count($parts_split) !== 2) continue;
                
                $headers = $parts_split[0];
                $content = rtrim($parts_split[1], "\r\n");
                
                // Extraer nombre del campo
                if (preg_match('/name="([^"]+)"/', $headers, $matches)) {
                    $fieldName = $matches[1];
                    $data[$fieldName] = $content;
                }
            }
            return $data;
        }
        
        // Si es url-encoded
        if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            $data = [];
            parse_str($input, $data);
            return $data;
        }
        
        // Fallback: intentar parsear como url-encoded
        $data = [];
        parse_str($input, $data);
        return $data;
    }

    // POST normal o GET
    return $_POST;
}

}
<?php
// app/core/Router.php

class Router {

    private $routes = [];
    public $currentUri = '/';

    public function add($method, $path, $controllerAction) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controllerAction
        ];
    }

    // Normaliza la URI antes de procesar
    private function normalizeUri() {
        // Obtener REQUEST_URI (la ruta solicitada originalmente)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Definir la base del proyecto (donde está index.php)
        // Para /gestion-tickets/, la base es /gestion-tickets
        $base = '/gestion-tickets';
        
        // Quitar la base si está al inicio
        if (strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        // Si queda vacío o es false, es la raíz
        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        // Quitar trailing slash (excepto la raíz)
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
            if ($uri === '') $uri = '/';
        }

        $this->currentUri = $uri;
    }

    public function dispatch() {
        $this->normalizeUri();

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->currentUri;

        foreach ($this->routes as $route) {
            // Convertir {id} → regex
            $pattern = preg_replace('#\{([^}]+)\}#', '([^/]+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                list($controllerName, $action) = explode('@', $route['controller']);

                // Confiar en el autoloader registrado en index.php
                // El autoloader ya ha buscado las clases en core/ y controllers/
                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    call_user_func_array([$controller, $action], $matches);
                    return;
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => "Controlador no encontrado: {$controllerName}"]);
                    return;
                }
            }
        }

        // Si nada coincide → 404
        http_response_code(404);
        echo "<!DOCTYPE html>
<html>
<head>
    <title>404 - Página no encontrada</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e74c3c; }
        p { color: #7f8c8d; }
    </style>
</head>
<body>
    <h1>404 - Página no encontrada</h1>
    <p>La ruta <strong>{$uri}</strong> no existe.</p>
    <p>Método: <strong>{$method}</strong></p>
    <a href='/gestion-tickets/'>Volver al inicio</a>
</body>
</html>";
    }
}
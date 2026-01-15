<?php
// Configurar sesión ANTES de session_start()
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Headers de seguridad (después de session_start())
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once __DIR__ . '/app/helpers/csrf.php';

// Autoloader simple: busca clases en core/, models/ y controllers/
spl_autoload_register(function($class) {
	$paths = [
		__DIR__ . "/app/core/{$class}.php",
		__DIR__ . "/app/models/{$class}.php",
		__DIR__ . "/app/controllers/{$class}.php",
	];
	foreach ($paths as $file) {
		if (file_exists($file)) {
			require_once $file;
			return true;
		}
	}
	return false;
});

require_once __DIR__ . '/app/config/app.php';
require_once __DIR__ . '/app/config/database.php';

// Router (la clase se cargará por el autoloader)
require_once __DIR__ . '/app/core/Router.php';

$router = new Router();

require_once __DIR__ . '/app/config/routes.php';

// Dejar que el Router maneje todo
$router->dispatch();

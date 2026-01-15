<?php
// tools/check_user.php
// Comprueba si existe un usuario por email y muestra información básica.

$config = require __DIR__ . '/../app/config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    echo "Error conectando a la DB: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

$email = $argv[1] ?? 'admin@example.com';

$stmt = $pdo->prepare('SELECT id, name, email, password, role, department_id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo "Usuario no encontrado: {$email}\n";
    exit(0);
}

echo "Usuario encontrado:\n";
echo " - ID: " . $user['id'] . "\n";
echo " - Nombre: " . $user['name'] . "\n";
echo " - Email: " . $user['email'] . "\n";
echo " - Rol: " . $user['role'] . "\n";
echo " - Departamento ID: " . $user['department_id'] . "\n";
echo " - Hash de password: " . $user['password'] . "\n";

// Probar verificación rápida si el usuario imprime password ingresado
if (php_sapi_name() === 'cli') {
    $try = $argv[2] ?? null;
    if ($try) {
        if (password_verify($try, $user['password'])) {
            echo "La contraseña proporcionada ('{$try}') COINCIDE con el hash.\n";
        } else {
            echo "La contraseña proporcionada ('{$try}') NO coincide con el hash.\n";
        }
    } else {
        echo "Para probar la contraseña: php tools/check_user.php {$email} <password>\n";
    }
}

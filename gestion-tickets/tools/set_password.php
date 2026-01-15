<?php
// tools/set_password.php
// Uso: php set_password.php user@example.com newpassword

if ($argc < 3) {
    echo "Uso: php set_password.php <email> <new_password>\n";
    exit(1);
}

$email = $argv[1];
$newPassword = $argv[2];

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

$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->execute([$hash, $email]);

if ($stmt->rowCount() > 0) {
    echo "Contraseña actualizada correctamente para {$email}\n";
} else {
    echo "No se encontró usuario con email {$email} o no se actualizó la fila.\n";
}

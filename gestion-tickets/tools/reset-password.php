<?php
// tools/reset-password.php
// Resetea la contraseña de un usuario a un valor específico

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
$newPassword = $argv[2] ?? 'password';

// Generar hash correcto
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

// Actualizar usuario
$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
if ($stmt->execute([$hash, $email])) {
    echo "✅ Contraseña actualizada para {$email}\n";
    echo "   Nueva contraseña: {$newPassword}\n";
    echo "   Nuevo hash: {$hash}\n";
} else {
    echo "❌ Error actualizando la contraseña\n";
    exit(1);
}

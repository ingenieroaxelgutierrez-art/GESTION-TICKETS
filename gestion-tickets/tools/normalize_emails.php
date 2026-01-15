<?php
// tools/normalize_emails.php
// Corrige dominios mal escritos en la tabla users

require_once __DIR__ . '/../app/config/database.php';
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

// Mapa de correcciones: buscar => reemplazar (ajusta a tus dominios reales)
$fixes = [
    '@old-domain.test' => '@example.com'
];

foreach ($fixes as $bad => $good) {
    $sql = "UPDATE users SET email = REPLACE(email, ?, ?) WHERE email LIKE %?%"; // placeholder, we'll prepare differently
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email LIKE ?");
    $stmt->execute(["%" . $bad . "%"]);
    $rows = $stmt->fetchAll();
    if (empty($rows)) {
        echo "No se encontraron emails con {$bad}\n";
        continue;
    }
    foreach ($rows as $r) {
        $new = str_replace($bad, $good, $r['email']);
        $upd = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $upd->execute([$new, $r['id']]);
        echo "Updated {$r['email']} -> {$new}\n";
    }
}

echo "Normalización completada.\n";

<?php
// tools/db_check.php
// Pequeño script para verificar la conexión a la BD y listar tablas.

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

echo "Conexión OK a {$config['dbname']}@{$config['host']}" . PHP_EOL;

// Listar tablas
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($tables)) {
    echo "No se encontraron tablas en la base de datos." . PHP_EOL;
    exit(0);
}

echo "Tablas encontradas (" . count($tables) . "):\n";
foreach ($tables as $t) {
    // Obtener conteo aproximado
    try {
        $c = $pdo->query("SELECT COUNT(*) as c FROM `" . $t . "`")->fetchColumn();
    } catch (Exception $ex) {
        $c = 'N/A';
    }
    echo " - {$t} (rows: {$c})\n";
}

// Revisiones rápidas: comprobar columnas críticas
$checks = [
    'tickets' => ['closed_at','closed_reason','closed_by'],
    'users' => ['reset_token','reset_expires']
];

foreach ($checks as $table => $cols) {
    if (!in_array($table, $tables)) continue;
    echo "\nComprobando columnas en {$table}:\n";
    $res = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($cols as $col) {
        echo " - {$col}: " . (in_array($col, $res) ? 'OK' : 'MISSING') . "\n";
    }
}

echo "\nComprobación completada." . PHP_EOL;

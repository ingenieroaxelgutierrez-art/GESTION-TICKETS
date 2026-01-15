<?php
// tools/setup-db.php
// Script para crear la BD e importar el schema automáticamente

$config = require __DIR__ . '/../app/config/database.php';

// Conectar a MySQL SIN especificar BD (para poder crear la BD)
$dsn = "mysql:host={$config['host']};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ Conexión a MySQL OK\n";
} catch (Exception $e) {
    echo "❌ Error conectando a MySQL: " . $e->getMessage() . "\n";
    exit(1);
}

// Crear BD si no existe
$dbname = $config['dbname'];
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "✅ Base de datos '{$dbname}' creada/verificada\n";
} catch (Exception $e) {
    echo "❌ Error creando BD: " . $e->getMessage() . "\n";
    exit(1);
}

// Conectar a la BD
try {
    $pdo = new PDO("mysql:host={$config['host']};dbname={$dbname};charset=utf8mb4", $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ Conexión a BD '{$dbname}' OK\n";
} catch (Exception $e) {
    echo "❌ Error conectando a BD: " . $e->getMessage() . "\n";
    exit(1);
}

// Leer el archivo schema.sql
$schemaFile = __DIR__ . '/../sql/schema.sql';
if (!file_exists($schemaFile)) {
    echo "❌ Archivo sql/schema.sql no encontrado\n";
    exit(1);
}

$sql = file_get_contents($schemaFile);
echo "📄 Importando sql/schema.sql...\n";

// Dividir por ';' y ejecutar cada statement
$statements = array_filter(array_map('trim', explode(';', $sql)));
$count = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    try {
        $pdo->exec($statement);
        $count++;
    } catch (Exception $e) {
        echo "⚠️  Advertencia al ejecutar sentencia: " . $e->getMessage() . "\n";
        // Continuamos con la siguiente
    }
}

echo "✅ {$count} sentencias SQL ejecutadas correctamente\n";

// Verificar tablas
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "\n📋 Tablas creadas:\n";
foreach ($tables as $t) {
    try {
        $c = $pdo->query("SELECT COUNT(*) as cnt FROM `" . $t . "`")->fetchColumn();
    } catch (Exception $ex) {
        $c = 'N/A';
    }
    echo " - {$t} (rows: {$c})\n";
}

echo "\n✅ Configuración de BD completada. La app ya puede iniciar.\n";

<?php
// tools/view_logs.php
// Visualiza los logs de la aplicación

$logFile = __DIR__ . '/../storage/logs/app.log';

if (!file_exists($logFile)) {
    echo "No hay logs.\n";
    exit(0);
}

$lines = file($logFile);
echo "Últimas 50 líneas de app.log:\n";
echo str_repeat("=", 60) . "\n";

$lastLines = array_slice($lines, -50);
foreach ($lastLines as $line) {
    echo $line;
}
echo "\n" . str_repeat("=", 60) . "\n";
echo "Total de líneas: " . count($lines) . "\n";

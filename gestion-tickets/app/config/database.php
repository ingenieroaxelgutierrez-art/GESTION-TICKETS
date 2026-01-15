<?php
// app/config/database.php
return [
    // Usa variables de entorno en producción; los valores aquí son de ejemplo
    'host'   => getenv('DB_HOST') ?: 'localhost',
    'port'   => getenv('DB_PORT') ?: '3306',
    'dbname' => getenv('DB_NAME') ?: 'gestion_tickets',
    'user'   => getenv('DB_USER') ?: 'db_user',
    'pass'   => getenv('DB_PASS') ?: 'change-me',
];
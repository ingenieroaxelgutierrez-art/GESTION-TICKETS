<?php
// tools/reset-passwords-all.php
// Restaura contraseñas para todos los usuarios cuyo role != 'admin'.
// Uso:
//   php reset-passwords-all.php [NEW_PASSWORD]
// Si NO se pasa NEW_PASSWORD, se usará 'password123'.
// Si se pasa '--unique' como primer argumento, se generará una contraseña aleatoria distinta por usuario.

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

$arg = $argv[1] ?? null;
$useUnique = false;
$defaultPassword = 'password123';

if ($arg === '--unique') {
    $useUnique = true;
} elseif ($arg && $arg !== '') {
    $defaultPassword = $arg;
}

function genPass($length = 10) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $pass = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) $pass .= $chars[random_int(0, $max)];
    return $pass;
}

// Obtener usuarios que NO sean admin
$stmt = $pdo->query("SELECT id, email, name, role FROM users WHERE role <> 'admin'");
$users = $stmt->fetchAll();

if (empty($users)) {
    echo "No se encontraron usuarios para actualizar.\n";
    exit(0);
}

echo "Usuarios encontrados: " . count($users) . "\n\n";

$updated = 0;

foreach ($users as $u) {
    $newPass = $useUnique ? genPass(12) : $defaultPassword;
    $hash = password_hash($newPass, PASSWORD_DEFAULT);

    $up = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $ok = $up->execute([$hash, $u['id']]);

    if ($ok) {
        $updated++;
        echo "✅ {$u['email']} ({$u['role']}) -> nueva contraseña: {$newPass}\n";
    } else {
        echo "❌ Error actualizando {$u['email']}\n";
    }
}

echo "\nResumen: $updated actualizados de " . count($users) . " usuarios afectados.\n";

echo "\nRecomendaciones post-reset:\n";
echo " - Solicitar a los usuarios cambiar su contraseña en el primer inicio de sesión.\n";
echo " - Forzar cambio obligatorio en el sistema si es posible.\n";
echo " - Registrar el cambio en logs seguros y no almacenar contraseñas en texto plano.\n";

echo "\nUsado: ". ($useUnique ? 'contraseñas únicas generadas' : "contraseña común '{$defaultPassword}'") ."\n";

?>
<?php
// app/controllers/DiagnosticController.php

class DiagnosticController extends Controller
{
    // GET /diagnostic/db
    public function db()
    {
        // Este endpoint devuelve JSON tanto para peticiones AJAX como para acceso directo por navegador
        try {
            require_once __DIR__ . '/../core/Logger.php';

            // Permitir sólo acceso local al endpoint diagnóstico
            $remote = $_SERVER['REMOTE_ADDR'] ?? '';
            if (!in_array($remote, ['127.0.0.1', '::1', 'localhost'])) {
                Logger::log("Blocked diagnostic access from {$remote}");
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                return;
            }
            $pdo = Database::getInstance()->getConnection();

            // Listar tablas
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Contar usuarios si existe la tabla
            $usersCount = null;
            $users = [];
            if (in_array('users', $tables)) {
                try {
                    $usersCount = $pdo->query("SELECT COUNT(*) as c FROM users")->fetchColumn();
                } catch (Exception $e) {
                    $usersCount = 'error';
                }

                // Primeros 10 usuarios
                try {
                    $stmt = $pdo->query("SELECT id, name, email, role, active FROM users LIMIT 10");
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $users = [];
                }
            }

            // Registrar acceso diagnostic
            Logger::log("Diagnostic accessed by {$remote}");

            // No exponer el token en producción; solo para debugging local
            $this->json([
                'success' => true,
                'tables' => $tables,
                'users_count' => $usersCount,
                'users_sample' => $users,
                'session' => [
                    'id' => session_id(),
                    'csrf_token_present' => isset($_SESSION['csrf_token']),
                    'user_id' => $_SESSION['user_id'] ?? null,
                ],
                'server' => php_uname(),
            ]);

        } catch (Exception $ex) {
            $this->json(['success' => false, 'error' => $ex->getMessage()], 500);
        }
    }
}

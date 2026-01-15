<?php
/**
 * API Endpoint: GET /api/notifications
 * Obtiene notificaciones del usuario autenticado
 */
session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../app/config/database.php';
    require_once __DIR__ . '/../app/core/Database.php';
    require_once __DIR__ . '/../app/models/Notification.php';

    // Verificar sesión
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $action = $_GET['action'] ?? 'list';
    $limit = (int)($_GET['limit'] ?? 50);

    $notification = new Notification();

    switch($action) {
        case 'list':
            $notifications = $notification->getByUser($userId, $limit);
            echo json_encode([
                'success' => true,
                'data' => $notifications,
                'count' => count($notifications)
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'unread':
            $notifications = $notification->getUnread($userId);
            echo json_encode([
                'success' => true,
                'data' => $notifications,
                'count' => count($notifications)
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'count-unread':
            $count = $notification->countUnread($userId);
            echo json_encode([
                'success' => true,
                'count' => (int)$count
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'mark-read':
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }
            
            $result = $notification->markAsRead($id);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Marcada como leída' : 'Error'
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'mark-all-read':
            $result = $notification->markAllAsRead($userId);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Todas marcadas como leídas' : 'Error'
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'delete':
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }
            
            $result = $notification->delete($id);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Eliminada' : 'Error'
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'delete-all':
            $result = $notification->deleteAll($userId);
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Todas eliminadas' : 'Error'
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'by-type':
            $type = $_GET['type'] ?? 'system';
            $notifications = $notification->getByType($userId, $type);
            echo json_encode([
                'success' => true,
                'data' => $notifications,
                'count' => count($notifications),
                'type' => $type
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;

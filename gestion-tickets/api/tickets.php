<?php
/**
 * API Endpoint directo: GET /api/tickets
 * Responde con JSON de tickets
 */

session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../app/config/database.php';
    require_once __DIR__ . '/../app/core/Database.php';
    require_once __DIR__ . '/../app/models/Ticket.php';

    // Verificar sesión
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    // Obtener filtros
    $filters = [];
    if (!empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (!empty($_GET['priority'])) {
        $filters['priority'] = $_GET['priority'];
    }
    if (!empty($_GET['department'])) {
        $filters['department'] = $_GET['department'];
    }

    // Obtener tickets
    $ticket = new Ticket();
    $tickets = $ticket->getFiltered($filters);

    // Responder con éxito
    echo json_encode([
        'success' => true,
        'data' => $tickets,
        'count' => count($tickets)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
exit;

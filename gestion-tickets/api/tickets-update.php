<?php
/**
 * API Endpoint: PUT /api/tickets-update.php?id=X
 * Actualiza un ticket
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

    // Verificar permisos
    $role = $_SESSION['user_role'] ?? '';
    if (!in_array($role, ['admin', 'agent'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permiso']);
        exit;
    }

    // Obtener ID del ticket
    $ticketId = $_GET['id'] ?? null;
    if (!$ticketId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de ticket requerido']);
        exit;
    }

    // Obtener datos del body
    $data = json_decode(file_get_contents('php://input'), true);

    // Actualizar ticket
    $ticket = new Ticket();
    $result = $ticket->update($ticketId, $data);

    if ($result) {
        $updated = $ticket->getById($ticketId);
        echo json_encode([
            'success' => true,
            'message' => 'Ticket actualizado',
            'data' => $updated
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
exit;

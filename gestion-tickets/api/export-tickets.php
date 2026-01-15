<?php
/**
 * API Endpoint: GET /api/export-tickets.php
 * Exporta tickets a CSV
 */

session_start();

try {
    require_once __DIR__ . '/../app/config/database.php';
    require_once __DIR__ . '/../app/core/Database.php';
    require_once __DIR__ . '/../app/models/Ticket.php';

    // Verificar sesión
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        die('No autorizado');
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

    // Generar CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=tickets_' . date('Y-m-d_H-i-s') . '.csv');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    
    fputcsv($output, ['ID', 'Fecha', 'Usuario', 'Departamento', 'Categoría', 'Título', 'Prioridad', 'Estado'], ';');

    foreach ($tickets as $t) {
        fputcsv($output, [
            $t['id'],
            $t['created_at'],
            $t['user_name'],
            $t['department_name'],
            $t['category_name'],
            $t['title'],
            $t['priority'],
            $t['status']
        ], ';');
    }

    fclose($output);

} catch (Exception $e) {
    http_response_code(500);
    die('Error: ' . $e->getMessage());
}
exit;

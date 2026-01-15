<?php
// app/controllers/ApiController.php

require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/User.php';

class ApiController extends Controller
{
    public function __construct()
    {
        parent::__construct(); // Llamar al constructor padre para inicializar $this->db
        // Validar autenticación para todas las rutas API
        Auth::requireRole(['admin', 'agent', 'user']);
    }

    public function receptores()
    {
        $this->onlyJson();
        $dept = new Department();
        $this->json([
            'success' => true,
            'data'    => $dept->getReceptores()
        ]);
    }

    public function categoriasPorDepartamento($dept_id)
    {
        $this->onlyJson();
        $cat = new Category();
        $categorias = $cat->getByDepartment($dept_id);

        $this->json([
            'success' => true,
            'data'    => $categorias
        ]);
    }

    /**
     * GET /api/agents-by-department/{dept_id} - Obtener agentes de un departamento
     */
    public function agentsByDepartment($dept_id)
    {
        $this->onlyJson();
        
        $stmt = $this->db->prepare("
            SELECT id, name, email FROM users 
            WHERE department_id = ? AND role = 'agent' AND active = 1
            ORDER BY name
        ");
        $stmt->execute([(int)$dept_id]);
        $agents = $stmt->fetchAll();

        $this->json([
            'success' => true,
            'agents' => $agents
        ]);
    }

    /**
     * GET /api/tickets - Obtener listado de tickets con filtros opcionales
     */
    public function tickets()
    {
        $this->onlyJson();
        
        $ticket = new Ticket();
        $filters = [];

        // Aplicar filtros si existen
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['priority'])) {
            $filters['priority'] = $_GET['priority'];
        }
        if (!empty($_GET['department'])) {
            $filters['department'] = $_GET['department'];
        }

        $tickets = $ticket->getFiltered($filters);

        $this->json([
            'success' => true,
            'data' => $tickets,
            'count' => count($tickets)
        ]);
    }

    /**
     * PUT /api/tickets/:id - Actualizar un ticket
     */
    public function updateTicket($id)
    {
        $this->onlyJson();
        
        // Validar CSRF token
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$csrfToken || !validate_csrf($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token inválido'], 403);
        }
        
        // Verificar que el usuario sea admin o agente
        if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'agent'])) {
            $this->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $ticket = new Ticket();

        try {
            $ticket->update($id, $data);
            
            $this->json([
                'success' => true,
                'message' => 'Ticket actualizado',
                'data' => $ticket->find($id)
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/tickets/export - Exportar tickets a CSV
     */
    public function exportTickets()
    {
        // Obtener tickets con filtros
        $ticket = new Ticket();
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

        $tickets = $ticket->getFiltered($filters);

        // Generar CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=tickets_' . date('Y-m-d_H-i-s') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Fecha', 'Usuario', 'Departamento', 'Categoría', 'Título', 'Prioridad', 'Estado']);

        foreach ($tickets as $ticket) {
            fputcsv($output, [
                $ticket['id'],
                $ticket['created_at'],
                $ticket['user_name'],
                $ticket['department_name'],
                $ticket['category_name'],
                $ticket['title'],
                $ticket['priority'],
                $ticket['status']
            ]);
        }

        fclose($output);
        exit;
    }
}

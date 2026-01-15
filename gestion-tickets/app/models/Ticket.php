<?php
// app/models/Ticket.php

require_once __DIR__ . '/../core/Model.php';

class Ticket extends Model
{
    protected $table = 'tickets';

    // ===================================================================
    // 1. Listado paginado con filtros + JOINs para mostrar nombres
    // ===================================================================
    public function getAllPaginated($filters = [])
{
    $sql = "SELECT 
                t.*, u_from.name AS creator_name, d_to.name AS dept_to_name, 
                u_assigned.name AS assigned_name, c.name AS category_name
            FROM tickets t
            LEFT JOIN users u_from ON t.user_id = u_from.id
            LEFT JOIN departments d_to ON t.department_to_id = d_to.id
            LEFT JOIN users u_assigned ON t.assigned_to = u_assigned.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE 1=1";

    $params = [];

    if (!empty($filters['search'])) {
        $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
        $search = "%{$filters['search']}%";
        $params[] = $search; 
        $params[] = $search;
    }
    if (!empty($filters['status'])) {
        $sql .= " AND t.status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['priority'])) {
        $sql .= " AND t.priority = ?";
        $params[] = $filters['priority'];
    }
    if (!empty($filters['dept_to'])) {
        $sql .= " AND t.department_to_id = ?";
        $params[] = $filters['dept_to'];
    }
    if (!empty($filters['assigned_to'])) {
        $sql .= " AND t.assigned_to = ?";
        $params[] = $filters['assigned_to'];
    }
    if (!empty($filters['category_id'])) {
        $sql .= " AND t.category_id = ?";
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['user_id'])) {
        $sql .= " AND t.user_id = ?";
        $params[] = $filters['user_id'];
    }

    $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $filters['limit'] ?? 20;
    $params[] = $filters['offset'] ?? 0;

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

    // ===================================================================
    // 2. Contar total de tickets con los mismos filtros
    // ===================================================================
    public function countAll($filters = [])
    {
        $sql = "SELECT COUNT(*) FROM tickets t WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['dept_to'])) {
            $sql .= " AND t.department_to_id = ?";
            $params[] = $filters['dept_to'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND t.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['assigned_to'])) {
            $sql .= " AND t.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Obtener tickets del usuario actual (como creador)
     */
    public function getByUser($userId, $limit = 50)
    {
        $sql = "SELECT 
                    t.*, 
                    u.name AS creator_name, 
                    d.name AS department_name,
                    c.name AS category_name,
                    ua.name AS assigned_name
                FROM tickets t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN departments d ON t.department_to_id = d.id
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN users ua ON t.assigned_to = ua.id
                WHERE t.user_id = ?
                ORDER BY t.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener tickets para un departamento receptor (para agentes)
     * Mostrar TODOS los tickets dirigidos a ese departamento
     */
    public function getByReceiverDepartment($departmentId, $limit = 50, $filters = [])
    {
        $sql = "SELECT 
                    t.*, 
                    u.name AS creator_name, 
                    u.email AS creator_email,
                    d_to.name AS dept_to_name,
                    c.name AS category_name,
                    ua.name AS assigned_name
                FROM tickets t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN departments d_to ON t.department_to_id = d_to.id
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN users ua ON t.assigned_to = ua.id
                WHERE t.department_to_id = ?";
        
        $params = [$departmentId];

        // Filtros opcionales
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Contar tickets de un departamento receptor con filtros
     */
    public function countByReceiverDepartment($departmentId, $filters = [])
    {
        $sql = "SELECT COUNT(*) FROM tickets WHERE department_to_id = ?";
        $params = [$departmentId];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND priority = ?";
            $params[] = $filters['priority'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // ===================================================================
    // 3. Ticket completo con todos los datos relacionados
    // ===================================================================
    public function findWithDetails($id)
    {
        $sql = "SELECT 
                    t.*,
                    u_from.name AS creator_name,
                    u_from.email AS creator_email,
                    d_from.name AS dept_from_name,
                    d_to.name   AS dept_to_name,
                    u_assigned.name AS assigned_name,
                    c.name AS category_name
                FROM tickets t
                LEFT JOIN users u_from ON t.user_id = u_from.id
                LEFT JOIN departments d_from ON t.department_from_id = d_from.id
                LEFT JOIN departments d_to   ON t.department_to_id = d_to.id
                LEFT JOIN users u_assigned   ON t.assigned_to = u_assigned.id
                LEFT JOIN categories c       ON t.category_id = c.id
                WHERE t.id = ? 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ===================================================================
    // 4. Comentarios del ticket
    // ===================================================================
    public function getComments($ticket_id)
    {
        $sql = "SELECT c.*, u.name AS user_name 
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.ticket_id = ?
                ORDER BY c.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticket_id]);
        $comments = $stmt->fetchAll();
        
        // Obtener adjuntos para cada comentario
        foreach ($comments as &$comment) {
            $comment['attachments'] = $this->getCommentAttachments($comment['id']);
        }
        
        return $comments;
    }

    // ===================================================================
    // 5. Crear ticket → devuelve el ID insertado
    // ===================================================================
    public function create($data)
    {
        // Auto-asignar a un agente del departamento destino si existe
        $assignedTo = null;
        if (!empty($data['department_to_id'])) {
            require_once __DIR__ . '/User.php';
            $userModel = new User();
            
            // OPCIÓN 1: Asignar a un agente específico por email (descomentar si se necesita)
            // Ejemplo: Para TI, siempre asignar a agent-ti@example.com
            /*
            if ($data['department_to_id'] == 4) { // 4 = TI
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = 'agent-ti@example.com' AND active = 1 LIMIT 1");
                $stmt->execute();
                $specificAgent = $stmt->fetch();
                if ($specificAgent) {
                    $assignedTo = $specificAgent['id'];
                }
            }
            */
            
            // OPCIÓN 2: Asignación aleatoria (comportamiento actual)
            if (!$assignedTo) {
                $agent = $userModel->getAgentsByDepartment($data['department_to_id']);
                if ($agent) {
                    $assignedTo = $agent['id'];
                }
            }
        }

        $sql = "INSERT INTO tickets (
                    title, description, status, priority,
                    department_from_id, department_to_id, category_id,
                    user_id, assigned_to
                ) VALUES (
                    :title, :description, 'open', :priority,
                    :department_from_id, :department_to_id, :category_id,
                    :user_id, :assigned_to
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':title'              => $data['title'],
            ':description'        => $data['description'],
            ':priority'           => $data['priority'],
            ':department_from_id' => $data['department_from_id'],
            ':department_to_id'   => $data['department_to_id'],
            ':category_id'        => $data['category_id'] ?? null,
            ':user_id'            => $data['user_id'],
            ':assigned_to'        => $assignedTo
        ]);

        return $this->db->lastInsertId();
    }

    // ===================================================================
    // 6. Cambiar estado
    // ===================================================================
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    // ===================================================================
    // 7. Cambiar prioridad
    // ===================================================================
    public function updatePriority($id, $priority)
    {
        $stmt = $this->db->prepare("UPDATE tickets SET priority = ? WHERE id = ?");
        return $stmt->execute([$priority, $id]);
    }

    // ===================================================================
    // 8. Asignar agente (puede ser NULL)
    // ===================================================================
    public function assignTo($id, $agent_id)
    {
        $stmt = $this->db->prepare("UPDATE tickets SET assigned_to = ? WHERE id = ?");
        return $stmt->execute([$agent_id, $id]);
    }

    // ===================================================================
    // 9. Añadir comentario
    // ===================================================================
    public function addComment($ticket_id, $user_id, $message)
    {
        $sql = "INSERT INTO comments (ticket_id, user_id, message) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticket_id, $user_id, $message]);
        return $this->db->lastInsertId();
    }

    // ===================================================================
    // DASHBOARD METHODS (solo una vez, limpios y optimizados)
    // ===================================================================

    public function countByStatus($department_to_id = null)
    {
        $sql = "SELECT status, COUNT(*) as total FROM tickets WHERE 1=1";
        $params = [];
        if ($department_to_id) {
            $sql .= " AND department_to_id = ?";
            $params[] = $department_to_id;
        }
        $sql .= " GROUP BY status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();

        $defaults = ['open' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0];
        foreach ($result as $row) {
            $defaults[$row['status']] = (int)$row['total'];
        }
        return $defaults;
    }

    /**
     * Obtener tickets filtrados para el dashboard y API
     */
    public function getFiltered($filters = [])
    {
        $sql = "SELECT 
                    t.*, 
                    u.name AS user_name, 
                    d.name AS department_name,
                    c.name AS category_name
                FROM tickets t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN departments d ON t.department_to_id = d.id
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }
        if (!empty($filters['department'])) {
            $sql .= " AND t.department_to_id = ?";
            $params[] = $filters['department'];
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener ticket por ID
     */
    public function getById($id)
    {
        $sql = "SELECT 
                    t.*, 
                    u.name AS user_name, 
                    d.name AS department_name,
                    c.name AS category_name,
                    ua.name AS assigned_name
                FROM tickets t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN departments d ON t.department_to_id = d.id
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN users ua ON t.assigned_to = ua.id
                WHERE t.id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Actualizar ticket
     */
    public function update($id, $data)
    {
        $updates = [];
        $params = [];

        if (isset($data['title'])) {
            $updates[] = "title = ?";
            $params[] = $data['title'];
        }
        if (isset($data['description'])) {
            $updates[] = "description = ?";
            $params[] = $data['description'];
        }
        if (isset($data['status'])) {
            $updates[] = "status = ?";
            $params[] = $data['status'];
        }
        if (isset($data['priority'])) {
            $updates[] = "priority = ?";
            $params[] = $data['priority'];
        }
        if (isset($data['category_id'])) {
            $updates[] = "category_id = ?";
            $params[] = $data['category_id'];
        }
        if (isset($data['department_id'])) {
            $updates[] = "department_to_id = ?";
            $params[] = $data['department_id'];
        }
        if (isset($data['assigned_to'])) {
            $updates[] = "assigned_to = ?";
            $params[] = $data['assigned_to'] ?: null;
        }

        if (empty($updates)) {
            return false;
        }

        $updates[] = "updated_at = NOW()";
        $params[] = $id;

        $sql = "UPDATE tickets SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getAvgResponseTime($department_to_id = null)
    {
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes
                FROM tickets
                WHERE status != 'open'";
        if ($department_to_id) {
            $sql .= " AND department_to_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$department_to_id]);
        } else {
            $stmt = $this->db->query($sql);
        }
        $minutes = $stmt->fetchColumn() ?: 0;
        return round($minutes / 60, 1);
    // horas
    }

    public function countByPriorityLast30Days($department_to_id = null)
    {
        $sql = "SELECT priority, COUNT(*) as total 
                FROM tickets 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $params = [];
        if ($department_to_id) {
            $sql .= " AND department_to_id = ?";
            $params[] = $department_to_id;
        }
        $sql .= " GROUP BY priority";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();

        $defaults = ['urgente' => 0, 'alta' => 0, 'media' => 0, 'baja' => 0];
        foreach ($result as $row) {
            $defaults[$row['priority']] = (int)$row['total'];
        }
        return $defaults;
    }

    public function getTopCategories($limit = 5)
    {
        $stmt = $this->db->prepare("
            SELECT c.name, COUNT(t.id) as total
            FROM tickets t
            JOIN categories c ON t.category_id = c.id
            GROUP BY t.category_id, c.name
            ORDER BY total DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getTicketsLast7Days($department_to_id = null)
    {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as total
                FROM tickets
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
        $params = [];
        if ($department_to_id) {
            $sql .= " AND department_to_id = ?";
            $params[] = $department_to_id;
        }
        $sql .= " GROUP BY DATE(created_at) ORDER BY date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public function close($id, $reason, $user_id)
{
    if (empty(trim($reason))) {
        return false;
    }
    $sql = "UPDATE tickets 
            SET status = 'closed', 
                closed_reason = ?, 
                closed_by = ?, 
                closed_at = NOW(),
                updated_at = NOW()
            WHERE id = ? AND status != 'closed'";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$reason, $user_id, $id]);
}
public function logHistory($ticket_id, $user_id, $action, $old = null, $new = null)
{
    $stmt = $this->db->prepare("INSERT INTO ticket_history (ticket_id, user_id, action, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ticket_id, $user_id, $action, $old, $new]);
}

public function addAttachment($ticket_id, $file, $comment_id = null)
{
    $allowed = ['jpg','jpeg','png','gif','pdf','docx','doc','xlsx','xls','txt','zip'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed) || $file['size'] > 10*1024*1024) { // 10MB máx
        return false;
    }

    $filename = uniqid('attach_') . '.' . $ext;
    $uploadDir = __DIR__ . '/../../assets/uploads/';
    
    // Crear directorio si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $path = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $path)) {
        $stmt = $this->db->prepare("INSERT INTO ticket_attachments (ticket_id, comment_id, filename, original_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ticket_id, $comment_id, $filename, $file['name']]);
        return $filename;
    }
    return false;
}

/**
 * Obtener todos los adjuntos de un ticket
 */
public function getAttachments($ticket_id)
{
    $stmt = $this->db->prepare("SELECT * FROM ticket_attachments WHERE ticket_id = ? AND (comment_id IS NULL OR comment_id = 0) ORDER BY id ASC");
    $stmt->execute([$ticket_id]);
    return $stmt->fetchAll();
}

/**
 * Obtener todos los adjuntos de un comentario
 */
public function getCommentAttachments($comment_id)
{
    $stmt = $this->db->prepare("SELECT * FROM ticket_attachments WHERE comment_id = ? ORDER BY id ASC");
    $stmt->execute([$comment_id]);
    return $stmt->fetchAll();
}

}
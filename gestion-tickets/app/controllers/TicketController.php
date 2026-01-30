<?php
// app/controllers/TicketController.php

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/User.php';

class TicketController extends Controller
{
    private $ticketModel;
    private $deptModel;
    private $catModel;

    public function __construct()
    {
        parent::__construct(); // Inicializa $this->db desde Controller
        Auth::requireRole(['admin', 'agent', 'user']); // todos pueden usar tickets
        $this->ticketModel = new Ticket();
        $this->deptModel   = new Department();
        $this->catModel    = new Category();
    }

    // GET /tickets → listado con filtros (para DataTables o tu propio front)
    public function index()
    {
        // Log para debugging
        error_log("TicketController::index() called - isAjax: " . ($this->isAjax() ? 'yes' : 'no'));
        
        // Si la petición NO es AJAX, renderizamos la vista HTML
        if (!$this->isAjax()) {
            try {
                $user = Auth::user();
                $userId = $user['id'] ?? null;
                $role = Auth::role();
                
                error_log("TicketController::index() - user: " . ($user ? $userId : 'null') . ", role: " . $role);

                // Si es admin -> ver todos los tickets
                if ($role === 'admin') {
                    error_log("TicketController::index() - Loading all tickets for admin");
                    $myTickets = $this->ticketModel->getAllPaginated([
                        'limit' => 50,
                        'offset' => 0
                    ]);
                } elseif ($role === 'agent') {
                    // Agentes ven tickets del departamento receptor
                    $deptId = $_SESSION['user_department_id'] ?? null;
                    error_log("TicketController::index() - Loading tickets for department: " . $deptId);
                    $myTickets = $this->ticketModel->getByReceiverDepartment($deptId);
                } else {
                    // usuarios normales ven sólo sus tickets (que crearon)
                    error_log("TicketController::index() - Loading tickets for user: " . $userId);
                    $myTickets = $this->ticketModel->getByUser($userId);
                }
                
                error_log("TicketController::index() - Tickets found: " . count($myTickets ?? []));

                // Calcular estadísticas según el rol
                if (in_array($role, ['agent', 'admin'])) {
                    $myStats = [
                        'total' => count($myTickets),
                        'abiertos' => count(array_filter($myTickets, fn($t) => $t['status'] === 'open')),
                        'en_progreso' => count(array_filter($myTickets, fn($t) => $t['status'] === 'in_progress')),
                        'resueltos' => count(array_filter($myTickets, fn($t) => $t['status'] === 'resolved')),
                        'cerrados' => count(array_filter($myTickets, fn($t) => $t['status'] === 'closed'))
                    ];
                } else {
                    $myStats = [
                        'total' => count($myTickets),
                        'abiertos' => count(array_filter($myTickets, fn($t) => $t['status'] === 'open')),
                        'resueltos' => count(array_filter($myTickets, fn($t) => $t['status'] === 'resolved'))
                    ];
                }
                
                $pageTitle = in_array($role, ['agent', 'admin']) ? 'Tickets por Atender' : 'Mis Tickets';
                $this->view('tickets/lista', [
                    'pageTitle' => $pageTitle,
                    'myTickets' => $myTickets,
                    'myStats' => $myStats,
                    'userRole' => $role
                ]);
            } catch (Exception $e) {
                $fallbackRole = Auth::role();
                $this->view('tickets/lista', [
                    'pageTitle' => 'Mis Tickets',
                    'myTickets' => [],
                    'myStats' => ['total' => 0, 'abiertos' => 0, 'resueltos' => 0],
                    'userRole' => $fallbackRole,
                    'error' => $e->getMessage()
                ]);
            }
            return;
        }

        $status   = $_GET['status'] ?? null;
        $dept_to  = $_GET['dept_to'] ?? null;
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $limit    = min(100, (int)($_GET['limit'] ?? 20));
        $offset   = ($page - 1) * $limit;

        $tickets = $this->ticketModel->getAllPaginated([
            'status'     => $status,
            'dept_to'    => $dept_to,
            'user_id'    => Auth::role() === 'user' ? Auth::user()['id'] : null, // usuarios solo ven los suyos
            'limit'      => $limit,
            'offset'     => $offset
        ]);

        $total = $this->ticketModel->countAll([
            'status'  => $status,
            'dept_to' => $dept_to,
            'user_id' => Auth::role() === 'user' ? Auth::user()['id'] : null
        ]);

        $this->json([
            'success' => true,
            'data'    => $tickets,
            'pagination' => [
                'page'  => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    // GET /tickets/create → mostrar formulario de crear ticket
    public function createForm()
    {
        try {
            // Obtener departamentos emisores (donde trabaja el usuario)
            $departments = $this->deptModel->getEmisores();

            // Obtener departamentos receptores (TI, Procesos, etc.)
            $receptorDepts = $this->deptModel->getReceptores();

            // Obtener todas las categorías para mostrar de inicio
            $categories = $this->catModel->getAllWithDepartment();

            $pageTitle = 'Crear Ticket - RIDECO';
            $this->view('tickets/create', [
                'pageTitle' => $pageTitle,
                'departments' => $departments,
                'receptorDepts' => $receptorDepts,
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            $this->view('tickets/create', [
                'pageTitle' => 'Crear Ticket',
                'departments' => [],
                'receptorDepts' => [],
                'categories' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    // GET /tickets/{id}
    public function show($id)
    {
        $ticket = $this->ticketModel->findWithDetails($id);

        if (!$ticket || !$this->canAccessTicket($ticket)) {
            // Si es AJAX, retorna JSON
            if ($this->isAjax()) {
                $this->json(['success' => false, 'error' => 'Ticket no encontrado'], 404);
            }
            // Si es navegador, redirige al listado
            $this->redirect('/tickets');
        }

        $comments = $this->ticketModel->getComments($id);
        $attachments = $this->ticketModel->getAttachments($id);

        // Si es AJAX, retorna JSON
        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'ticket'  => $ticket,
                'comments'=> $comments,
                'attachments' => $attachments
            ]);
        }

        // Si es navegador, retorna la lista con el ticket abierto
        // Recuperar todos los tickets del usuario/agente
        $user = Auth::user();
        $userId = $user['id'] ?? null;
        $role = Auth::role();

        if ($role === 'admin') {
            $myTickets = $this->ticketModel->getAllPaginated([
                'limit' => 50,
                'offset' => 0
            ]);
        } elseif ($role === 'agent') {
            $deptId = $_SESSION['user_department_id'] ?? null;
            $myTickets = $this->ticketModel->getByReceiverDepartment($deptId);
        } else {
            $myTickets = $this->ticketModel->getByUser($userId);
        }

        // Calcular estadísticas
        if (in_array($role, ['agent', 'admin'])) {
            $myStats = [
                'total' => count($myTickets),
                'abiertos' => count(array_filter($myTickets, fn($t) => $t['status'] === 'open')),
                'en_progreso' => count(array_filter($myTickets, fn($t) => $t['status'] === 'in_progress')),
                'resueltos' => count(array_filter($myTickets, fn($t) => $t['status'] === 'resolved')),
                'cerrados' => count(array_filter($myTickets, fn($t) => $t['status'] === 'closed'))
            ];
        } else {
            $myStats = [
                'total' => count($myTickets),
                'abiertos' => count(array_filter($myTickets, fn($t) => $t['status'] === 'open')),
                'resueltos' => count(array_filter($myTickets, fn($t) => $t['status'] === 'resolved'))
            ];
        }

        $this->view('tickets/lista', [
            'pageTitle' => 'Detalle del Ticket',
            'myTickets' => $myTickets,
            'myStats' => $myStats,
            'userRole' => $role,
            'selectedTicketId' => $id
        ]);
    }

    // POST /tickets/create  → crear ticket (usuarios normales)
    public function store()
    {
        $this->onlyJson();

        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        $data = [
            'title'            => trim($_POST['title'] ?? ''),
            'description'      => trim($_POST['description'] ?? ''),
            'department_to_id' => (int)($_POST['department_to_id'] ?? 0),
            'category_id'      => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'priority'         => $_POST['priority'] ?? 'media',
            'user_id'          => $_SESSION['user_id'],
            'department_from_id' => $_SESSION['user_department_id']
        ];

        // Validaciones
        if (empty($data['title']) || empty($data['description']) || $data['department_to_id'] <= 0) {
            $this->json(['success' => false, 'error' => 'Faltan datos obligatorios']);
        }

        if (!$this->deptModel->isReceptor($data['department_to_id'])) {
            $this->json(['success' => false, 'error' => 'Departamento destino inválido']);
        }

        $ticketId = $this->ticketModel->create($data);

        if ($ticketId) {
            $this->json([
                'success' => true,
                'message' => 'Ticket creado correctamente',
                'ticket_id' => $ticketId
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'Error al crear ticket'], 500);
        }
    }

    // POST /ajax/tickets/create → crear ticket vía AJAX (form multipart)
    public function ajaxStore()
    {
        $this->onlyJson();

        // Aceptar token desde POST o desde header `X-CSRF-Token`
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_CSRF'] ?? null;
        $postToken = $_POST['csrf_token'] ?? null;
        $tokenToValidate = $postToken ?: $headerToken;
        if (!validate_csrf($tokenToValidate)) {
            return $this->json(['success' => false, 'error' => 'Token inválido']);
        }

        // Aceptar nombres de campo alternativos para compatibilidad
        $title = trim($_POST['title'] ?? $_POST['titulo'] ?? '');
        $description = trim($_POST['description'] ?? $_POST['descripcion'] ?? '');
        $department_to = (int)($_POST['department_to_id'] ?? $_POST['dept_destino'] ?? 0);
        $category_id = !empty($_POST['category_id'] ?? $_POST['incidencia'] ?? null) ? (int)($_POST['category_id'] ?? $_POST['incidencia']) : null;
        $priority = $_POST['priority'] ?? $_POST['prioridad_sugerida'] ?? 'media';

        $data = [
            'title' => $title,
            'description' => $description,
            'department_to_id' => $department_to,
            'category_id' => $category_id,
            'priority' => $priority,
            'user_id' => $_SESSION['user_id'],
            'department_from_id' => $_POST['department_from_id'] ?? $_SESSION['user_department_id'] ?? null
        ];

        if (empty($data['title']) || empty($data['description']) || $data['department_to_id'] <= 0) {
            return $this->json(['success' => false, 'error' => 'Faltan datos obligatorios']);
        }

        if (!$this->deptModel->isReceptor($data['department_to_id'])) {
            return $this->json(['success' => false, 'error' => 'Departamento destino inválido']);
        }

        $ticketId = $this->ticketModel->create($data);

        if ($ticketId) {
            // Procesar archivos adjuntos si los hay
            if (!empty($_FILES['attachments'])) {
                $files = $_FILES['attachments'];
                $uploadedCount = 0;
                $failedCount = 0;

                // Si es un solo archivo, $_FILES lo devuelve como un array único
                // Si son múltiples, es un array de arrays
                $isMultiple = is_array($files['tmp_name']);

                if ($isMultiple) {
                    for ($i = 0; $i < count($files['tmp_name']); $i++) {
                        if (!empty($files['tmp_name'][$i])) {
                            $file = [
                                'name' => $files['name'][$i],
                                'tmp_name' => $files['tmp_name'][$i],
                                'size' => $files['size'][$i],
                                'type' => $files['type'][$i],
                                'error' => $files['error'][$i]
                            ];
                            
                            if ($this->ticketModel->addAttachment($ticketId, $file)) {
                                $uploadedCount++;
                            } else {
                                $failedCount++;
                            }
                        }
                    }
                } else {
                    // Un solo archivo
                    if (!empty($files['tmp_name']) && $files['error'] === UPLOAD_ERR_OK) {
                        if ($this->ticketModel->addAttachment($ticketId, $files)) {
                            $uploadedCount++;
                        } else {
                            $failedCount++;
                        }
                    }
                }

                // Log para debugging
                error_log("Ticket $ticketId: $uploadedCount archivos subidos, $failedCount fallaron");
            }

            return $this->json(['success' => true, 'ticket_id' => $ticketId]);
        }

        return $this->json(['success' => false, 'error' => 'Error al crear ticket']);
    }

    // PUT /tickets/{id}/status  → agentes cambian estado
    public function changeStatus($id)
    {
        $this->onlyJson();
        
        $ticket = $this->ticketModel->findWithDetails($id);
        if (!$ticket) {
            return $this->json(['success' => false, 'error' => 'Ticket no encontrado'], 404);
        }

        // Validar que puede actuar sobre este ticket
        if (!$this->canActOnTicket($ticket)) {
            return $this->json(['success' => false, 'error' => 'Sin permiso para actuar en este ticket'], 403);
        }

        // Parsear datos de PUT request
        $data = $this->parseRequestBody();
        $status = $data['status'] ?? $_POST['status'] ?? '';
        $reason = $data['reason'] ?? $_POST['reason'] ?? '';

        if (!in_array($status, ['open','in_progress','resolved','closed'])) {
            return $this->json(['success' => false, 'error' => 'Estado inválido']);
        }

        // Si se cierra → motivo obligatorio
        if ($status === 'closed' && empty(trim($reason))) {
            return $this->json(['success' => false, 'error' => 'El motivo de cierre es obligatorio']);
        }

        $oldStatus = $ticket['status'];

        if ($status === 'closed') {
            $success = $this->ticketModel->close($id, $reason, $_SESSION['user_id']);
        } else {
            $success = $this->ticketModel->updateStatus($id, $status);
        }

        if ($success) {
            // REGISTRAMOS EN EL HISTORIAL
            $this->ticketModel->logHistory($id, $_SESSION['user_id'], 'status_changed', $oldStatus, $status);
            
            $this->json(['success' => true, 'message' => 'Estado actualizado']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al cambiar estado'], 500);
        }
    }

    // PUT /tickets/{id}/priority
    public function changePriority($id)
    {
        $this->onlyJson();
        
        $ticket = $this->ticketModel->findWithDetails($id);
        if (!$ticket) {
            return $this->json(['success' => false, 'error' => 'Ticket no encontrado'], 404);
        }

        // Validar que puede actuar sobre este ticket
        if (!$this->canActOnTicket($ticket)) {
            return $this->json(['success' => false, 'error' => 'Sin permiso para cambiar prioridad'], 403);
        }

        // Parsear datos de PUT request
        $data = $this->parseRequestBody();
        $priority = $data['priority'] ?? $_POST['priority'] ?? '';
        if (!in_array($priority, ['baja','media','alta','urgente'])) {
            return $this->json(['success' => false, 'error' => 'Prioridad inválida']);
        }

        $oldPriority = $ticket['priority'];
        $success = $this->ticketModel->updatePriority($id, $priority);

        if ($success) {
            // REGISTRAMOS EN EL HISTORIAL
            $this->ticketModel->logHistory($id, $_SESSION['user_id'], 'priority_changed', $oldPriority, $priority);
            
            $this->json(['success' => true, 'message' => 'Prioridad actualizada']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al cambiar prioridad'], 500);
        }
    }

    // PUT /tickets/{id}/assign  → asignar agente
    public function assign($id)
    {
        $this->onlyJson();
        
        $ticket = $this->ticketModel->findWithDetails($id);
        if (!$ticket) {
            return $this->json(['success' => false, 'error' => 'Ticket no encontrado'], 404);
        }

        // Validar que puede actuar sobre este ticket
        if (!$this->canActOnTicket($ticket)) {
            return $this->json(['success' => false, 'error' => 'Sin permiso para reasignar'], 403);
        }

        // Parsear datos de PUT request
        $data = $this->parseRequestBody();
        $agentId = (int)($data['agent_id'] ?? $_POST['agent_id'] ?? 0);
        
        // El agent_id puede ser 0 (desasignar) o debe ser un agente del mismo departamento
        if ($agentId > 0) {
            $agentModel = new User();
            $agent = $agentModel->find($agentId);
            
            if (!$agent) {
                return $this->json(['success' => false, 'error' => 'Agente no encontrado']);
            }

            // Validar que el agente sea del mismo departamento receptor
            if ($agent['department_id'] != $ticket['department_to_id'] || $agent['role'] !== 'agent') {
                return $this->json(['success' => false, 'error' => 'El agente debe pertenecer al departamento receptor']);
            }
            
            $agentName = $agent['name'];
        } else {
            $agentName = 'Sin asignar';
        }

        $success = $this->ticketModel->assignTo($id, $agentId > 0 ? $agentId : null);

        if ($success) {
            // REGISTRAMOS EN EL HISTORIAL
            $this->ticketModel->logHistory($id, $_SESSION['user_id'], 'assigned', null, $agentName);
            
            $this->json(['success' => true, 'message' => 'Ticket reasignado correctamente']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al reasignar'], 500);
        }
    }
    // POST /tickets/{id}/comment
    public function addComment($id)
    {
        $this->onlyJson();

        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        $ticket = $this->ticketModel->findWithDetails($id);
        if (!$ticket) {
            return $this->json(['success' => false, 'error' => 'Ticket no encontrado'], 404);
        }

        // Solo creadores y receptores (agentes del departamento) pueden comentar
        $canComment = false;
        
        if (Auth::role() === 'admin') {
            $canComment = true;
        } elseif (Auth::role() === 'user' && $ticket['user_id'] == $_SESSION['user_id']) {
            // El usuario que creó el ticket puede comentar
            $canComment = true;
        } elseif (Auth::role() === 'agent' && $this->canActOnTicket($ticket)) {
            // Los agentes del departamento receptor pueden comentar
            $canComment = true;
        }

        if (!$canComment) {
            return $this->json(['success' => false, 'error' => 'No tienes permiso para comentar'], 403);
        }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            return $this->json(['success' => false, 'error' => 'Mensaje vacío']);
        }

        $commentId = $this->ticketModel->addComment($id, $_SESSION['user_id'], $message);

        if ($commentId) {
            // Procesar archivos adjuntos si existen
            $attachedFiles = [];
            if (!empty($_FILES['attachments'])) {
                foreach ($_FILES['attachments']['tmp_name'] as $key => $tmpName) {
                    if (!empty($tmpName) && is_uploaded_file($tmpName)) {
                        $file = [
                            'name' => $_FILES['attachments']['name'][$key],
                            'type' => $_FILES['attachments']['type'][$key],
                            'tmp_name' => $tmpName,
                            'error' => $_FILES['attachments']['error'][$key],
                            'size' => $_FILES['attachments']['size'][$key]
                        ];
                        
                        $filename = $this->ticketModel->addAttachment($id, $file, $commentId);
                        if ($filename) {
                            $attachedFiles[] = $filename;
                        }
                    }
                }
            }
            
            // REGISTRAMOS EN EL HISTORIAL (solo los primeros 100 caracteres)
            $shortMessage = substr($message, 0, 100);
            if (strlen($message) > 100) $shortMessage .= '...';
            $this->ticketModel->logHistory($id, $_SESSION['user_id'], 'comment_added', null, $shortMessage);
            
            $this->json([
                'success' => true, 
                'comment_id' => $commentId,
                'attachments_count' => count($attachedFiles)
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'Error al comentar'], 500);
        }
    }

    // Helper privado: ¿este usuario puede ver este ticket?
    private function canAccessTicket($ticket)
    {
        $role = Auth::role();
        $userId = $_SESSION['user_id'];
        $userDeptId = $_SESSION['user_department_id'] ?? null;

        // Admin puede acceder a cualquier ticket
        if ($role === 'admin') {
            return true;
        }

        // Agent solo puede ver tickets de su departamento receptor
        if ($role === 'agent') {
            return $ticket['department_to_id'] == $userDeptId;
        }

        // Usuario normal solo puede ver sus propios tickets
        if ($role === 'user') {
            return $ticket['user_id'] == $userId;
        }

        return false;
    }

    /**
     * Helper privado: ¿este usuario puede ACTUAR (modificar/responder) en este ticket?
     * Solo los agentes del departamento receptor pueden actuar
     */
    private function canActOnTicket($ticket)
    {
        $role = Auth::role();
        $userDeptId = $_SESSION['user_department_id'] ?? null;

        // Admin puede actuar en cualquier ticket
        if ($role === 'admin') {
            return true;
        }

        // Agent solo puede actuar si el ticket es para su departamento
        if ($role === 'agent') {
            return $ticket['department_to_id'] == $userDeptId;
        }

        // Usuario normal NO puede actuar en tickets (solo ver los propios)
        return false;
    }
    
    /**
     * Descargar o visualizar un archivo adjunto
     */
    public function downloadAttachment($attachmentId)
    {
        // Obtener información del adjunto
        $stmt = $this->db->prepare("SELECT * FROM ticket_attachments WHERE id = ?");
        $stmt->execute([$attachmentId]);
        $attachment = $stmt->fetch();
        
        if (!$attachment) {
            http_response_code(404);
            die('Archivo no encontrado');
        }
        
        // Determinar el ticket_id (puede venir directamente o desde el comentario)
        $ticketId = $attachment['ticket_id'];
        if (!$ticketId && $attachment['comment_id']) {
            // Si el adjunto es de un comentario, obtener el ticket_id del comentario
            $stmt = $this->db->prepare("SELECT ticket_id FROM comments WHERE id = ?");
            $stmt->execute([$attachment['comment_id']]);
            $comment = $stmt->fetch();
            $ticketId = $comment ? $comment['ticket_id'] : null;
        }
        
        if (!$ticketId) {
            http_response_code(404);
            die('Ticket no encontrado');
        }
        
        // Verificar que el usuario tenga acceso al ticket
        $ticket = $this->ticketModel->find($ticketId);
        if (!$ticket || !$this->canAccessTicket($ticket)) {
            http_response_code(403);
            die('Acceso denegado');
        }
        
        $filePath = __DIR__ . '/../../assets/uploads/' . $attachment['filename'];
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            die('Archivo no encontrado en el servidor');
        }
        
        // Determinar tipo MIME
        $ext = strtolower(pathinfo($attachment['filename'], PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'zip' => 'application/zip'
        ];
        
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        
        // Para PDFs e imágenes, mostrar inline; para otros, descargar
        $disposition = in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt']) ? 'inline' : 'attachment';
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: ' . $disposition . '; filename="' . $attachment['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=3600');
        
        readfile($filePath);
        exit;
    }
    
    public function notifications()
{
    $this->onlyJson();
    $lastCheck = $_GET['last'] ?? time();

    $stmt = $this->db->prepare("
        SELECT COUNT(*) as nuevos 
        FROM comments c 
        JOIN tickets t ON c.ticket_id = t.id 
        WHERE c.created_at > FROM_UNIXTIME(?) 
          AND (t.assigned_to = ? OR t.user_id = ?)
    ");
    $stmt->execute([$lastCheck, $_SESSION['user_id'], $_SESSION['user_id']]);
    $nuevos = $stmt->fetchColumn();

    $this->json(['nuevos' => (int)$nuevos, 'timestamp' => time()]);
}

}

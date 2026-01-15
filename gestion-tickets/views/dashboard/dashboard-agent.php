<?php
/**
 * Dashboard personalizado para agentes (role = 'agent')
 */
$pageTitle = 'Dashboard - RIDECO';
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/helpers/menu.php';
require_once __DIR__ . '/../../app/models/Ticket.php';

$pdo = Database::getInstance()->getConnection();
$ticketModel = new Ticket();
$userId = $_SESSION['user_id'] ?? null;
$deptId = $_SESSION['user_department_id'] ?? null;

// Estadísticas para agentes: tickets del departamento
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE department_to_id = ?");
$stmt->execute([$deptId]);
$totalTickets = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE department_to_id = ? AND status = 'open'");
$stmt->execute([$deptId]);
$openTickets = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE department_to_id = ? AND status = 'in_progress'");
$stmt->execute([$deptId]);
$inProgressTickets = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE department_to_id = ? AND status = 'resolved'");
$stmt->execute([$deptId]);
$resolvedTickets = $stmt->fetchColumn();

// Tickets asignados a este agente
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE assigned_to = ?");
$stmt->execute([$userId]);
$assignedToMe = $stmt->fetchColumn();

// Últimos tickets del departamento
$stmt = $pdo->prepare("
    SELECT t.*, u.name as creator_name, u_assigned.name as assigned_name
    FROM tickets t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN users u_assigned ON t.assigned_to = u_assigned.id
    WHERE t.department_to_id = ?
    ORDER BY t.created_at DESC
    LIMIT 10
");
$stmt->execute([$deptId]);
$departmentTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/gestion-tickets/assets/img/favicon_rideco.png" alt="RIDECO">
        </div>
        <?php echo renderMenu(); ?>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header">
            <div style="display:flex; align-items:center; gap:12px;">
                <button class="btn btn-secondary btn-sm" onclick="toggleSidebar()" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Panel de Agente</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Agente'); ?></span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $totalTickets; ?></div>
                        <div class="stat-label">Total del Departamento</div>
                    </div>
                    <div class="stat-icon icon-blue"><i class="fas fa-ticket-alt"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $openTickets; ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                    <div class="stat-icon icon-orange"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $assignedToMe; ?></div>
                        <div class="stat-label">Asignados a Mí</div>
                    </div>
                    <div class="stat-icon icon-purple"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $resolvedTickets; ?></div>
                        <div class="stat-label">Resueltos</div>
                    </div>
                    <div class="stat-icon icon-green"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
        </div>

        <!-- Department Tickets -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h3 class="card-title">Tickets del Departamento</h3>
                <a href="<?php echo BASE_URL; ?>/tickets" class="btn btn-primary btn-sm">Ver Todos</a>
            </div>
            <div class="card-body">
                <?php if (count($departmentTickets) > 0): ?>
                    <div class="table-responsive">
                        <table class="tickets-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Creador</th>
                                    <th>Asignado a</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($departmentTickets, 0, 10) as $ticket): ?>
                                    <tr>
                                        <td><strong>#<?php echo str_pad($ticket['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo htmlspecialchars(substr($ticket['title'], 0, 35)); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['creator_name'] ?? 'Desconocido'); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['assigned_name'] ?? 'Sin asignar'); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = ['open' => 'warning', 'in_progress' => 'primary', 'resolved' => 'success', 'closed' => 'secondary'][$ticket['status']] ?? 'secondary';
                                            $statusLabel = ['open' => 'Abierto', 'in_progress' => 'En Proceso', 'resolved' => 'Resuelto', 'closed' => 'Cerrado'][$ticket['status']] ?? 'Desconocido';
                                            ?>
                                            <span class="badge badge-<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityClass = ['baja' => 'secondary', 'media' => 'warning', 'alta' => 'danger', 'urgente' => 'danger'][$ticket['priority']] ?? 'secondary';
                                            $priorityLabel = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta', 'urgente' => 'Urgente'][$ticket['priority']] ?? 'Desconocida';
                                            ?>
                                            <span class="badge badge-<?php echo $priorityClass; ?>"><?php echo $priorityLabel; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--secondary); padding: 40px;">
                        ✅ No hay tickets pendientes en tu departamento
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

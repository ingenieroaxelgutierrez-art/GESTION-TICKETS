<?php
/**
 * Dashboard personalizado para usuarios normales (role = 'user')
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

// Estadísticas para usuarios normales
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = ?");
$stmt->execute([$userId]);
$totalTickets = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = ? AND status = 'open'");
$stmt->execute([$userId]);
$openTickets = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = ? AND status = 'in_progress'");
$stmt->execute([$userId]);
$inProgressTickets = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = ? AND status = 'resolved'");
$stmt->execute([$userId]);
$resolvedTickets = $stmt->fetchColumn();

// Últimos 5 tickets
$stmt = $pdo->prepare("
    SELECT t.*, c.name as category_name
    FROM tickets t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/gestion-tickets/assets/favicon_rideco.png" alt="ticket">
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
                <h1>Mis Tickets</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $totalTickets; ?></div>
                        <div class="stat-label">Tickets Totales</div>
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
                        <div class="stat-value"><?php echo $inProgressTickets; ?></div>
                        <div class="stat-label">En Proceso</div>
                    </div>
                    <div class="stat-icon icon-purple"><i class="fas fa-sync-alt"></i></div>
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

        <!-- Recent Tickets -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h3 class="card-title">Mis Últimos Tickets</h3>
                <a href="<?php echo BASE_URL; ?>/tickets" class="btn btn-primary btn-sm">Ver Todos</a>
            </div>
            <div class="card-body">
                <?php if (count($recentTickets) > 0): ?>
                    <div class="table-responsive">
                        <table class="tickets-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTickets as $ticket): ?>
                                    <tr>
                                        <td><strong>#<?php echo str_pad($ticket['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo htmlspecialchars(substr($ticket['title'], 0, 40)); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['category_name'] ?? '-'); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = ['open' => 'warning', 'in_progress' => 'primary', 'resolved' => 'success', 'closed' => 'secondary'][$ticket['status']] ?? 'secondary';
                                            $statusLabel = ['open' => 'Abierto', 'in_progress' => 'En Proceso', 'resolved' => 'Resuelto', 'closed' => 'Cerrado'][$ticket['status']] ?? 'Desconocido';
                                            ?>
                                            <span class="badge badge-<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($ticket['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--secondary); padding: 40px;">
                        📝 No hay tickets aún. <a href="<?php echo BASE_URL; ?>/tickets/create">Crear uno ahora</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>


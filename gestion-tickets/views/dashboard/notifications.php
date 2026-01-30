<?php
$pageTitle = $pageTitle ?? 'Notificaciones - TICKETS';
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/helpers/menu.php';
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
                <h1>🔔 Notificaciones</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Controles -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtros</h3>
                </div>
                <div class="card-body" style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <button class="btn btn-primary active" onclick="filterNotifications('all')">📬 Todas</button>
                    <button class="btn btn-secondary" onclick="filterNotifications('unread')">🆕 Sin Leer</button>
                    <button class="btn btn-secondary" onclick="filterNotifications('tickets')"><i class="fas fa-ticket-alt"></i> Tickets</button>
                    <button class="btn btn-secondary" onclick="filterNotifications('comments')">💬 Comentarios</button>
                    <button class="btn btn-danger" onclick="clearAllNotifications()" style="margin-left: auto;">🗑️ Borrar Todo</button>
                </div>
            </div>

            <!-- Lista de Notificaciones -->
            <div class="notifications-list" id="notificationsList" style="margin-top: 20px;">
                <!-- Las notificaciones se cargarán aquí -->
            </div>
        </div>
    </main>
</div>

<style>
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-item {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    display: flex;
    gap: 15px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.notification-item:hover {
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(33, 150, 243, 0.1);
}

.notification-item.unread {
    background: var(--bg-secondary);
    border-left: 4px solid var(--primary);
}

.notification-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 5px;
}

.notification-message {
    color: var(--text-secondary);
    font-size: 14px;
    margin-bottom: 8px;
}

.notification-time {
    font-size: 12px;
    color: var(--text-secondary);
}

.notification-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
    align-items: center;
}

.notification-actions button {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-mark-read {
    background: var(--success);
    color: white;
}

.btn-mark-read:hover {
    background: var(--success-dark, #1e7e34);
}

.btn-delete-notif {
    background: var(--danger);
    color: white;
}

.btn-delete-notif:hover {
    background: var(--danger-dark, #c82333);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 20px;
    color: var(--text-secondary);
}

.empty-state-text {
    font-size: 18px;
    margin-bottom: 10px;
    color: var(--text-primary);
}

/* Responsive */
@media (max-width: 768px) {
    .notification-item {
        flex-direction: column;
    }
    
    .notification-actions {
        justify-content: flex-start;
    }
}
</style>

<script>
const baseUrl = '<?= base_url() ?>';
const apiUrl = `${baseUrl}/api/notifications.php`;

let allNotifications = [];
let currentFilter = 'all';

// Iconos por tipo de notificación
const typeIcons = {
    'ticket': '🎫',
    'comment': '💬',
    'assignment': '👤',
    'status_change': '🔄',
    'system': 'ℹ️'
};

// Cargar al iniciar
document.addEventListener('DOMContentLoaded', () => {
    loadNotifications();
    
    // Actualizar cada 30 segundos
    setInterval(loadNotifications, 30000);
});

// Cargar notificaciones desde API
function loadNotifications() {
    const action = currentFilter === 'all' ? 'list' : 
                   currentFilter === 'unread' ? 'unread' : 
                   `by-type&type=${currentFilter}`;
    
    fetch(`${apiUrl}?action=${action}`, {
        method: 'GET',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            allNotifications = data.data || [];
            renderNotifications();
        } else {
            console.error('Error:', data.message || 'Error desconocido');
            renderEmptyState('Error al cargar notificaciones');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        renderEmptyState('Error de conexión');
    });
}

// Renderizar notificaciones
function renderNotifications() {
    const container = document.getElementById('notificationsList');

    if (!allNotifications || allNotifications.length === 0) {
        renderEmptyState();
        return;
    }

    container.innerHTML = allNotifications.map(n => {
        const isRead = n.read_at !== null && n.read_at !== '';
        const icon = typeIcons[n.type] || 'ℹ️';
        const time = formatTime(n.created_at);
        
        return `
            <div class="notification-item ${!isRead ? 'unread' : ''}" onclick="handleNotificationClick(${n.id}, ${n.related_ticket_id || 'null'})">
                <div class="notification-icon">${icon}</div>
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(n.title)}</div>
                    <div class="notification-message">${escapeHtml(n.message)}</div>
                    <div class="notification-time">${time}</div>
                </div>
                <div class="notification-actions" onclick="event.stopPropagation();">
                    ${!isRead ? `<button class="btn-mark-read" onclick="markAsRead(${n.id})"><i class="fas fa-check"></i> Leída</button>` : ''}
                    <button class="btn-delete-notif" onclick="deleteNotification(${n.id})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
    }).join('');
}

// Manejar click en notificación
function handleNotificationClick(notifId, ticketId) {
    // Marcar como leída
    markAsRead(notifId, false);
    
    // Redirigir al ticket si existe
    if (ticketId) {
        window.location.href = `${baseUrl}/tickets/${ticketId}`;
    }
}

// Estado vacío
function renderEmptyState(message = null) {
    const container = document.getElementById('notificationsList');
    const msg = message || 'No hay notificaciones para mostrar';
    container.innerHTML = `
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
            <div class="empty-state-text">Sin notificaciones</div>
            <p style="color: var(--text-secondary);">${msg}</p>
        </div>
    `;
}

// Filtrar notificaciones
function filterNotifications(type) {
    currentFilter = type;
    
    // Actualizar botones activos
    document.querySelectorAll('.card-body button').forEach(btn => {
        btn.classList.remove('active', 'btn-primary');
        btn.classList.add('btn-secondary');
    });
    
    const activeBtn = event?.target;
    if (activeBtn) {
        activeBtn.classList.remove('btn-secondary');
        activeBtn.classList.add('btn-primary', 'active');
    }

    loadNotifications();
}

// Marcar como leída
function markAsRead(id, reload = true) {
    fetch(`${apiUrl}?action=mark-read&id=${id}`, {
        method: 'GET',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && reload) {
            loadNotifications();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Eliminar notificación
function deleteNotification(id) {
    if (!confirm('¿Eliminar esta notificación?')) return;
    
    fetch(`${apiUrl}?action=delete&id=${id}`, {
        method: 'GET',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        } else {
            alert('Error al eliminar la notificación');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
}

// Eliminar todas
function clearAllNotifications() {
    if (!confirm('¿Estás seguro de que quieres eliminar TODAS las notificaciones? Esta acción no se puede deshacer.')) return;
    
    fetch(`${apiUrl}?action=delete-all`, {
        method: 'GET',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
            alert('✅ Todas las notificaciones han sido eliminadas');
        } else {
            alert('Error al eliminar las notificaciones');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
}

// Utilidades
function formatTime(dateStr) {
    if (!dateStr) return 'Ahora';
    
    const date = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // segundos
    
    if (diff < 60) return 'Hace unos segundos';
    if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Hace ${Math.floor(diff / 3600)}h`;
    if (diff < 604800) return `Hace ${Math.floor(diff / 86400)}d`;
    
    return date.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>


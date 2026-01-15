<?php 
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/helpers/menu.php';
?>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/gestion-tickets/assets/img/favicon_rideco.png" alt="Logo">
        </div>
        <?php echo renderMenu(); ?>

    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="btn btn-secondary btn-sm" onclick="toggleSidebar()" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Mis Tickets</h1>
            </div>
            <div class="topbar-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Stats rápidas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value"><?php echo $myStats['total'] ?? 0; ?></div>
                            <div class="stat-label">Tickets Totales</div>
                        </div>
                        <div class="stat-icon primary">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value"><?php echo $myStats['abiertos'] ?? 0; ?></div>
                            <div class="stat-label">Abiertos</div>
                        </div>
                        <div class="stat-icon warning">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>

                <?php if (isset($userRole) && in_array($userRole, ['agent', 'admin'])): ?>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value"><?php echo $myStats['en_progreso'] ?? 0; ?></div>
                            <div class="stat-label">En Progreso</div>
                        </div>
                        <div class="stat-icon primary">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value"><?php echo $myStats['resueltos'] ?? 0; ?></div>
                            <div class="stat-label">Resueltos</div>
                        </div>
                        <div class="stat-icon success">
                            ✅
                        </div>
                    </div>
                </div>

                <?php if (isset($userRole) && in_array($userRole, ['agent', 'admin'])): ?>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value"><?php echo $myStats['cerrados'] ?? 0; ?></div>
                            <div class="stat-label">Cerrados</div>
                        </div>
                        <div class="stat-icon secondary">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tabla de Tickets -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo isset($userRole) && in_array($userRole, ['agent', 'admin']) ? 'Tickets por Atender' : 'Mis Tickets'; ?></h3>
                    <?php if (!isset($userRole) || $userRole === 'user'): ?>
                    <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/tickets/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nuevo Ticket
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="form-row mb-20">
                        <div class="form-group">
                            <label>Estado</label>
                            <select class="form-control" id="filterStatus">
                                <option value="">Todos</option>
                                <option value="open">Abierto</option>
                                <option value="in_progress">En Proceso</option>
                                <option value="resolved">Resuelto</option>
                                <option value="closed">Cerrado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Prioridad</label>
                            <select class="form-control" id="filterPriority">
                                <option value="">Todas</option>
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Buscar</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="searchTicket"
                                placeholder="Buscar por ID o título..."
                            >
                        </div>
                    </div>

                    <!-- Tabla de Tickets -->
                    <div class="table-responsive">
                        <table id="myTicketsTable" class="tickets-table">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th style="width: 120px;">Fecha</th>
                                    <th>Creador</th>
                                    <th style="max-width: 300px;">Título</th>
                                    <th style="width: 100px;">Prioridad</th>
                                    <th style="width: 120px;">Estado</th>
                                    <th style="width: 150px;">Asignado a</th>
                                    <th style="width: 100px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(isset($myTickets) && count($myTickets) > 0): ?>
                                    <?php foreach($myTickets as $ticket): ?>
                                        <tr data-status="<?php echo $ticket['status']; ?>" 
                                            data-priority="<?php echo $ticket['priority']; ?>"
                                            data-ticket-id="<?php echo $ticket['id']; ?>">
                                            <td>
                                                <strong>#<?php echo str_pad($ticket['id'], 4, '0', STR_PAD_LEFT); ?></strong>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['creator_name'] ?? 'Desconocido'); ?></td>
                                            <td>
                                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?php echo htmlspecialchars($ticket['title'] ?? 'Sin título'); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $priorityClass = [
                                                    'baja' => 'secondary',
                                                    'media' => 'warning',
                                                    'alta' => 'danger',
                                                    'urgente' => 'danger'
                                                ][$ticket['priority']] ?? 'secondary';
                                                
                                                $priorityLabel = [
                                                    'baja' => 'Baja',
                                                    'media' => 'Media',
                                                    'alta' => 'Alta',
                                                    'urgente' => 'Urgente'
                                                ][$ticket['priority']] ?? 'Desconocida';
                                                ?>
                                                <span class="badge badge-<?php echo $priorityClass; ?>">
                                                    <?php echo $priorityLabel; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = [
                                                    'open' => 'warning',
                                                    'in_progress' => 'primary',
                                                    'resolved' => 'success',
                                                    'closed' => 'secondary'
                                                ][$ticket['status']] ?? 'secondary';
                                                
                                                $statusLabel = [
                                                    'open' => 'Abierto',
                                                    'in_progress' => 'En Proceso',
                                                    'resolved' => 'Resuelto',
                                                    'closed' => 'Cerrado'
                                                ][$ticket['status']] ?? 'Desconocido';
                                                ?>
                                                <span class="badge badge-<?php echo $statusClass; ?>">
                                                    <?php echo $statusLabel; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($ticket['assigned_name'] ?? 'Sin asignar'); ?>
                                            </td>
                                            <td>
                                                <button 
                                                    class="btn btn-primary btn-xs view-ticket-btn" 
                                                    data-ticket-id="<?php echo $ticket['id']; ?>"
                                                    title="Ver detalles"
                                                >
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div style="padding: 40px;">
                                                <div style="font-size: 48px; margin-bottom: 20px;">📝</div>
                                                <p style="font-size: 18px; color: var(--secondary); margin-bottom: 20px;">
                                                    <?php echo isset($userRole) && in_array($userRole, ['agent', 'admin']) 
                                                        ? 'No hay tickets pendientes'
                                                        : 'No has creado ningún ticket aún'; ?>
                                                </p>
                                                <?php if (!isset($userRole) || $userRole === 'user'): ?>
                                                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/tickets/create" class="btn btn-primary">
                                                    Crear mi Primer Ticket
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal para ver detalle del ticket -->
<div id="detailModal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title">Detalle del Ticket <span id="ticketIdDisplay"></span></h3>
            <button class="modal-close" onclick="closeModal('detailModal')">&times;</button>
        </div>
        <div class="modal-body" id="ticketDetailContent">
            <!-- El contenido se cargará dinámicamente con AJAX -->
            <div class="text-center" style="padding: 40px;">
                <div class="loading" style="width: 40px; height: 40px; margin: 0 auto;"></div>
                <p style="margin-top: 20px; color: var(--secondary);">Cargando información...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('detailModal')">Cerrar</button>
        </div>
    </div>
</div>
<script>
const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
const userRole = '<?php echo isset($userRole) ? htmlspecialchars($userRole) : 'user'; ?>';

console.log('[DEBUG] Script inicializado');
console.log('[DEBUG] baseUrl:', baseUrl);
console.log('[DEBUG] userRole:', userRole);
console.log('[DEBUG] CSRF_TOKEN disponible:', typeof CSRF_TOKEN !== 'undefined');

// Función para ver el detalle de un ticket con modal mejorado
function viewTicketDetail(ticketId) {
    console.log('[DEBUG] ViewTicketDetail llamado con ID:', ticketId);
    
    const modal = document.getElementById('detailModal');
    const ticketIdDisplay = document.getElementById('ticketIdDisplay');
    const contentArea = document.getElementById('ticketDetailContent');
    
    if (!modal) {
        console.error('[ERROR] Modal detailModal no encontrado');
        alert('Error: No se pudo abrir el modal. Por favor recarga la página.');
        return;
    }
    
    if (ticketIdDisplay) {
        ticketIdDisplay.textContent = '#' + String(ticketId).padStart(4, '0');
    }
    
    modal.classList.add('active');
    
    // Mostrar loading
    if (contentArea) {
        contentArea.innerHTML = `
            <div class="text-center" style="padding: 40px;">
                <div class="loading" style="width: 40px; height: 40px; margin: 0 auto;"></div>
                <p style="margin-top: 20px; color: var(--secondary);">Cargando información...</p>
            </div>
        `;
    }
    
    const url = baseUrl + '/tickets/' + ticketId;
    console.log('[DEBUG] Fetching URL:', url);
    
    // Cargar los detalles del ticket con AJAX
    fetch(url, {
        method: 'GET',
        credentials: 'include',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            console.log('[DEBUG] Response status:', response.status);
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(data => {
            console.log('[DEBUG] Data received:', data);
            console.log('[DEBUG] Ticket object:', data.ticket);
            console.log('[DEBUG] department_to_id value:', data.ticket?.department_to_id);
            console.log('[DEBUG] assigned_to value:', data.ticket?.assigned_to);
            if (data.success) {
                displayTicketDetail(data.ticket, data.comments, data.attachments || []);
            } else {
                if (contentArea) {
                    contentArea.innerHTML = `
                        <div class="alert alert-error">
                            ${data.error ? data.error : 'No se pudo cargar la información del ticket.'}
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('[ERROR]:', error);
            if (contentArea) {
                contentArea.innerHTML = `
                    <div class="alert alert-error">
                        Error al cargar el ticket: ${error.message}<br>
                        Por favor, intenta de nuevo o recarga la página.
                    </div>
                `;
            }
        });
}

function displayTicketDetail(ticket, comments = [], attachments = []) {
    const statusClass = {
        'open': 'warning',
        'in_progress': 'primary',
        'resolved': 'success',
        'closed': 'secondary'
    }[ticket.status] || 'secondary';
    
    const statusLabel = {
        'open': 'Abierto',
        'in_progress': 'En Proceso',
        'resolved': 'Resuelto',
        'closed': 'Cerrado'
    }[ticket.status] || 'Desconocido';
    
    const priorityClass = {
        'baja': 'secondary',
        'media': 'warning',
        'alta': 'danger',
        'urgente': 'danger'
    }[ticket.priority] || 'secondary';
    
    const priorityLabel = {
        'baja': 'Baja',
        'media': 'Media',
        'alta': 'Alta',
        'urgente': 'Urgente'
    }[ticket.priority] || 'Desconocida';
    
    // Determinar si el usuario puede actuar sobre el ticket
    const canAct = userRole === 'admin' || 
                   (userRole === 'agent' && ticket.department_to_id && ticket.department_to_id > 0);
    
    let html = `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <strong style="color: var(--secondary); font-size: 12px;">ESTADO</strong>
                <div style="margin-top: 5px;">
                    <span class="badge badge-${statusClass}">${statusLabel.toUpperCase()}</span>
                </div>
            </div>
            <div>
                <strong style="color: var(--secondary); font-size: 12px;">PRIORIDAD</strong>
                <div style="margin-top: 5px;">
                    <span class="badge badge-${priorityClass}">${priorityLabel.toUpperCase()}</span>
                </div>
            </div>
            <div>
                <strong style="color: var(--secondary); font-size: 12px;">CREADOR</strong>
                <div style="margin-top: 5px;">${ticket.creator_name || 'Desconocido'}</div>
            </div>
            <div>
                <strong style="color: var(--secondary); font-size: 12px;">ASIGNADO A</strong>
                <div style="margin-top: 5px;">${ticket.assigned_name || 'Sin asignar'}</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <strong style="color: var(--secondary); font-size: 12px;">FECHA CREACIÓN</strong>
                <div style="margin-top: 5px;">${new Date(ticket.created_at).toLocaleString('es-MX')}</div>
            </div>
            <div>
                <strong style="color: var(--secondary); font-size: 12px;">ÚLTIMA ACTUALIZACIÓN</strong>
                <div style="margin-top: 5px;">${ticket.updated_at ? new Date(ticket.updated_at).toLocaleString('es-MX') : 'N/A'}</div>
            </div>
        </div>
        
        <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">
        
        <div style="margin-bottom: 15px;">
            <strong style="color: var(--secondary); font-size: 12px;">DEPARTAMENTO ORIGEN</strong>
            <div style="margin-top: 5px;">${ticket.dept_from_name || 'N/A'}</div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <strong style="color: var(--secondary); font-size: 12px;">DEPARTAMENTO DESTINO</strong>
            <div style="margin-top: 5px;">${ticket.dept_to_name || 'N/A'}</div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <strong style="color: var(--secondary); font-size: 12px;">CATEGORÍA</strong>
            <div style="margin-top: 5px;">${ticket.category_name || 'N/A'}</div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <strong style="color: var(--secondary); font-size: 12px;">TÍTULO</strong>
            <div style="margin-top: 5px; font-weight: bold; font-size: 14px;">${ticket.title}</div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <strong style="color: var(--secondary); font-size: 12px;">DESCRIPCIÓN</strong>
            <div style="margin-top: 5px; background: var(--bg-secondary); padding: 15px; border-radius: 10px; line-height: 1.6;">
                ${ticket.description}
            </div>
        </div>
        
        ${ticket.closed_reason ? `
        <div style="margin-bottom: 15px;">
            <strong style="color: var(--secondary); font-size: 12px;">MOTIVO DE CIERRE</strong>
            <div style="margin-top: 5px; background: var(--bg-secondary); padding: 15px; border-radius: 10px; line-height: 1.6;">
                ${ticket.closed_reason}
            </div>
        </div>
        ` : ''}
        
        ${attachments && attachments.length > 0 ? `
        <div style="margin-bottom: 15px;">
            <strong style="color: var(--secondary); font-size: 12px;">📎 ARCHIVOS ADJUNTOS (${attachments.length})</strong>
            <div style="margin-top: 10px; display: grid; gap: 8px;">
                ${attachments.map(att => {
                    const ext = att.original_name.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
                    const isPDF = ext === 'pdf';
                    const icon = isImage ? '🖼️' : (isPDF ? '📄' : '📎');
                    
                    return `
                        <a href="${baseUrl}/tickets/attachment/${att.id}" 
                           target="_blank"
                           style="display: flex; align-items: center; gap: 10px; padding: 10px; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; text-decoration: none; color: var(--text-primary); transition: all 0.2s;"
                           onmouseover="this.style.background='var(--primary-light)'; this.style.borderColor='var(--primary)';"
                           onmouseout="this.style.background='var(--bg-secondary)'; this.style.borderColor='var(--border)';">
                            <span style="font-size: 24px;">${icon}</span>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; font-size: 13px;">${att.original_name}</div>
                                <div style="font-size: 11px; color: var(--secondary);">${isImage ? 'Imagen' : (isPDF ? 'PDF' : 'Documento')}</div>
                            </div>
                            <span style="font-size: 11px; color: var(--secondary);">
                                ${isImage || isPDF ? 'Ver' : 'Descargar'} →
                            </span>
                        </a>
                    `;
                }).join('')}
            </div>
        </div>
        ` : ''}
    `;
    
    // Agregar sección de comentarios
    html += `
        <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">
        
        <div style="margin-bottom: 15px;">
            <strong style="color: var(--secondary); font-size: 12px;">RESPUESTAS (${comments.length})</strong>
            <div style="margin-top: 10px; max-height: 300px; overflow-y: auto; border: 1px solid var(--border); border-radius: 10px; padding: 10px;">
    `;
    
    if (comments.length > 0) {
        comments.forEach(comment => {
            const commentDate = new Date(comment.created_at).toLocaleString('es-MX');
            html += `
                <div style="padding: 10px; border-bottom: 1px solid var(--border); margin-bottom: 10px;">
                    <div style="font-weight: bold; font-size: 12px; color: var(--primary);">
                        ${comment.user_name}
                    </div>
                    <div style="font-size: 11px; color: var(--secondary); margin-bottom: 5px;">
                        ${commentDate}
                    </div>
                    <div style="font-size: 13px; line-height: 1.5; margin-bottom: 8px;">
                        ${comment.message}
                    </div>
                    ${comment.attachments && comment.attachments.length > 0 ? `
                        <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
                            ${comment.attachments.map(att => {
                                const ext = att.original_name.split('.').pop().toLowerCase();
                                const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
                                const isPDF = ext === 'pdf';
                                const icon = isImage ? '🖼️' : (isPDF ? '📄' : '📎');
                                
                                return `
                                    <a href="${baseUrl}/tickets/attachment/${att.id}" 
                                       target="_blank"
                                       style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 4px; text-decoration: none; color: var(--text-primary); font-size: 11px;"
                                       title="${att.original_name}">
                                        <span>${icon}</span>
                                        <span style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${att.original_name}</span>
                                    </a>
                                `;
                            }).join('')}
                        </div>
                    ` : ''}
                </div>
            `;
        });
    } else {
        html += '<div style="text-align: center; padding: 20px; color: var(--secondary);">No hay respuestas aún</div>';
    }
    
    html += '</div></div>';
    
    // Agregar formulario de comentarios si puede actuar
    const currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; ?>;
    if (canAct || ticket.user_id === currentUserId) {
        html += `
            <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">
            <div style="margin-bottom: 15px;">
                <label style="color: var(--secondary); font-size: 12px; font-weight: bold;">AÑADIR RESPUESTA</label>
                <textarea 
                    id="commentInput" 
                    class="form-control" 
                    rows="3"
                    placeholder="Escribe tu respuesta aquí..."
                    style="margin-top: 10px;"
                ></textarea>
                <div style="margin-top: 10px;">
                    <label for="commentAttachments" style="color: var(--secondary); font-size: 11px; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                        <i class="fas fa-paperclip"></i> Adjuntar archivos (máx. 10MB)
                    </label>
                    <input 
                        type="file" 
                        id="commentAttachments" 
                        name="attachments[]" 
                        multiple
                        accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                        style="margin-top: 5px; font-size: 12px;"
                    />
                </div>
                <button 
                    class="btn btn-primary" 
                    style="margin-top: 10px;"
                    onclick="submitComment(${ticket.id})"
                >
                    <i class="fas fa-paper-plane"></i> Enviar Respuesta
                </button>
            </div>
        `;
    }
    
    // Agregar controles de agente si puede actuar
    if (canAct) {
        html += `
            <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="color: var(--secondary); font-size: 12px; font-weight: bold;">CAMBIAR ESTADO</label>
                    <select id="statusSelect" class="form-control" style="margin-top: 5px;">
                        <option value="open" ${ticket.status === 'open' ? 'selected' : ''}>Abierto</option>
                        <option value="in_progress" ${ticket.status === 'in_progress' ? 'selected' : ''}>En Proceso</option>
                        <option value="resolved" ${ticket.status === 'resolved' ? 'selected' : ''}>Resuelto</option>
                        <option value="closed" ${ticket.status === 'closed' ? 'selected' : ''}>Cerrado</option>
                    </select>
                    <button class="btn btn-info btn-sm" style="margin-top: 5px; width: 100%;" onclick="changeStatus(${ticket.id})">
                        Actualizar Estado
                    </button>
                </div>
                <div>
                    <label style="color: var(--secondary); font-size: 12px; font-weight: bold;">CAMBIAR PRIORIDAD</label>
                    <select id="prioritySelect" class="form-control" style="margin-top: 5px;">
                        <option value="baja" ${ticket.priority === 'baja' ? 'selected' : ''}>Baja</option>
                        <option value="media" ${ticket.priority === 'media' ? 'selected' : ''}>Media</option>
                        <option value="alta" ${ticket.priority === 'alta' ? 'selected' : ''}>Alta</option>
                        <option value="urgente" ${ticket.priority === 'urgente' ? 'selected' : ''}>Urgente</option>
                    </select>
                    <button class="btn btn-info btn-sm" style="margin-top: 5px; width: 100%;" onclick="changePriority(${ticket.id})">
                        Actualizar Prioridad
                    </button>
                </div>
            </div>
            <div style="margin-top: 15px;">
                <label style="color: var(--secondary); font-size: 12px; font-weight: bold;">REASIGNAR A AGENTE</label>
                <select id="agentSelect" class="form-control" style="margin-top: 5px;">
                    <option value="">-- Sin asignar --</option>
                </select>
                <button class="btn btn-info btn-sm" style="margin-top: 5px; width: 100%;" onclick="changeAssigned(${ticket.id})">
                    Reasignar
                </button>
            </div>
        `;
    }
    
    // Primero insertar el HTML en el DOM
    document.getElementById('ticketDetailContent').innerHTML = html;
    
    // Luego cargar los agentes si canAct es true
    if (canAct) {
        console.log('[DEBUG] displayTicketDetail - Full ticket object:', ticket);
        console.log('[DEBUG] displayTicketDetail - department_to_id:', ticket.department_to_id);
        console.log('[DEBUG] displayTicketDetail - assigned_to:', ticket.assigned_to);
        
        setTimeout(() => {
            if (ticket.department_to_id) {
                loadAgentsForDepartment(ticket.department_to_id, ticket.assigned_to || 0);
            } else {
                console.error('[ERROR] No department_to_id found in ticket object');
            }
        }, 100);
    }
}

function loadAgentsForDepartment(deptId, currentAgentId = 0) {
    console.log('[DEBUG] loadAgentsForDepartment - deptId:', deptId, 'currentAgentId:', currentAgentId);
    
    if (!deptId || deptId <= 0) {
        console.warn('[WARN] No se puede cargar agentes sin un ID de departamento válido');
        return;
    }
    
    const url = baseUrl + '/api/agents-by-department/' + deptId;
    console.log('[DEBUG] Fetching agents from:', url);
    
    fetch(url, {
        credentials: 'include',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => {
        console.log('[DEBUG] API Response status:', r.status);
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        console.log('[DEBUG] Agents data received:', data);
        const select = document.getElementById('agentSelect');
        if (!select) {
            console.error('[ERROR] agentSelect element not found in DOM');
            return;
        }
        
        select.innerHTML = '<option value="">-- Sin asignar --</option>';
        
        if (data.success && data.agents && data.agents.length > 0) {
            console.log('[DEBUG] Loading', data.agents.length, 'agents');
            data.agents.forEach(agent => {
                const option = document.createElement('option');
                option.value = agent.id;
                option.textContent = agent.name;
                if (agent.id === currentAgentId) option.selected = true;
                select.appendChild(option);
            });
        } else {
            console.warn('[WARN] No agents found for department:', deptId);
        }
    })
    .catch(err => {
        console.error('[ERROR] Error loading agents:', err);
        const select = document.getElementById('agentSelect');
        if (select) {
            select.innerHTML = '<option value="">-- Error cargando agentes --</option>';
        }
    });
}

function changeAssigned(ticketId) {
    const agentId = document.getElementById('agentSelect').value;
    
    const formData = new FormData();
    formData.append('agent_id', agentId);

    fetch(baseUrl + '/tickets/' + ticketId + '/assign', {
        method: 'PUT',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Agente reasignado correctamente', 'success');
                viewTicketDetail(ticketId);
            } else {
                showAlert(data.error || 'Error al reasignar', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        });
}

function submitComment(ticketId) {
    const message = document.getElementById('commentInput').value.trim();
    if (!message) {
        showAlert('El comentario no puede estar vacío', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('message', message);
    formData.append('csrf_token', CSRF_TOKEN);
    
    // Agregar archivos adjuntos si los hay
    const fileInput = document.getElementById('commentAttachments');
    if (fileInput && fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('attachments[]', fileInput.files[i]);
        }
    }

    fetch(baseUrl + '/tickets/' + ticketId + '/comment', {
        method: 'POST',
        credentials: 'include',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Respuesta enviada correctamente', 'success');
                document.getElementById('commentInput').value = '';
                if (fileInput) fileInput.value = '';
                viewTicketDetail(ticketId);
            } else {
                showAlert(data.error || 'Error al enviar respuesta', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        });
}

function changeStatus(ticketId) {
    const status = document.getElementById('statusSelect').value;
    
    if (status === 'closed') {
        const reason = prompt('¿Cuál es el motivo del cierre?');
        if (!reason) return;
        
        submitStatusChange(ticketId, status, reason);
    } else {
        submitStatusChange(ticketId, status);
    }
}

function submitStatusChange(ticketId, status, reason = null) {
    const formData = new FormData();
    formData.append('status', status);
    if (reason) formData.append('reason', reason);

    fetch(baseUrl + '/tickets/' + ticketId + '/status', {
        method: 'PUT',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Estado actualizado correctamente', 'success');
                viewTicketDetail(ticketId);
            } else {
                showAlert(data.error || 'Error al cambiar estado', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        });
}

function changePriority(ticketId) {
    const priority = document.getElementById('prioritySelect').value;
    
    const formData = new FormData();
    formData.append('priority', priority);

    fetch(baseUrl + '/tickets/' + ticketId + '/priority', {
        method: 'PUT',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Prioridad actualizada correctamente', 'success');
                viewTicketDetail(ticketId);
            } else {
                showAlert(data.error || 'Error al cambiar prioridad', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        });
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Cerrar modal al hacer clic fuera
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

// Event listener para todos los botones "Ver"
document.addEventListener('DOMContentLoaded', function() {
    console.log('[DEBUG] DOM Loaded - Inicializando event listeners');
    
    // Agregar event listeners a todos los botones de ver
    const viewButtons = document.querySelectorAll('.view-ticket-btn');
    console.log('[DEBUG] Botones encontrados:', viewButtons.length);
    
    viewButtons.forEach(function(button, index) {
        console.log('[DEBUG] Agregando listener al botón', index);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const ticketId = this.getAttribute('data-ticket-id');
            console.log('[DEBUG] Botón clickeado, Ticket ID:', ticketId);
            if (ticketId) {
                viewTicketDetail(ticketId);
            } else {
                console.error('[ERROR] No se encontró ticket ID en el botón');
                alert('Error: No se encontró el ID del ticket');
            }
        });
    });
    
    // También intentar con delegación de eventos
    const tableBody = document.querySelector('#myTicketsTable tbody');
    if (tableBody) {
        console.log('[DEBUG] Agregando delegación de eventos a la tabla');
        tableBody.addEventListener('click', function(e) {
            const btn = e.target.closest('.view-ticket-btn');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                const ticketId = btn.getAttribute('data-ticket-id');
                console.log('[DEBUG] Click delegado - Ticket ID:', ticketId);
                if (ticketId) {
                    viewTicketDetail(ticketId);
                }
            }
        });
    }
});

// Funcionalidad de filtros
document.getElementById('filterStatus').addEventListener('change', filterTickets);
document.getElementById('filterPriority').addEventListener('change', filterTickets);
document.getElementById('searchTicket').addEventListener('input', filterTickets);

function filterTickets() {
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const priority = document.getElementById('filterPriority').value.toLowerCase();
    const search = document.getElementById('searchTicket').value.toLowerCase();
    
    const rows = document.querySelectorAll('#myTicketsTable tbody tr');
    
    rows.forEach(row => {
        const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
        const rowPriority = (row.getAttribute('data-priority') || '').toLowerCase();
        const rowText = row.textContent.toLowerCase();
        
        const matchStatus = !status || rowStatus === status;
        const matchPriority = !priority || rowPriority === priority;
        const matchSearch = !search || rowText.includes(search);
        
        row.style.display = (matchStatus && matchPriority && matchSearch) ? '' : 'none';
    });
}

// Si se pasó un selectedTicketId, abrir ese ticket automáticamente
<?php if (isset($selectedTicketId)): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        viewTicketDetail(<?php echo (int)$selectedTicketId; ?>);
    }, 500);
});
<?php endif; ?>
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
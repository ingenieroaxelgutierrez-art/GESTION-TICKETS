<?php
$pageTitle = $pageTitle ?? 'Reportes - RIDECO';
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/helpers/menu.php';
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
                <h1><i class="fas fa-chart-bar"></i> Reportes y Análisis</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtros de Reporte</h3>
                </div>
                <div class="card-body">
                    <div class="filter-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="filter-group">
                            <label>Periodo:</label>
                            <select id="reportPeriod">
                                <option value="7d">Últimos 7 días</option>
                                <option value="30d" selected>Últimos 30 días</option>
                                <option value="90d">Últimos 90 días</option>
                                <option value="all">Todo el tiempo</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Estado:</label>
                            <select id="reportStatus">
                                <option value="">Todos</option>
                                <option value="open">Abiertos</option>
                                <option value="in_progress">En Progreso</option>
                                <option value="resolved">Resueltos</option>
                                <option value="closed">Cerrados</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <button class="btn btn-primary" onclick="generateReport()"><i class="fas fa-search"></i> Generar Reporte</button>
                            <button class="btn btn-secondary" onclick="exportReport()"><i class="fas fa-file-csv"></i> Descargar CSV</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Resumen -->
            <div class="stats-grid" style="margin-top: 30px;">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="stat-total">0</div>
                            <div class="stat-label">Tickets Totales</div>
                        </div>
                        <div class="stat-icon primary"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="stat-open">0</div>
                            <div class="stat-label">Abiertos</div>
                        </div>
                        <div class="stat-icon warning"><i class="fas fa-hourglass-half"></i></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="stat-resolved">0</div>
                            <div class="stat-label">Resueltos</div>
                        </div>
                        <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="stat-closed">0</div>
                            <div class="stat-label">Cerrados</div>
                        </div>
                        <div class="stat-icon info"><i class="fas fa-lock"></i></div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Datos -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title">Detalle de Tickets</h3>
                </div>
                <div class="card-body">
                    <table class="table" id="reportTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">Cargando datos... (haz clic en "Generar Reporte")</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-group label {
    font-weight: 600;
    min-width: 80px;
}

.filter-group select,
.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.text-center {
    text-align: center;
}

.stat-icon.info {
    color: #0288d1;
}
</style>

<script>
// BASE_URL definido en header.php

function generateReport() {
    const status = document.getElementById('reportStatus').value;
    const tbody = document.querySelector('#reportTable tbody');

    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center"><i class="fas fa-hourglass-half"></i> Generando reporte...</td>
        </tr>
    `;

    const params = new URLSearchParams();
    if (status) params.append('status', status);

    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
    const url = baseUrl + '/api/tickets' + (params.toString() ? `?${params.toString()}` : '');

    fetch(url, {
        credentials: 'include',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
    .then(({ ok, data }) => {
        if (ok && data.success && Array.isArray(data.data)) {
            renderReportTable(data.data);
            updateReportStats(data.data);
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No se pudo cargar el reporte</td></tr>';
            updateReportStats([]);
        }
    })
    .catch(() => {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Error al obtener datos</td></tr>';
        updateReportStats([]);
    });
}

function renderReportTable(data) {
    const tbody = document.querySelector('#reportTable tbody');
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Sin datos para este reporte</td></tr>';
        return;
    }

    const priorityColors = { baja: '#ccc', media: '#ff9800', alta: '#f44336', urgente: '#c41c3b' };
    
    tbody.innerHTML = data.map(row => {
        const date = row.created_at ? String(row.created_at).slice(0, 10) : (row.date || '');
        const priority = row.priority || 'media';
        const status = row.status || 'open';

        return `
        <tr>
            <td><strong>#${row.id}</strong></td>
            <td>${escapeHtml(row.title || '')}</td>
            <td><span class="badge badge-${status}">${status}</span></td>
            <td><span style="background: ${priorityColors[priority] || '#999'}; color: white; padding: 4px 8px; border-radius: 3px; font-size: 12px;">${priority}</span></td>
            <td>${date}</td>
            <td><button class="btn btn-sm btn-primary" onclick="viewReportTicket(${row.id})">Ver</button></td>
        </tr>`;
    }).join('');
}

// Mostrar datos de ticket en modal
function viewReportTicket(id) {
    const modalHtml = `
        <div id="reportTicketModal" class="modal active">
            <div class="modal-content" style="max-width:700px;">
                <div class="modal-header">
                    <h3 class="modal-title">Detalle del Ticket <span id="rTicketId"></span></h3>
                    <button class="modal-close" onclick="closeReportModal()">&times;</button>
                </div>
                <div class="modal-body" id="rTicketBody">
                    <div style="padding:20px; text-align:center;">Cargando...</div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeReportModal()">Cerrar</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
    
    fetch(baseUrl + '/tickets/' + id, {
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
        .then(({ ok, data }) => {
            if (ok && data.success && data.ticket) {
                const t = data.ticket;
                document.getElementById('rTicketId').textContent = `#${String(t.id).padStart(4,'0')}`;
                document.getElementById('rTicketBody').innerHTML = `
                    <p><strong>Título:</strong> ${escapeHtml(t.title || '')}</p>
                    <p><strong>Descripción:</strong><br>${escapeHtml(t.description || '')}</p>
                    <p><strong>Usuario:</strong> ${escapeHtml(t.creator_name || t.user_name || '')}</p>
                    <p><strong>Departamento:</strong> ${escapeHtml(t.dept_to_name || t.department_name || '')}</p>
                    <p><strong>Categoría:</strong> ${escapeHtml(t.category_name || '')}</p>
                    <p><strong>Estado:</strong> ${escapeHtml(t.status || '')}</p>
                `;
            } else {
                const msg = (data && data.error) ? escapeHtml(data.error) : 'No se pudo cargar el ticket';
                document.getElementById('rTicketBody').innerHTML = `<div class="alert alert-error">${msg}</div>`;
            }
        })
        .catch(() => {
            document.getElementById('rTicketBody').innerHTML = '<div class="alert alert-error">Error al obtener datos</div>';
        });
}

function closeReportModal() {
    const modal = document.getElementById('reportTicketModal');
    if (modal) modal.remove();
}

function updateReportStats(data) {
    const total = data.length;
    const open = data.filter(d => d.status === 'open').length;
    const resolved = data.filter(d => d.status === 'resolved').length;
    const closed = data.filter(d => d.status === 'closed').length;

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-open').textContent = open;
    document.getElementById('stat-resolved').textContent = resolved;
    document.getElementById('stat-closed').textContent = closed;
}

function exportReport() {
    const params = new URLSearchParams();
    const status = document.getElementById('reportStatus').value;
    if (status) params.append('status', status);
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
    const url = baseUrl + '/api/tickets/export' + (params.toString() ? `?${params.toString()}` : '');
    window.location.href = url;
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Cargar reporte al iniciar
document.addEventListener('DOMContentLoaded', generateReport);
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
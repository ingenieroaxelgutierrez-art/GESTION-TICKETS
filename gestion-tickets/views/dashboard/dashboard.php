<?php
$pageTitle = 'Dashboard - RIDECO';
include __DIR__ . '/../layouts/header.php';
include_once __DIR__. '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/helpers/menu.php';

$pdo = Database::getInstance()->getConnection();
$config = ['pdo' => $pdo];

$stmt = $config['pdo']->query("SELECT COUNT(*) FROM tickets");
$totalTickets = $stmt->fetchColumn();
$stmt = $config['pdo']->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'");
$pendingTickets = $stmt->fetchColumn();
$stmt = $config['pdo']->query("SELECT COUNT(*) FROM tickets WHERE status = 'in_progress'");
$inProgressTickets = $stmt->fetchColumn(); 
$stmt = $config['pdo']->query("SELECT COUNT(*) FROM tickets WHERE status = 'resolved'");
$resolvedTickets = $stmt->fetchColumn();

$ticketsHoy = $pdo->query("SELECT COUNT(*) FROM tickets WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$prioridadAlta = $pdo->query("SELECT COUNT(*) FROM tickets WHERE priority = 'alta' OR priority = 'urgente'")->fetchColumn();
$avgResponseTime = $pdo->query("
    SELECT ROUND(AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) / 60, 1)
    FROM tickets 
        WHERE updated_at IS NOT NULL 
            AND status != 'open'
")->fetchColumn() ?? 0;
$acgResponseTime = $avgResponseTime ?? 0;
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
                    <h1>Dashboard</h1>
                </div>
            <div class="user-info">
                <div class="user-avatar">A</div>
                <span class="user-name">Administrador</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $totalTickets; ?></div>
                        <div class="stat-label">Total de Tickets</div>
                    </div>
                    <div class="stat-icon icon-blue"><i class="fas fa-ticket-alt"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $pendingTickets; ?></div>
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

        <!-- Metrics Row -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $avgResponseTime; ?> h</div>
                        <div class="stat-label">Tiempo Promedio de Respuesta</div>
                    </div>
                    <div class="stat-icon icon-teal"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $ticketsHoy; ?></div>
                        <div class="stat-label">Tickets de Hoy</div>
                    </div>
                    <div class="stat-icon icon-blue"><i class="fas fa-calendar"></i></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $prioridadAlta; ?></div>
                        <div class="stat-label">Prioridad Alta</div>
                    </div>
                    <div class="stat-icon icon-red"><i class="fas fa-exclamation-circle"></i></div>
                </div>
            </div>
        </div>

        <!-- Tickets Section -->
        <div class="tickets-section">
            <div class="section-header">
                <h2>Tickets Recientes</h2>
                <div class="header-actions">
                    <button class="btn btn-secondary" id="filterBtn" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i>
                        <span>Filtrar</span>
                    </button>
                    <button class="btn btn-success" onclick="exportReport()">
                        <i class="fas fa-download"></i>
                        <span>Exportar Reporte</span>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters" id="filterSection">
                <div class="filter-group">
                    <label>Estado</label>
                    <select>
                        <option>Todos</option>
                        <option>Pendiente</option>
                        <option>En Proceso</option>
                        <option>Resuelto</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Prioridad</label>
                    <select>
                        <option>Todas</option>
                        <option>Baja</option>
                        <option>Media</option>
                        <option>Alta</option>
                        <option>Urgente</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Departamento Destino</label>
                    <select>
                        <option>Todos</option>
                        <option>TI</option>
                        <option>Procesos</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Emisor</th>
                            <th>Dept. Origen</th>
                            <th>Dept. Destino</th>
                            <th>Incidencia</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ticketsTableBody">
                        <!-- Los tickets se cargarán aquí dinámicamente -->
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                                <i class="fas fa-hourglass-half"></i> Cargando tickets...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Editar Ticket -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Editar Ticket</h3>
                <button class="modal-close" onclick="closeModal('editModal')">×</button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editTicketId">
                    
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" id="editTitle" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="editDescription" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Prioridad</label>
                        <select id="editPriority" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Estado</label>
                        <select id="editStatus" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <option value="open">Abierto</option>
                            <option value="in_progress">En Proceso</option>
                            <option value="resolved">Resuelto</option>
                            <option value="closed">Cerrado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Categoría</label>
                        <select id="editCategory" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <?php 
                            $categories = new Category();
                            foreach($categories->all() as $cat): 
                            ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Departamento</label>
                        <select id="editDepartment" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <?php 
                            $departments = new Department();
                            foreach($departments->all() as $dept): 
                            ?>
                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancelar</button>
                <button class="btn btn-success" onclick="saveTicketChanges()"><i class="fas fa-save"></i> Guardar Cambios</button>
            </div>
        </div>
    </div>

    <!-- Modal Reasignar Ticket -->
    <div id="reassignModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Reasignar Ticket</h3>
                <button class="modal-close" onclick="closeModal('reassignModal')">×</button>
            </div>
            <div class="modal-body">
                <form id="reassignForm">
                    <input type="hidden" id="reassignTicketId">
                    
                    <div class="form-group">
                        <label>Asignar Agente</label>
                        <select id="reassignAgent" class="form-control" required>
                            <option value="">Seleccionar agente...</option>
                            <?php 
                            $user = new User();
                            $agents = $user->getByRole('agent');
                            foreach($agents as $agent): 
                            ?>
                                <option value="<?= $agent['id'] ?>"><?= htmlspecialchars($agent['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('reassignModal')">Cancelar</button>
                <button class="btn btn-success" onclick="saveReassignment()"><i class="fas fa-sync-alt"></i> Reasignar</button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let allTickets = [];
        let currentFilters = {
            status: '',
            priority: '',
            department: ''
        };

        // Cargar tickets en el dashboard
        function loadTickets() {
            const params = new URLSearchParams();
            if (currentFilters.status) params.append('status', currentFilters.status);
            if (currentFilters.priority) params.append('priority', currentFilters.priority);
            if (currentFilters.department) params.append('department', currentFilters.department);

            const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
            const url = baseUrl + '/api/tickets' + (params.toString() ? `?${params.toString()}` : '');
            console.log('Fetching tickets from:', url);

            fetch(url, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success && Array.isArray(data.data)) {
                    allTickets = data.data;
                    renderTickets(data.data);
                } else {
                    console.error('Error al cargar tickets:', data);
                    renderEmptyState();
                }
            })
            .catch(error => {
                console.error('Error fetching tickets:', error);
                renderEmptyState();
            });
        }

        // Renderizar tickets en la tabla
        function renderTickets(tickets) {
            const tbody = document.getElementById('ticketsTableBody');
            
            if (!tickets || tickets.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                            <i class="fas fa-inbox"></i> No hay tickets para mostrar
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = tickets.map(ticket => `
                <tr>
                    <td><span class="ticket-id">#${String(ticket.id).padStart(4, '0')}</span></td>
                    <td>${formatDate(ticket.created_at)}</td>
                    <td>${ticket.user_name || 'N/A'}</td>
                    <td>${ticket.department_name || 'N/A'}</td>
                    <td><span class="badge badge-${ticket.category_id}">${ticket.category_name || 'N/A'}</span></td>
                    <td>${truncateText(ticket.title, 40)}</td>
                    <td><span class="badge badge-${ticket.priority}">${formatPriority(ticket.priority)}</span></td>
                    <td><span class="badge badge-${ticket.status}">${formatStatus(ticket.status)}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-icon btn-view" title="Ver" onclick="viewTicket(${ticket.id})"><i class="fas fa-eye"></i></button>
                            <button class="btn-icon btn-edit" title="Editar" onclick="editTicket(${ticket.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-reassign" title="Reasignar" onclick="reassignTicket(${ticket.id})"><i class="fas fa-sync-alt"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Estado vacío
        function renderEmptyState() {
            const tbody = document.getElementById('ticketsTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                        <i class="fas fa-inbox"></i> No hay datos disponibles
                    </td>
                </tr>
            `;
        }

        // Utilidades de formato
        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
        }

        function formatStatus(status) {
            const statuses = {
                'open': 'Abierto',
                'in_progress': 'En Proceso',
                'resolved': 'Resuelto',
                'closed': 'Cerrado'
            };
            return statuses[status] || status;
        }

        function formatPriority(priority) {
            const priorities = {
                'baja': 'Baja',
                'media': 'Media',
                'alta': 'Alta',
                'urgente': 'Urgente'
            };
            return priorities[priority] || priority;
        }

        function truncateText(text, length) {
            if (!text) return 'N/A';
            return text.length > length ? text.substring(0, length) + '...' : text;
        }

        // Filtros
        function toggleFilters() {
            const filterSection = document.getElementById('filterSection');
            if (filterSection) {
                filterSection.style.display = filterSection.style.display === 'none' ? 'block' : 'none';
            }
        }

        function applyFilters() {
            currentFilters = {
                status: document.getElementById('filterStatus')?.value || '',
                priority: document.getElementById('filterPriority')?.value || '',
                department: document.getElementById('filterDepartment')?.value || ''
            };
            loadTickets();
        }

        function clearFilters() {
            currentFilters = { status: '', priority: '', department: '' };
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterPriority').value = '';
            document.getElementById('filterDepartment').value = '';
            loadTickets();
        }

        // Acciones de tickets
        function viewTicket(ticketId) {
            const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
            window.location.href = baseUrl + '/tickets/' + ticketId;
        }

        function editTicket(ticketId) {
            const ticket = allTickets.find(t => t.id === ticketId);
            if (ticket) {
                document.getElementById('editTicketId').value = ticket.id;
                document.getElementById('editTitle').value = ticket.title;
                document.getElementById('editDescription').value = ticket.description;
                document.getElementById('editStatus').value = ticket.status;
                document.getElementById('editPriority').value = ticket.priority;
                document.getElementById('editCategory').value = ticket.category_id;
                document.getElementById('editDepartment').value = ticket.department_id;
                openEditModal();
            }
        }

        function reassignTicket(ticketId) {
            const ticket = allTickets.find(t => t.id === ticketId);
            if (ticket) {
                document.getElementById('reassignTicketId').value = ticket.id;
                document.getElementById('reassignAgent').value = ticket.assigned_to || '';
                openReassignModal();
            }
        }

        function saveTicketChanges() {
            const ticketId = document.getElementById('editTicketId').value;
            const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
            const data = {
                title: document.getElementById('editTitle').value,
                description: document.getElementById('editDescription').value,
                status: document.getElementById('editStatus').value,
                priority: document.getElementById('editPriority').value,
                category_id: document.getElementById('editCategory').value,
                department_id: document.getElementById('editDepartment').value
            };

            fetch(baseUrl + '/api/tickets/' + ticketId, {
                method: 'PUT',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': '<?= get_csrf_token() ?>'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Ticket actualizado exitosamente');
                    closeModal('editModal');
                    loadTickets();
                } else {
                    alert('Error al actualizar: ' + (result.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar cambios');
            });
        }

        function saveReassignment() {
            const ticketId = document.getElementById('reassignTicketId').value;
            const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
            const data = {
                assigned_to: document.getElementById('reassignAgent').value
            };

            fetch(baseUrl + '/api/tickets/' + ticketId, {
                method: 'PUT',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': '<?= get_csrf_token() ?>'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Ticket reasignado exitosamente');
                    closeModal('reassignModal');
                    loadTickets();
                } else {
                    alert('Error al reasignar: ' + (result.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al reasignar ticket');
            });
        }

        function exportReport() {
            const params = new URLSearchParams();
            if (currentFilters.status) params.append('status', currentFilters.status);
            if (currentFilters.priority) params.append('priority', currentFilters.priority);
            if (currentFilters.department) params.append('department', currentFilters.department);

            const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
            window.location.href = baseUrl + '/api/tickets/export' + (params.toString() ? `?${params.toString()}` : '');
        }

        // Modales
        function openEditModal() {
            document.getElementById('editModal').classList.add('active');
        }

        function openReassignModal() {
            document.getElementById('reassignModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Cerrar modal al hacer clic fuera
        document.addEventListener('DOMContentLoaded', function() {
            loadTickets();

            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('active');
                    }
                });
            });
        });

        // Cargar tickets cada 30 segundos
        setInterval(loadTickets, 30000);
    </script>

    </div> <!-- .dashboard -->

<?php include __DIR__ . '/../layouts/footer.php';?>
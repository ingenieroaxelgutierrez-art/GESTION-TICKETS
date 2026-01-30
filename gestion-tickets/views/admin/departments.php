<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/gestion-tickets/assets/favicon_rideco.png" alt="tickets">
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/dashboard">
                    <span class="menu-icon"><i class="fas fa-chart-line"></i></span><span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/admin/users">
                    <span class="menu-icon"><i class="fas fa-users"></i></span><span>Usuarios</span>
                </a>
            </li>
            <li>
                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/admin/departments" class="active">
                    <span class="menu-icon"><i class="fas fa-building"></i></span><span>Departamentos</span>
                </a>
            </li>
            <li>
                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/admin/categories">
                    <span class="menu-icon"><i class="fas fa-list"></i></span><span>Categorías</span>
                </a>
            </li>
            <li>
                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/tickets">
                    <span class="menu-icon"><i class="fas fa-ticket-alt"></i></span><span>Tickets</span>
                </a>
            </li>
            <li>
                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/logout">
                    <span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span><span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header">
            <div style="display:flex; align-items:center; gap:12px;">
                <button class="btn btn-secondary btn-sm" onclick="toggleSidebar()" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><i class="fas fa-building"></i> Gestión de Departamentos</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Formulario para crear departamento -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus"></i> Crear Nuevo Departamento</h3>
                </div>
                <div class="card-body">
                    <form id="departmentForm" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="dept_name">Nombre del Departamento *</label>
                            <input type="text" id="dept_name" name="name" required placeholder="Ingresa el nombre" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>

                        <div class="form-group">
                            <label for="dept_type">Tipo *</label>
                            <select id="dept_type" name="type" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="">-- Selecciona --</option>
                                <option value="emisor">Emisor</option>
                                <option value="receptor">Receptor</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="grid-column: 1 / -1; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s;">✓ Crear Departamento</button>
                    </form>
                    <div id="formMessage" class="alert" style="display:none; margin-top: 15px;"></div>
                </div>
            </div>

            <!-- Tabla de departamentos -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Departamentos Registrados</h3>
                </div>
                <div class="card-body">
                    <table id="departmentsTable" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: var(--bg-secondary); border-bottom: 2px solid var(--border);">
                                <th style="padding: 12px; text-align: left; font-weight: 600;">ID</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Nombre</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Tipo</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Fecha Creación</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center; color: #999;"><i class="fas fa-hourglass-half"></i> Cargando departamentos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// BASE_URL y CSRF_TOKEN definidos en header.php

// Cargar departamentos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    loadDepartments();
    
    // Enviar formulario
    document.getElementById('departmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createDepartment();
    });
});

function loadDepartments() {
    fetch(`${BASE_URL}/admin/departments`, {
        method: 'GET',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderDepartments(data.data);
        } else {
            showMessage('Error al cargar departamentos: ' + data.error, 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showMessage('Error de conexión', 'error');
    });
}

function renderDepartments(departments) {
    const tbody = document.querySelector('#departmentsTable tbody');
    if (departments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #999;">📭 No hay departamentos</td></tr>';
        return;
    }
    
    tbody.innerHTML = departments.map(dept => `
        <tr style="border-bottom: 1px solid #eee; transition: background 0.2s;">
            <td style="padding: 12px;"><strong>#${dept.id}</strong></td>
            <td style="padding: 12px;"><strong>${escapeHtml(dept.name)}</strong></td>
            <td style="padding: 12px;">
                <span style="background: ${dept.type === 'emisor' ? '#3498db' : '#e74c3c'}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; white-space: nowrap;">
                    ${dept.type === 'emisor' ? '📤 Emisor' : '📥 Receptor'}
                </span>
            </td>
            <td style="padding: 12px; color: #666; font-size: 12px;">${new Date(dept.created_at).toLocaleDateString('es-MX')}</td>
            <td style="padding: 12px; white-space: nowrap;">
                <button class="btn btn-sm btn-danger" onclick="deleteDepartment(${dept.id})" style="padding: 6px 10px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: 500; transition: background 0.2s;">🗑️ Eliminar</button>
            </td>
        </tr>
    `).join('');
}

function createDepartment() {
    const formData = new FormData(document.getElementById('departmentForm'));
    formData.append('csrf_token', CSRF_TOKEN);

    fetch(`${BASE_URL}/admin/departments`, {
        method: 'POST',
        credentials: 'include',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showMessage('Departamento creado exitosamente', 'success');
            document.getElementById('departmentForm').reset();
            loadDepartments();
        } else {
            showMessage('Error: ' + data.error, 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showMessage('Error de conexión', 'error');
    });
}

function deleteDepartment(id) {
    if (!confirm('¿Estás seguro? Esta acción no se puede deshacer.')) return;
    alert('Función de eliminar no implementada aún en el backend');
}

function showMessage(msg, type) {
    const msgDiv = document.getElementById('formMessage');
    const bgColor = type === 'success' ? '#27ae60' : '#e74c3c';
    const textColor = 'white';
    msgDiv.style.cssText = `background: ${bgColor}; color: ${textColor}; padding: 12px; border-radius: 4px; margin-bottom: 15px; display: block; border-left: 4px solid ${type === 'success' ? '#229954' : '#c0392b'};`;
    msgDiv.textContent = (type === 'success' ? '✓ ' : '✗ ') + msg;
    setTimeout(() => msgDiv.style.display = 'none', 5000);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>


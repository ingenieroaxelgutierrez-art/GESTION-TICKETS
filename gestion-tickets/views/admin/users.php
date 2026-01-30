<?php
$pageTitle = $pageTitle ?? 'Usuarios - Admin';
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/helpers/menu.php';
?>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/gestion-tickets/assets/favicon_rideco.png" alt="tickets">
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
                <h1><i class="fas fa-users"></i> Gestión de Usuarios</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Formulario para crear usuario -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus"></i> Crear Nuevo Usuario</h3>
                </div>
                <div class="card-body">
                    <form id="userForm">
                        <?php echo csrf_field(); ?>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="user_name">Nombre Completo *</label>
                                <input type="text" id="user_name" name="name" required placeholder="Ej: Juan Pérez" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>

                            <div class="form-group">
                                <label for="user_email">Correo Electrónico *</label>
                                <input type="email" id="user_email" name="email" required placeholder="correo@empresa.com" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>

                            <div class="form-group">
                                <label for="user_password">Contraseña *</label>
                                <input type="password" id="user_password" name="password" required placeholder="Mínimo 6 caracteres" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>

                            <div class="form-group">
                                <label for="user_dept">Departamento *</label>
                                <select id="user_dept" name="department_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">-- Cargando departamentos --</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="user_role">Rol *</label>
                                <select id="user_role" name="role" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="user"><i class="fas fa-user"></i> Usuario</option>
                                    <option value="agent"><i class="fas fa-tools"></i> Agente</option>
                                    <option value="admin"><i class="fas fa-crown"></i> Administrador</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" style="width: 100%; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s;">✓ Crear Usuario</button>
                            </div>
                        </div>
                    </form>
                    <div id="formMessage" class="alert" style="display:none; margin-top: 15px;"></div>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Usuarios Registrados</h3>
                </div>
                <div class="card-body">
                    <table id="usersTable" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: var(--bg-secondary); border-bottom: 2px solid var(--border);">
                                <th style="padding: 12px; text-align: left; font-weight: 600;">ID</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Nombre</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Email</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Departamento</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Rol</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Estado</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" style="padding: 20px; text-align: center; color: #999;"><i class="fas fa-hourglass-half"></i> Cargando usuarios...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
// Funciones para gestión de usuarios
// CSRF_TOKEN ya está definido en header.php, solo obtenemos la referencia
const CSRF_META = document.querySelector('meta[name="csrf-token"]');

document.addEventListener('DOMContentLoaded', function(){
    loadDepartments();
    loadUsers();
    
    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createUser();
    });
});

function loadDepartments() {
    fetch(`${BASE_URL}/admin/departments`, {
        method: 'GET',
        credentials: 'include',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('user_dept');
            select.innerHTML = '<option value="">-- Selecciona --</option>';
            select.innerHTML += data.data.map(d => 
                `<option value="${d.id}">${escapeHtml(d.name)}</option>`
            ).join('');
        }
    })
    .catch(err => console.error('Error:', err));
}

function loadUsers() {
    console.log('[loadUsers] Iniciando fetch a:', `${BASE_URL}/admin/users`);
    console.log('[loadUsers] CSRF_TOKEN:', CSRF_TOKEN);
    console.log('[loadUsers] Enviando petición con credentials: include');
    
    fetch(`${BASE_URL}/admin/users`, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'include'
    })
    .then(r => {
        console.log('[loadUsers] Response status:', r.status);
        console.log('[loadUsers] Response headers:', {
            'Content-Type': r.headers.get('Content-Type'),
            'Set-Cookie': r.headers.get('Set-Cookie')
        });
        return r.json();
    })
    .then(data => {
        console.log('[loadUsers] Respuesta de usuarios:', data);
        if (data.success && data.data) {
            console.log('[loadUsers] Usuarios recibidos:', data.data.length);
            renderUsers(data.data);
        } else {
            console.error('[loadUsers] Error en respuesta:', data);
            document.querySelector('#usersTable tbody').innerHTML = 
                '<tr><td colspan="7" class="text-center">Error al cargar usuarios: ' + (data.error || 'sin datos') + '</td></tr>';
        }
    })
    .catch(err => {
        console.error('[loadUsers] Error en fetch:', err);
        document.querySelector('#usersTable tbody').innerHTML = 
            '<tr><td colspan="7" class="text-center">Error de conexión: ' + err.message + '</td></tr>';
    });
}

function renderUsers(users) {
    const tbody = document.querySelector('#usersTable tbody');
    console.log('Renderizando usuarios:', users);
    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="padding: 20px; text-align: center; color: #999;">📭 No hay usuarios</td></tr>';
        return;
    }
    
    const roleColors = {
        'user': '#3498db',
        'agent': '#f39c12',
        'admin': '#e74c3c'
    };

    const roleEmojis = {
        'user': '👤',
        'agent': '🔧',
        'admin': '👑'
    };
    
    tbody.innerHTML = users.map(u => `
        <tr style="border-bottom: 1px solid #eee; transition: background 0.2s;">
            <td style="padding: 12px;"><strong>#${u.id}</strong></td>
            <td style="padding: 12px;"><strong>${escapeHtml(u.name)}</strong></td>
            <td style="padding: 12px;">${escapeHtml(u.email)}</td>
            <td style="padding: 12px;">${escapeHtml(u.department_name || 'Sin departamento')}</td>
            <td style="padding: 12px;">
                <span style="background: ${roleColors[u.role] || '#999'}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; white-space: nowrap;">
                    ${roleEmojis[u.role] || '?'} ${u.role}
                </span>
            </td>
            <td style="padding: 12px;">
                <span style="background: ${u.active ? '#27ae60' : '#e74c3c'}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; white-space: nowrap;">
                    ${u.active ? '✓ Activo' : '✗ Inactivo'}
                </span>
            </td>
            <td style="padding: 12px; white-space: nowrap;">
                <button onclick="changePassword(${u.id})" style="padding: 6px 10px; margin-right: 5px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: 500; transition: background 0.2s;" onhover="this.style.background='#2980b9'">🔑 Pass</button>
                <button onclick="deleteUser(${u.id})" style="padding: 6px 10px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: 500; transition: background 0.2s;" onhover="this.style.background='#c0392b'">🗑️ Eliminar</button>
            </td>
        </tr>
    `).join('');
}

function createUser() {
    const formData = new FormData(document.getElementById('userForm'));
    formData.append('csrf_token', CSRF_TOKEN);

    fetch(`${BASE_URL}/admin/users`, {
        method: 'POST',
        body: formData,
        credentials: 'include',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMessage('Usuario creado exitosamente', 'success');
            document.getElementById('userForm').reset();
            loadUsers();
        } else {
            showMessage('Error: ' + data.error, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showMessage('Error de conexión', 'error');
    });
}

function editUser(id) {
    alert('Edición de usuario - función aún no implementada completamente');
}

function changePassword(id) {
    const newPassword = prompt('Ingresa la nueva contraseña (mín. 6 caracteres):');
    if (!newPassword) return;
    
    const formData = new FormData();
    formData.append('password', newPassword);
    formData.append('csrf_token', CSRF_TOKEN);

    fetch(`${BASE_URL}/admin/users/${id}/password`, {
        method: 'POST',
        body: formData,
        credentials: 'include',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMessage('Contraseña cambiada exitosamente', 'success');
        } else {
            showMessage('Error: ' + data.error, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showMessage('Error de conexión', 'error');
    });
}

function showMessage(msg, type) {
    const msgDiv = document.getElementById('formMessage');
    const bgColor = type === 'success' ? '#27ae60' : '#e74c3c';
    const textColor = 'white';
    msgDiv.style.cssText = `background: ${bgColor}; color: ${textColor}; padding: 12px; border-radius: 4px; margin-bottom: 15px; display: block; border-left: 4px solid ${type === 'success' ? '#229954' : '#c0392b'};`;
    msgDiv.textContent = (type === 'success' ? '✓ ' : '✗ ') + msg;
    setTimeout(() => msgDiv.style.display = 'none', 5000);
}

function deleteUser(id) {
    if (!confirm('¿Estás seguro? Esta acción no se puede deshacer.')) return;
    
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);

    fetch(`${BASE_URL}/admin/users/${id}`, {
        method: 'DELETE',
        body: formData,
        credentials: 'include',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMessage('Usuario eliminado exitosamente', 'success');
            loadUsers();
        } else {
            showMessage('Error: ' + (data.error || 'No se pudo eliminar'), 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showMessage('Error de conexión', 'error');
    });
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

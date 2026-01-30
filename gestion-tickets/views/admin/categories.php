<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="/gestion-tickets/assets/favicon_rideco.png" alt="ticket">
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
                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/admin/departments">
                    <span class="menu-icon"><i class="fas fa-building"></i></span><span>Departamentos</span>
                </a>
            </li>
            <li>
                <a href="<?php echo (defined('BASE_URL') ? BASE_URL : (isset($BASE_URL)?$BASE_URL:'')) ?: '/gestion-tickets'; ?>/admin/categories" class="active">
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
                <h1><i class="fas fa-list"></i> Gestión de Categorías</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Formulario para crear categoría -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus"></i> Crear Nueva Categoría</h3>
                </div>
                <div class="card-body">
                    <form id="categoryForm" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group">
                            <label for="cat_dept">Departamento *</label>
                            <select id="cat_dept" name="department_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="">-- Selecciona --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cat_name">Nombre de la Categoría *</label>
                            <input type="text" id="cat_name" name="name" required placeholder="Ingresa el nombre" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="cat_desc">Descripción</label>
                            <textarea id="cat_desc" name="description" rows="3" placeholder="Descripción opcional" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="grid-column: 1 / -1; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s;">✓ Crear Categoría</button>
                    </form>
                    <div id="formMessage" class="alert" style="display:none; margin-top: 15px;"></div>
                </div>
            </div>

            <!-- Tabla de categorías -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Categorías Registradas</h3>
                </div>
                <div class="card-body">
                    <table id="categoriesTable" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: var(--bg-secondary); border-bottom: 2px solid var(--border);">
                                <th style="padding: 12px; text-align: left; font-weight: 600;">ID</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Nombre</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Departamento</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Descripción</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center; color: #999;"><i class="fas fa-hourglass-half"></i> Cargando categorías...</td>
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

// Cargar al iniciar
document.addEventListener('DOMContentLoaded', function() {
    loadDepartments();
    loadCategories();
    
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createCategory();
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
            const select = document.getElementById('cat_dept');
            select.innerHTML = '<option value="">-- Selecciona un departamento --</option>';
            select.innerHTML += data.data.map(dept => 
                `<option value="${dept.id}">${escapeHtml(dept.name)}</option>`
            ).join('');
        }
    })
    .catch(err => console.error('Error:', err));
}

function loadCategories() {
    fetch(`${BASE_URL}/admin/categories`, {
        method: 'GET',
        credentials: 'include',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderCategories(data.data);
        } else {
            showMessage('Error al cargar categorías: ' + data.error, 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showMessage('Error de conexión', 'error');
    });
}

function renderCategories(categories) {
    const tbody = document.querySelector('#categoriesTable tbody');
    if (categories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #999;">📭 No hay categorías</td></tr>';
        return;
    }
    
    tbody.innerHTML = categories.map(cat => `
        <tr style="border-bottom: 1px solid #eee; transition: background 0.2s;">
            <td style="padding: 12px;"><strong>#${cat.id}</strong></td>
            <td style="padding: 12px;"><strong>${escapeHtml(cat.name)}</strong></td>
            <td style="padding: 12px;">
                <span style="background: #9b59b6; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; white-space: nowrap;">
                    ${escapeHtml(cat.department_name || 'Sin dept.')}
                </span>
            </td>
            <td style="padding: 12px; color: #666; font-size: 13px; max-width: 250px; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(cat.description || '')}</td>
            <td style="padding: 12px; white-space: nowrap;">
                <button class="btn btn-sm btn-danger" onclick="deleteCategory(${cat.id})" style="padding: 6px 10px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: 500; transition: background 0.2s;">🗑️ Eliminar</button>
            </td>
        </tr>
    `).join('');
}

function createCategory() {
    const formData = new FormData(document.getElementById('categoryForm'));
    formData.append('csrf_token', CSRF_TOKEN);

    fetch(`${BASE_URL}/admin/categories`, {
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
            showMessage('Categoría creada exitosamente', 'success');
            document.getElementById('categoryForm').reset();
            loadCategories();
        } else {
            showMessage('Error: ' + data.error, 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showMessage('Error de conexión', 'error');
    });
}

function deleteCategory(id) {
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


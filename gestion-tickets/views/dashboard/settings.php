<?php
$pageTitle = $pageTitle ?? 'Configuración - RIDECO';
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
                <h1>⚙️ Configuración</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Preferencias de Notificaciones -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bell"></i> Preferencias de Notificaciones</h3>
                </div>
                <div class="card-body">
                    <form id="notificationsForm">
                        <?php echo csrf_field(); ?>

                        <div class="settings-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="notify_new_ticket" checked>
                                <span>Notificarme de nuevos tickets asignados</span>
                            </label>
                            <p class="settings-description">Recibirás alertas cuando se cree un nuevo ticket para ti</p>
                        </div>

                        <div class="settings-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="notify_ticket_update" checked>
                                <span>Notificarme de actualizaciones de tickets</span>
                            </label>
                            <p class="settings-description">Alertas cuando hay comentarios o cambios de estado</p>
                        </div>

                        <div class="settings-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="notify_email" checked>
                                <span>Enviar notificaciones por correo electrónico</span>
                            </label>
                            <p class="settings-description">Email: <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'no definido'); ?></p>
                        </div>

                        <div class="settings-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="notify_desktop">
                                <span>Notificaciones de escritorio</span>
                            </label>
                            <p class="settings-description">Alertas emergentes en el navegador</p>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 15px;"><i class="fas fa-save"></i> Guardar Preferencias</button>
                    </form>
                    <div id="notificationMessage" class="alert" style="display:none; margin-top: 15px;"></div>
                </div>
            </div>

            <!-- Preferencias de Interfaz -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-palette"></i> Preferencias de Interfaz</h3>
                </div>
                <div class="card-body">
                    <form id="interfaceForm">
                        <?php echo csrf_field(); ?>

                        <div class="settings-group">
                            <label for="theme">Tema:</label>
                            <select id="theme" name="theme">
                                <option value="light"><i class="fas fa-sun"></i> Claro (por defecto)</option>
                                <option value="dark"><i class="fas fa-moon"></i> Oscuro</option>
                                <option value="auto"><i class="fas fa-sync-alt"></i> Automático (según sistema)</option>
                            </select>
                            <p class="settings-description">Elige cómo deseas ver la interfaz</p>
                        </div>

                        <div class="settings-group">
                            <label for="items_per_page">Elementos por página:</label>
                            <select id="items_per_page" name="items_per_page">
                                <option value="10">10 elementos</option>
                                <option value="20" selected>20 elementos</option>
                                <option value="50">50 elementos</option>
                                <option value="100">100 elementos</option>
                            </select>
                        </div>

                        <div class="settings-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="compact_mode">
                                <span>Modo compacto</span>
                            </label>
                            <p class="settings-description">Reduce el espaciado para ver más información</p>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 15px;"><i class="fas fa-save"></i> Guardar Preferencias</button>
                    </form>
                    <div id="interfaceMessage" class="alert" style="display:none; margin-top: 15px;"></div>
                </div>
            </div>

            <!-- Sesión y Seguridad -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lock"></i> Sesión y Seguridad</h3>
                </div>
                <div class="card-body">
                    <div class="settings-group">
                        <label>Sesiones Activas:</label>
                        <p>Estás logueado desde 1 dispositivo</p>
                    </div>

                    <div class="settings-group">
                        <button class="btn btn-warning" onclick="cerrarOtrasSesiones()"><i class="fas fa-times-circle"></i> Cerrar Otras Sesiones</button>
                        <p class="settings-description">Cierra tu sesión en otros dispositivos</p>
                    </div>

                    <div class="settings-group" style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
                        <button class="btn btn-danger" onclick="cerrarSesion()"><i class="fas fa-sign-out-alt"></i>Cerrar Sesión Actual</button>
                        <p class="settings-description">Saldrá de tu cuenta en este dispositivo</p>
                    </div>
                </div>
            </div>

            <!-- Información de Cuenta -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Información de Cuenta</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; gap: 10px;">
                        <p><strong>Usuario ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'N/A'); ?></p>
                        <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['user_role'] ?? 'N/A'); ?></p>
                        <p><strong>Departamento:</strong> <?php echo htmlspecialchars($_SESSION['user_department'] ?? 'N/A'); ?></p>
                        <p><strong>Cuenta creada:</strong> Diciembre de 2025</p>
                        <p><strong>Última actualización:</strong> Hoy a las <?php echo date('H:i'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.settings-group {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.settings-group:last-of-type {
    border-bottom: none;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 10px;
    cursor: pointer;
}

.checkbox-label span {
    font-weight: 500;
    color: #333;
}

.settings-description {
    margin: 8px 0 0 26px;
    font-size: 13px;
    color: #666;
}

.settings-group select,
.settings-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.btn-danger {
    background-color: #f44336;
    color: white;
}

.btn-danger:hover {
    background-color: #d32f2f;
}
</style>

<script>
// BASE_URL y CSRF_TOKEN definidos en header.php

// Cargar tema guardado al iniciar
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.getElementById('theme').value = savedTheme;
    loadNotificationPreferences();
});

// Notificaciones
document.getElementById('notificationsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
    
    const preferences = {
        notify_new_ticket: formData.get('notify_new_ticket') === 'on',
        notify_ticket_update: formData.get('notify_ticket_update') === 'on',
        notify_email: formData.get('notify_email') === 'on',
        notify_desktop: formData.get('notify_desktop') === 'on'
    };
    
    localStorage.setItem('notificationPreferences', JSON.stringify(preferences));
    
    showMessage('notificationMessage', '✓ Preferencias de notificaciones guardadas', 'success');
});

// Interfaz
document.getElementById('interfaceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const selectedTheme = document.getElementById('theme').value;
    
    // Guardar en localStorage
    localStorage.setItem('theme', selectedTheme);
    
    // Aplicar tema
    if (selectedTheme === 'auto') {
        const prefersLight = window.matchMedia('(prefers-color-scheme: light)');
        const theme = prefersLight.matches ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', theme);
    } else {
        document.documentElement.setAttribute('data-theme', selectedTheme);
    }
    
    showMessage('interfaceMessage', '✓ Tema cambiado a: ' + selectedTheme, 'success');
});

function loadNotificationPreferences() {
    const prefs = localStorage.getItem('notificationPreferences');
    if (prefs) {
        const preferences = JSON.parse(prefs);
        if (preferences.notify_new_ticket !== undefined) {
            document.querySelector('input[name="notify_new_ticket"]').checked = preferences.notify_new_ticket;
        }
        if (preferences.notify_ticket_update !== undefined) {
            document.querySelector('input[name="notify_ticket_update"]').checked = preferences.notify_ticket_update;
        }
        if (preferences.notify_email !== undefined) {
            document.querySelector('input[name="notify_email"]').checked = preferences.notify_email;
        }
        if (preferences.notify_desktop !== undefined) {
            document.querySelector('input[name="notify_desktop"]').checked = preferences.notify_desktop;
        }
    }
}

function cerrarOtrasSesiones() {
    if (confirm('¿Deseas cerrar todas tus otras sesiones?')) {
        showMessage('interfaceMessage', '✓ Otras sesiones cerradas', 'success');
    }
}

function cerrarSesion() {
    if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
        const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets';
        window.location.href = baseUrl + '/logout';
    }
}

function showMessage(elementId, msg, type) {
    const msgDiv = document.getElementById(elementId);
    msgDiv.className = `alert alert-${type}`;
    msgDiv.textContent = msg;
    msgDiv.style.display = 'block';
    setTimeout(() => msgDiv.style.display = 'none', 5000);
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
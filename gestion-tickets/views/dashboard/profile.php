<?php
$pageTitle = $pageTitle ?? 'Mi Perfil - TICKETS';
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
                <h1>Mi Perfil</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="content-area">
            <!-- Información Personal -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información Personal</h3>
                </div>
                <div class="card-body">
                    <div class="profile-section">
                        <div class="profile-item">
                            <label class="profile-label">Nombre Completo:</label>
                            <span class="profile-value"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'No definido'); ?></span>
                        </div>

                        <div class="profile-item">
                            <label class="profile-label">Correo Electrónico:</label>
                            <span class="profile-value"><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'No definido'); ?></span>
                        </div>

                        <div class="profile-item">
                            <label class="profile-label">Rol:</label>
                            <span class="profile-value">
                                <?php 
                                $roles = [
                                    'user' => 'Usuario',
                                    'agent' => 'Agente',
                                    'admin' => 'Administrador'
                                ];
                                $userRole = $_SESSION['user_role'] ?? 'guest';
                                echo htmlspecialchars($roles[$userRole] ?? 'Desconocido');
                                ?>
                            </span>
                        </div>

                        <div class="profile-item">
                            <label class="profile-label">Departamento:</label>
                            <span class="profile-value"><?php echo htmlspecialchars($_SESSION['user_department'] ?? 'No definido'); ?></span>
                        </div>

                        <div class="profile-item">
                            <label class="profile-label">Estado:</label>
                            <span class="profile-value">
                                <span class="badge badge-success">✓ Activo</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cambiar Contraseña -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title">Seguridad</h3>
                </div>
                <div class="card-body">
                    <form id="changePasswordForm" class="password-form">
                        <?php echo csrf_field(); ?>
                        
                        <div class="form-group-password">
                            <div class="password-input-wrapper">
                                <label for="current_password" class="form-label">Contraseña Actual <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <input type="password" id="current_password" name="current_password" required placeholder="Ingresa tu contraseña actual" class="password-input">
                                    <button type="button" class="toggle-password" data-target="current_password" title="Mostrar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="password-input-wrapper">
                                <label for="new_password" class="form-label">Nueva Contraseña <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <input type="password" id="new_password" name="new_password" required placeholder="Mínimo 6 caracteres" class="password-input">
                                    <button type="button" class="toggle-password" data-target="new_password" title="Mostrar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="strengthIndicator"></div>
                            </div>

                            <div class="password-input-wrapper">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña <span class="required">*</span></label>
                                <div class="input-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirma la nueva contraseña" class="password-input">
                                    <button type="button" class="toggle-password" data-target="confirm_password" title="Mostrar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-security"><i class="fas fa-shield-alt"></i> Cambiar Contraseña</button>
                    </form>
                    <div id="passwordMessage" class="alert-message" style="display:none; margin-top: 15px;"></div>
                </div>
            </div>

            <!-- Historial de Acceso (últimos accesos) -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title">Información Adicional</h3>
                </div>
                <div class="card-body">
                    <div class="profile-section">
                        <div class="profile-item">
                            <label class="profile-label">ID de Usuario:</label>
                            <span class="profile-value" style="font-family: monospace;"><?php echo htmlspecialchars($_SESSION['user_id'] ?? 'N/A'); ?></span>
                        </div>

                        <div class="profile-item">
                            <label class="profile-label">Última Actualización:</label>
                            <span class="profile-value">Hoy a las <?php echo date('H:i'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* ==================== PROFILE STYLES ==================== */
.profile-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.profile-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}

.profile-item:last-child {
    border-bottom: none;
}

.profile-label {
    font-weight: 600;
    color: var(--text-primary);
    min-width: 150px;
}

.profile-value {
    color: var(--text-secondary);
    font-size: 14px;
}

.badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

/* ==================== PASSWORD FORM STYLES ==================== */
.password-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-width: 500px;
}

.form-group-password {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.password-input-wrapper {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 14px;
    display: flex;
    gap: 4px;
}

.required {
    color: var(--danger);
}

/* Input Wrapper with Toggle */
.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input {
    width: 100%;
    padding: 12px 40px 12px 16px;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 15px;
    color: var(--text-primary);
    background: var(--bg-primary);
    font-family: inherit;
    transition: all 0.3s ease;
    letter-spacing: 0.05em;
}

.password-input::placeholder {
    color: var(--text-secondary);
    opacity: 0.7;
}

.password-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    background: var(--bg-primary);
}

.password-input:hover {
    border-color: var(--primary-dark);
}

/* Toggle Password Button */
.toggle-password {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-secondary);
    font-size: 18px;
    padding: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toggle-password:hover {
    color: var(--primary);
    transform: scale(1.1);
}

.toggle-password:active {
    transform: scale(0.95);
}

/* Password Strength Indicator */
.password-strength {
    height: 4px;
    background: var(--bg-secondary);
    border-radius: 2px;
    margin-top: 4px;
    overflow: hidden;
}

.password-strength.weak {
    background: linear-gradient(to right, #ef4444 0%, #ef4444 100%);
}

.password-strength.fair {
    background: linear-gradient(to right, #f59e0b 0%, #f59e0b 100%);
}

.password-strength.good {
    background: linear-gradient(to right, #3b82f6 0%, #3b82f6 100%);
}

.password-strength.strong {
    background: linear-gradient(to right, #10b981 0%, #10b981 100%);
}

/* Buttons */
.btn-security {
    background: var(--primary);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    align-self: flex-start;
    margin-top: 10px;
}

.btn-security:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-security:active {
    transform: translateY(0);
}

/* Alert Messages */
.alert-message {
    padding: 14px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    border-left: 4px solid;
    animation: slideInUp 0.3s ease;
}

.alert-message.alert-success {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
    border-left-color: var(--success);
}

.alert-message.alert-error {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
    border-left-color: var(--danger);
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .password-form {
        max-width: 100%;
    }

    .password-input {
        padding: 11px 36px 11px 14px;
        font-size: 14px;
    }

    .form-label {
        font-size: 13px;
    }

    .btn-security {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .password-input {
        padding: 10px 32px 10px 12px;
        font-size: 13px;
    }

    .toggle-password {
        font-size: 16px;
        right: 8px;
    }

    .form-label {
        font-size: 12px;
    }
}
</style>

<script>
// BASE_URL y CSRF_TOKEN definidos en header.php

// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});

// Password strength indicator
document.getElementById('new_password').addEventListener('input', function() {
    const strength = calculatePasswordStrength(this.value);
    const indicator = document.getElementById('strengthIndicator');
    
    indicator.className = 'password-strength ' + strength.level;
    indicator.title = strength.message;
});

function calculatePasswordStrength(password) {
    let strength = 0;
    let feedback = [];
    
    if (password.length >= 8) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
    
    if (strength <= 1) return { level: 'weak', message: '⚠️ Débil' };
    if (strength <= 2) return { level: 'fair', message: '⚡ Regular' };
    if (strength <= 3) return { level: 'good', message: '✓ Buena' };
    return { level: 'strong', message: '★ Muy Fuerte' };
}

// Form submission
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const current = document.getElementById('current_password').value;
    const newPass = document.getElementById('new_password').value;
    const confirm = document.getElementById('confirm_password').value;

    if (newPass !== confirm) {
        showMessage('Las contraseñas no coinciden', 'error');
        return;
    }

    if (newPass.length < 6) {
        showMessage('La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('current_password', current);
    formData.append('new_password', newPass);
    formData.append('confirm_password', confirm);
    formData.append('csrf_token', CSRF_TOKEN);

    fetch(`${BASE_URL}/auth/change-password`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMessage('✓ Contraseña cambiada exitosamente', 'success');
            document.getElementById('changePasswordForm').reset();
            document.getElementById('strengthIndicator').className = 'password-strength';
        } else {
            showMessage('✗ Error: ' + data.error, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showMessage('Error de conexión', 'error');
    });
});

function showMessage(msg, type) {
    const msgDiv = document.getElementById('passwordMessage');
    msgDiv.className = `alert-message alert-${type}`;
    msgDiv.textContent = msg;
    msgDiv.style.display = 'block';
    setTimeout(() => msgDiv.style.display = 'none', 5000);
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>


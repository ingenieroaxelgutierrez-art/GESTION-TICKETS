// Funciones globales del sistema

// ========================================
// FUNCIONES GLOBALES
// ========================================

// Función para obtener el tema actual
function getTheme() {
    return localStorage.getItem('theme') || 'light';
}

// Función para cambiar el tema
function toggleTheme() {
    const currentTheme = getTheme();
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    localStorage.setItem('theme', newTheme);
    document.documentElement.setAttribute('data-theme', newTheme);
    
    // Actualizar icono del botón
    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.innerHTML = newTheme === 'dark' 
            ? '<i class="fas fa-sun"></i>' 
            : '<i class="fas fa-moon"></i>';
    }
}

// Toggle sidebar en móvil y desktop
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (!sidebar) return;
    
    // Detectar si es dispositivo móvil
    const isMobile = window.innerWidth <= 1024;
    
    if (isMobile) {
        // En móvil, usar clase 'active' para deslizar
        sidebar.classList.toggle('active');
    } else {
        // En desktop, usar clase 'hidden' para ocultamiento suave
        sidebar.classList.toggle('hidden');
        if (mainContent) {
            mainContent.classList.toggle('sidebar-hidden');
        }
    }
    
    // Guardar estado en localStorage
    const isHidden = sidebar.classList.contains('hidden');
    localStorage.setItem('sidebarHidden', isHidden);
}

// Restaurar estado del sidebar al cargar la página
function restoreSidebarState() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (!sidebar) return;
    
    const isMobile = window.innerWidth <= 1024;
    const wasHidden = localStorage.getItem('sidebarHidden') === 'true';
    
    if (wasHidden && !isMobile) {
        sidebar.classList.add('hidden');
        if (mainContent) {
            mainContent.classList.add('sidebar-hidden');
        }
    }
}

// Manejar cambio de tamaño de ventana
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (!sidebar) return;
    
    const isMobile = window.innerWidth <= 1024;
    
    if (isMobile) {
        // En móvil, remover clase hidden y usar active
        sidebar.classList.remove('hidden');
        if (mainContent) {
            mainContent.classList.remove('sidebar-hidden');
        }
    }
});

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    restoreSidebarState();
});

// Cerrar modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Abrir modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

// Cerrar modales al hacer clic fuera
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });
});

// Función para mostrar alertas
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.animation = 'slideUp 0.3s ease';
    alertDiv.textContent = message;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            alertDiv.remove();
        }, 300);
    }, 4000);
}

// Función para confirmar acciones
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Prevención de envío doble de formularios
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tema
    initializeThemeToggle();
    
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                setTimeout(() => {
                    submitBtn.disabled = true;
                }, 100);
            }
        });
    });
});

// Inicializar el toggle de tema
function initializeThemeToggle() {
    const currentTheme = getTheme();
    const userInfo = document.querySelector('.user-info');
    
    if (userInfo) {
        // Crear botón de toggle de tema si no existe
        if (!document.getElementById('themeToggleBtn')) {
            const themeBtn = document.createElement('button');
            themeBtn.id = 'themeToggleBtn';
            themeBtn.className = 'theme-toggle-btn';
            themeBtn.title = 'Cambiar tema';
            themeBtn.innerHTML = currentTheme === 'dark' 
                ? '<i class="fas fa-sun"></i>' 
                : '<i class="fas fa-moon"></i>';
            themeBtn.onclick = (e) => {
                e.preventDefault();
                toggleTheme();
            };
            
            // Insertar antes del user-info
            userInfo.parentElement.insertBefore(themeBtn, userInfo);
        }
    }
}

// Función para formatear fechas
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

// Función para copiar al portapapeles
function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    showAlert('Copiado al portapapeles', 'success');
}

// Validación de campos requeridos
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = 'var(--danger)';
            
            field.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '';
                }
            });
        }
    });
    
    if (!isValid) {
        showAlert('Por favor completa todos los campos requeridos', 'error');
    }
    
    return isValid;
}

// Sanitización básica de entrada (cliente - no reemplaza sanitización del servidor)
function sanitizeInput(input) {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}

// Debounce para búsquedas
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Función para cargar datos con AJAX
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error fetching data:', error);
        showAlert('Error al cargar los datos', 'error');
        return null;
    }
}

// Auto-logout por inactividad (30 minutos)
let inactivityTimer;
const INACTIVITY_LIMIT = 30 * 60 * 1000; // 30 minutos en milisegundos

function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(() => {
        alert('Tu sesión ha expirado por inactividad');
            const _base = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/gestion-tickets');
            window.location.href = _base + '/logout';
    }, INACTIVITY_LIMIT);
}

// Eventos de actividad del usuario
['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
    document.addEventListener(event, resetInactivityTimer, true);
});

// Iniciar el timer cuando carga la página
    resetInactivityTimer();

// Protección contra XSS en innerHTML
function setInnerHTML(element, html) {
    if (typeof element === 'string') {
        element = document.getElementById(element);
    }
    if (element) {
        // Crear un elemento temporal para sanitizar
        const temp = document.createElement('div');
        temp.textContent = html;
        element.innerHTML = temp.innerHTML;
    }
}

// Animación de carga
function showLoader() {
    const loader = document.createElement('div');
    loader.id = 'globalLoader';
    loader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 99999;
    `;
    loader.innerHTML = '<div class="loading" style="width: 50px; height: 50px;"></div>';
    document.body.appendChild(loader);
}

function hideLoader() {
    const loader = document.getElementById('globalLoader');
    if (loader) {
        loader.remove();
    }
}

// Manejo de errores global
window.addEventListener('error', function(e) {
    console.error('Error global:', e.error);
});

// Prevenir ataques de clickjacking
if (window.top !== window.self) {
    window.top.location = window.self.location;
}

console.log('Sistema de Tickets - Inicializado correctamente');
-- ============================================================================
-- RIDECO - Sistema de Gestión de Tickets - Base de Datos Completa
-- Versión: 1.0 (Producción)
-- Fecha: 15 de enero de 2026
-- ============================================================================
-- Este archivo contiene TODO el schema necesario para desplegar en producción.
-- Ejecutar una sola vez en una base de datos nueva.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1. TABLAS PRINCIPALES (Schema)
-- ============================================================================

DROP TABLE IF EXISTS ticket_history;
DROP TABLE IF EXISTS ticket_attachments;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS departments;

-- Departamentos (quién emite y quién resuelve)
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('emisor', 'receptor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categorías / tipos de incidencia
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    INDEX idx_dept (department_id)
) ENGINE=InnoDB;

-- Usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department_id INT NOT NULL,
    role ENUM('admin', 'agent', 'user') NOT NULL DEFAULT 'user',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- Tickets
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
    priority ENUM('baja','media','alta','urgente') DEFAULT 'media',
    user_id INT NOT NULL,
    assigned_to INT,
    department_from_id INT NOT NULL,
    department_to_id INT NOT NULL,
    category_id INT,
    closed_reason TEXT,
    closed_by INT,
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (department_from_id) REFERENCES departments(id),
    FOREIGN KEY (department_to_id) REFERENCES departments(id),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_user (user_id),
    INDEX idx_assigned (assigned_to),
    INDEX idx_dept_to (department_to_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Comentarios
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    is_private TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Adjuntos de Tickets y Comentarios
CREATE TABLE ticket_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NULL,
    comment_id INT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id),
    INDEX idx_comment (comment_id)
) ENGINE=InnoDB;

-- Historial de cambios
CREATE TABLE ticket_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_value VARCHAR(255),
    new_value VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================================================
-- 2. TABLA DE NOTIFICACIONES
-- ============================================================================

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('ticket', 'comment', 'assignment', 'status_change', 'system') DEFAULT 'system',
    related_ticket_id INT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (read_at),
    INDEX idx_created (created_at),
    INDEX idx_user_read (user_id, read_at)
) ENGINE=InnoDB;

-- ============================================================================
-- 3. DATOS DE EJEMPLO
-- ============================================================================

-- Departamentos (con IDs específicos)
INSERT INTO departments (id, name, type) VALUES
(1, 'Compras', 'emisor'),
(2, 'Almacén', 'emisor'),
(3, 'Ventas', 'emisor'),
(6, 'Finanzas', 'emisor'),
(7, 'RRHH', 'emisor'),
(8, 'Marketing', 'emisor'),
(9, 'Diseño', 'emisor'),
(10, '´Producción', 'emisor'),
(11, 'Proyectos', 'emisor'),
(12, 'Auditoria', 'emisor'),
(4, 'TI', 'receptor'),
(5, 'Procesos', 'receptor'),
(13, 'Tiendas', 'emisor');

-- Categorías para TI (department_id = 4)
INSERT INTO categories (department_id, name, description) VALUES
(4, 'Falla de impresión', 'Impresora no imprime, atascos, error de driver, etc.'),
(4, 'Conectividad de internet', 'Sin internet, WiFi no conecta, VPN caído, ping alto'),
(4, 'No se escuchan las bocinas', 'Sin audio, controladores de sonido, auriculares tampoco'),
(4, 'Sitios web bloqueados', 'Página no carga por firewall o política de empresa'),
(4, 'Excel trabado o no abre', 'Excel congelado, archivo dañado, mucho tiempo abriendo lento'),
(4, 'Desactivación de Office', 'Licencia vencida, Office pide activación, error 0x800'),
(4, 'Contraseña olvidada', 'Usuario olvidó contraseña de Windows, correo o sistema interno'),
(4, 'Computadora lenta', 'PC tarda en abrir programas, ventilador ruidoso, muchas pestañas'),
(4, 'Fallas en cargador', 'Cargador no carga, puerto dañado, batería no reconoce'),
(4, 'Computadora no enciende', 'No prende, pantalla negra, luz azul, reinicio constante'),
(4, 'Correos de spam / phishing', 'Recibo muchos correos basura o sospechosos');

-- Categorías para Procesos (department_id = 5)
INSERT INTO categories (department_id, name, description) VALUES
(5, 'Error en ERP', 'Módulo no carga, error 500, campo no encontrado'),
(5, 'Solicitud de Políticas', 'Necesito nueva política o revisión de existente'),
(5, 'Solicitud de Proceso', 'Crear o modificar flujo de trabajo'),
(5, 'Solicitud de Formato', 'Necesito formato Excel, PDF o Word nuevo documento'),
(5, 'Solicitud de Modificación en ERP', 'Cambiar campo, regla de acceso, workflow'),
(5, 'Solicitud de Alta de Usuario en ERP', 'Nuevo empleado necesita acceso al ERP'),
(5, 'Actualización del Manual de Procedimientos', 'Agregar, modificar o eliminar procedimiento');

-- Usuarios de ejemplo (actualiza con tu lista real luego del deploy)
INSERT INTO users (name, email, password, department_id, role, active) VALUES
('Demo Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'admin', 1),
('Demo Agent TI', 'agent-ti@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'agent', 1),
('Demo Agent Procesos', 'agent-proc@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 'agent', 1),
('Demo User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'user', 1);

-- ============================================================================
-- 4. FINALIZAR CONFIGURACIÓN
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- FIN DEL SCRIPT DE INSTALACIÓN
-- ============================================================================


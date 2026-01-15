#Sistema de Gestión de Tickets RIDECO

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-blue)](https://www.mysql.com)

**Plataforma integral de gestión de tickets, solicitudes de servicio y atención al cliente.**

---

## Tabla de Contenidos

1. [¿Qué es?](#qué-es)
2. [Características](#características-principales)
3. [Requisitos](#requisitos)
4. [Instalación Local](#instalación-local)
5. [Estructura del Proyecto](#estructura-del-proyecto)
6. [Usuarios por Defecto](#usuarios-por-defecto)
7. [Despliegue en Producción](#despliegue-en-producción)
8. [Subir a GitHub](#subir-a-github)
9. [Troubleshooting](#troubleshooting)

---

## ¿Qué es?

**Sistema de Gestión de Tickets** es una aplicación web construida en PHP con arquitectura MVC que permite:

- **Usuarios** crear tickets (solicitudes de soporte)
- **Agentes** atender y resolver tickets asignados
- **Administradores** gestionar todo el sistema

Permite gestión completa del ciclo de vida de un ticket: creación → asignación → resolución → cierre.

---

## Características Principales

 **Autenticación & Seguridad**
- Login seguro con hash de contraseñas (bcrypt)
- Control de acceso por roles (User, Agent, Admin)
- Protección CSRF en formularios
- Sesiones seguras

 **Gestión de Tickets**
- Estados: Abierto, En Progreso, Resuelto, Cerrado
- Prioridades: Baja, Media, Alta, Urgente
- Categorización por departamento destino
- Historial completo de cambios

 **Funcionalidades Avanzadas**
- Asignación automática a agentes del departamento
- Sistema de comentarios con privacidad
- **Archivos adjuntos en tickets y comentarios**
- Reasignación entre agentes
- Búsqueda y filtrado de tickets

 **Dashboard Personalizado**
- Vista diferente según rol (User, Agent, Admin)
- Estadísticas en tiempo real
- Tickets pendientes por prioridad
- Reportes por estado

 **Sistema de Notificaciones**
- Alertas de nuevos tickets
- Notificaciones de cambios de estado
- Registro completo de actividades

---

## Requisitos

### Servidor Local
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx con mod_rewrite habilitado
- XAMPP (recomendado para desarrollo)

### Servidor de Producción
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Acceso SSH al servidor
- Git instalado (para deploy)

---

## Instalación Local

### 1. Descargar el Proyecto

```bash
# Opción A: Clonar desde GitHub
git clone https://github.com/tuusuario/gestion-tickets.git
cd gestion-tickets

# Opción B: Descargar ZIP
Extraer en c:\xampp\htdocs\gestion-tickets
```

### 2. Crear Base de Datos

```bash
# Usar production.sql (RECOMENDADO - TODO incluido)
mysql -u root -p < sql/production.sql

# O importar via phpMyAdmin:
1. Abre http://localhost/phpmyadmin
2. Crea BD "gestion_tickets"
3. Importa archivo sql/production.sql
```

### 3. Configurar Base de Datos

```bash
# Copiar archivo de ejemplo
cp app/config/database.example.php app/config/database.php

# Editar si es necesario (credenciales locales):
- Host: localhost
- Usuario: root
- Password: (tu contraseña MySQL)
- BD: gestion_tickets
```

### 4. Permisos de Directorios

```bash
# En Linux/Mac:
chmod 755 assets/uploads
chmod 755 storage/logs

# En Windows: (automático)
```

### 5. Acceder a la Aplicación

```
http://localhost/gestion-tickets
```

---

## Estructura del Proyecto

```
gestion-tickets/
│
├── app/                          # Lógica de la aplicación
│   ├── config/
│   │   ├── app.php               # Config general
│   │   ├── database.php          # Credenciales BD (NO commitear)
│   │   ├── database.example.php  # Template
│   │   └── routes.php            # Rutas
│   │
│   ├── controllers/              # Lógica de negocio
│   │   ├── AdminController.php
│   │   ├── ApiController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── TicketController.php
│   │
│   ├── core/                     # Framework
│   │   ├── Auth.php
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   ├── Logger.php
│   │   ├── Model.php
│   │   ├── Router.php
│   │   └── Session.php
│   │
│   ├── helpers/
│   │   ├── csrf.php
│   │   ├── menu.php
│   │   └── url.php
│   │
│   └── models/
│       ├── Category.php
│       ├── Department.php
│       ├── Notification.php
│       ├── Ticket.php
│       └── User.php
│
├── assets/
│   ├── css/                      # Estilos
│   ├── js/                       # JavaScript
│   └── uploads/                  # Archivos adjuntos
│
├── sql/
│   ├── production.sql            # USE THIS (TODO incluido)
│   ├── schema.sql
│   ├── create-notifications-table.sql
│   └── migration-comment-attachments.sql
│
├── storage/logs/                 # Logs
│
├── views/                        # Vistas HTML
│   ├── admin/
│   ├── auth/
│   ├── dashboard/
│   ├── layouts/
│   └── tickets/
│
├── index.php                     # Punto de entrada
├── .gitignore
└── README.md
```

---

## Usuarios por Defecto

Después de importar `sql/production.sql`:

| Nombre        | Email                 | Contraseña  | Rol   | Departamento |
|---------------|-----------------------|-------------|-------|--------------|
| Administrador | admin@rideco.mx       | admin123    | admin | TI           |
| Armando TI    | auxsoporte@rideco.mx  | password123 | agent | TI           |
| Luis TI       | sistemas@rideco.mx    | password123 | agent | TI           |
| Axel Procesos | procesos@rideco.mx    | password123 | agent | Procesos     |
| user 1        | user@rideco.mx        | password123 | user  | Compras      |
| + 55 más      | (varios)              | password123 | user  | (varios)     |

 **IMPORTANTE:** Cambiar contraseñas después del deploy en producción

---

## Despliegue en Producción

### Opción A: HostGator (cPanel)

#### 1. Preparar en Local

```bash
# Crear .gitignore
cat > .gitignore << 'EOF'
app/config/database.php
storage/logs/*
assets/uploads/*
.DS_Store
*.log
EOF

git add .
git commit -m "Ready for production"
```

#### 2. Conectar a HostGator por SSH

```bash
ssh usuario@tudominio.com
cd public_html
```

#### 3. Clonar desde GitHub

```bash
git clone https://github.com/tuusuario/gestion-tickets.git
cd gestion-tickets
```

#### 4. Crear BD en cPanel

```
1. cPanel → MySQL Databases
2. Crear BD: gestion_tickets
3. Crear usuario: gestion_user
4. Asignar: ALL PRIVILEGES
5. Anotar credenciales
```

#### 5. Configurar database.php

```bash
nano app/config/database.php

# Editar:
<?php
$dsn = 'mysql:host=localhost;dbname=gestion_tickets;charset=utf8mb4';
$user = 'gestion_user';
$password = 'tu_password_seguro';
$options = [...];
$pdo = new PDO($dsn, $user, $password, $options);
```

#### 6. Importar Schema

```bash
# Via SSH (si disponible):
mysql -u gestion_user -p gestion_tickets < sql/production.sql

# O via cPanel phpMyAdmin:
Importar sql/production.sql
```

#### 7. Permisos

```bash
chmod 755 assets/uploads
chmod 755 storage/logs
chmod 644 app/config/database.php
```

#### 8. Configurar .htaccess

```bash
cat > .htaccess << 'EOF'
RewriteEngine On
RewriteBase /gestion-tickets/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?path=$1 [QSA,L]
EOF
```

#### 9. Acceder

```
https://tudominio.com/gestion-tickets
```

---

### Opción B: VPS / Servidor Dedicado

#### 1. Conectar SSH

```bash
ssh usuario@ip_servidor
sudo su
```

#### 2. Instalar Dependencias

```bash
# Actualizar
apt-get update && apt-get upgrade -y

# PHP y extensiones
apt-get install php7.4 php7.4-mysql php7.4-mbstring php7.4-curl php7.4-gd -y

# MySQL
apt-get install mysql-server -y

# Git y Apache
apt-get install git apache2 libapache2-mod-php7.4 -y
a2enmod rewrite
```

#### 3. Descargar Proyecto

```bash
cd /var/www
git clone https://github.com/tuusuario/gestion-tickets.git
cd gestion-tickets
chown -R www-data:www-data .
```

#### 4. Crear BD

```bash
mysql -u root -p

# En MySQL:
CREATE DATABASE gestion_tickets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gestion_user'@'localhost' IDENTIFIED BY 'password_seguro';
GRANT ALL PRIVILEGES ON gestion_tickets.* TO 'gestion_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 5. Importar

```bash
mysql -u gestion_user -p gestion_tickets < sql/production.sql
```

#### 6. Configurar

```bash
nano app/config/database.php
# Añadir credenciales
```

#### 7. Configurar Apache

```bash
nano /etc/apache2/sites-available/gestion-tickets.conf
```

Contenido:
```apache
<VirtualHost *:80>
    ServerName tudominio.com
    DocumentRoot /var/www/gestion-tickets
    
    <Directory /var/www/gestion-tickets>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

```bash
a2ensite gestion-tickets.conf
systemctl restart apache2
```

#### 8. SSL (HTTPS)

```bash
apt-get install certbot python3-certbot-apache -y
certbot --apache -d tudominio.com
```

---

## Subir a GitHub

### 1. Crear Repositorio

```
1. https://github.com/new
2. Nombre: gestion-tickets
3. Privado o Público
4. Crear
```

### 2. Inicializar Localmente

```bash
cd c:\xampp\htdocs\gestion-tickets

git init

# Configurar usuario (primera vez):
git config --global user.name "Tu Nombre"
git config --global user.email "tu@email.com"
```

### 3. Crear .gitignore

```bash
cat > .gitignore << 'EOF'
# Sensibles
app/config/database.php

# Generados
storage/logs/*
*.log
assets/uploads/*
!assets/uploads/.gitkeep

# Otros
.DS_Store
node_modules/
vendor/
.vscode/
.idea/
EOF
```

### 4. Hacer Commit

```bash
git add .
git status  # Verificar
git commit -m "Initial commit: Sistema de Gestión de Tickets"
```

### 5. Conectar con GitHub

```bash
# Reemplazar USER y REPO
git remote add origin https://github.com/USER/gestion-tickets.git
git branch -M main
git push -u origin main
```

### 6. Comandos Útiles

```bash
# Nuevos cambios
git add .
git commit -m "Descripción del cambio"
git push origin main

# Descargar cambios
git pull origin main

# Crear rama
git checkout -b nueva-feature
git push origin nueva-feature

# Ver historial
git log --oneline
```

---

## Troubleshooting

### Error: "Call to undefined method Auth::requireAuth()"
```
Solución: Usar Auth::requireRole(['admin', 'agent', 'user'])
Archivo: app/controllers/ApiController.php línea 15
```

### Error: "SQLSTATE[HY000]: General error: 1030"
```
Solución: Verificar permisos
chmod 755 assets/uploads
chmod 755 storage/logs
```

### No se cargan archivos adjuntos
```
Solución:
1. Verificar directorio assets/uploads existe
2. chmod 755 assets/uploads
3. En php.ini: upload_max_filesize = 50M
```

### Base de datos no existe
```
Solución:
mysql -u root -p < sql/production.sql
```

### Sesión no persiste
```
Solución:
1. Limpiar caché navegador (Ctrl+Shift+Del)
2. Verificar storage/logs/ está escribible
3. Verificar session.save_path en php.ini
```

---

## Seguridad en Producción

 **Cambiar contraseñas por defecto**

```php
// Generar hash bcrypt:
$password = password_hash('nueva_password_segura', PASSWORD_BCRYPT);

// Ejecutar en MySQL:
UPDATE users SET password = '$2y$10$...' WHERE id = 1;
```

 **Habilitar HTTPS obligatorio** en .htaccess
 <IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /gestion-tickets/

    # Forzar HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php [L]
</IfModule>

Options -Indexes

 **Hacer backups regulares:**

```bash
mysqldump -u user -p gestion_tickets > backup_$(date +%Y%m%d).sql
```

 **Monitorear logs:**

```bash
tail -f storage/logs/*.log
```

---

## API Endpoints

```
GET    /api/tickets                    # Listar tickets
GET    /api/tickets/{id}               # Obtener detalle
POST   /ajax/tickets/create            # Crear ticket
PUT    /tickets/{id}/status            # Cambiar estado
PUT    /tickets/{id}/priority          # Cambiar prioridad
GET    /api/receptores                 # Departamentos
GET    /api/categorias/{dept_id}       # Categorías
GET    /api/agents-by-department/{id}  # Agentes
```

---

## Licencia

MIT License - Ver LICENSE para detalles

---

## Soporte

- **GitHub Issues:** Reportar bugs
- **Email:** ingenieroaxelgutierrez@gmail.com
- **Documentación:** Ver CAMBIOS_TECNICOS.md

---

**Versión:** 1.0.0  
**Última actualización:** 15 de enero de 2026  
**Desarrollado por:** Axel Gutiérrez

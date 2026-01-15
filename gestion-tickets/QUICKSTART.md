#  Guía de Inicio Rápido - RIDECO Ticket System

**Bienvenido al Sistema de Gestión de Tickets**

---

##  5 Pasos para Empezar

### 1️.- Clonar o Descargar

```bash
# Si tienes Git
git clone https://github.com/tuusuario/gestion-tickets.git

# O descargar ZIP y extraer en:
# c:\xampp\htdocs\gestion-tickets
```

### 2️.- Crear Base de Datos

```bash
# Opción A: Con MySQL CLI
mysql -u root -p < sql/production.sql

# Opción B: phpMyAdmin
# 1. Abre http://localhost/phpmyadmin
# 2. Crea BD "gestion_tickets"  
# 3. Importa archivo sql/production.sql
```

### 3️.- Configurar Conexión

```bash
# El archivo ya existe con configuración local
# Si necesitas cambiar credenciales:
cp app/config/database.example.php app/config/database.php

# Editar con tus datos de MySQL
```

### 4️.- Verificar Permisos

```bash
# Linux/Mac:
chmod 755 assets/uploads
chmod 755 storage/logs

# Windows: automático
```

### 5️.- ¡Acceder!

```
http://localhost/gestion-tickets
```

**Usuario (demo):** admin@example.com  
**Contraseña (demo):** password

---

## 👥 Usuarios de Prueba

| Tipo                  | Email                 | Contraseña    |  
|-----------------------|-----------------------|---------------|
| **Admin (demo)**      | admin@example.com       | password      |
| **Agente TI (demo)**  | agent-ti@example.com    | password      |
| **Agente Procesos**   | agent-proc@example.com  | password      |
| **Usuario Normal**    | user@example.com        | password      |

 Cambiar contraseñas después de instalar

---

##  Estructura Básica

```
app/
  ├── controllers/    ← Lógica de tickets, usuarios, etc.
  ├── models/         ← Base de datos
  └── config/         ← database.php (credenciales)

views/
  ├── tickets/        ← Listar, crear, detalle
  └── dashboard/      ← Paneles por rol

sql/
  └── production.sql  ← TODO (base de datos completa)

assets/
  ├── css/            ← Estilos
  ├── js/             ← JavaScript
  └── uploads/        ← Archivos adjuntos
```

---

##  Qué Puedes Hacer

### Si eres Usuario (role: user)
```
✓ Crear nuevos tickets
✓ Ver tus tickets
✓ Comentar en tus tickets
✓ Descargar archivos
```
 Ir a: `/gestion-tickets/tickets/create`

### Si eres Agente (role: agent)
```
✓ Ver todos los tickets de tu depto
✓ Cambiar estado (open → in_progress → resolved → closed)
✓ Cambiar prioridad
✓ Asignar a otros agentes
✓ Agregar comentarios
✓ Subir archivos adjuntos
```
 Ir a: `/gestion-tickets/tickets`

### Si eres Admin (role: admin)
```
✓ Ver TODOS los tickets
✓ Gestionar usuarios
✓ Gestionar departamentos
✓ Gestionar categorías
✓ Ver reportes completos
```
 Ir a: `/gestion-tickets/admin`

---

##  Troubleshooting Rápido

###  "Base de datos no existe"
```bash
mysql -u root -p < sql/production.sql
```

###  "No puedo subir archivos"
```bash
chmod 755 assets/uploads
```

###  "Error de conexión"
```bash
# Verificar app/config/database.php
# Debe coincidir con tus credenciales MySQL
```

###  "Sesión se cae"
```bash
# Limpiar caché navegador: Ctrl+Shift+Del
# Y caché de servidor: storage/logs/
```

---

##  Documentación Completa

Para más detalles, ver:

- **README.md** - Guía completa, instalación, deployment
- **DOCUMENTACION_TECNICA.md** - Arquitectura, controllers, API
- **CAMBIOS_TECNICOS.md** - Historial de cambios realizados

---

##  Deploy en Producción

Cuando estés listo para producción:

### Con HostGator:
1. Abre SSH a tu servidor
2. `git clone https://github.com/usuario/gestion-tickets.git`
3. Crea BD en cPanel
4. `mysql -u user -p bd < sql/production.sql`
5. Edita `app/config/database.php`
6. Habilita HTTPS (certbot)
7. ¡Listo!

Ver **README.md** → "Despliegue en Producción" para pasos completos

---

##  Tips Útiles

### Cambiar Contraseña

```php
// Generar hash bcrypt:
$password = password_hash('nueva_contraseña', PASSWORD_BCRYPT);

// Luego ejecutar en MySQL:
UPDATE users SET password = '$2y$10$...' WHERE email = 'usuario@example.com';
```

### Ver Logs

```bash
tail -f storage/logs/app.log
```

### Probar API

```bash
curl http://localhost/gestion-tickets/api/receptores
```

### Resetear BD (si necesitas empezar de cero)

```bash
mysql -u root -p

DROP DATABASE gestion_tickets;
CREATE DATABASE gestion_tickets;
\. sql/production.sql
EXIT;
```

---

##  Obtener Ayuda

1. **Revisar documentación** - README.md, DOCUMENTACION_TECNICA.md
2. **Revisar logs** - storage/logs/app.log
3. **Limpiar caché** - Ctrl+F5 en navegador
4. **GitHub Issues** - Reportar bugs

---

##  ¡Ya estás listo!

El sistema está 100% funcional. Ahora:

1. **Prueba como User:** Crea un ticket en `/tickets/create`
2. **Prueba como Agent:** Atiéndelo en `/tickets`
3. **Prueba como Admin:** Gestiona en `/admin`

**¿Preguntas?** Abre un issue o revisa la documentación completa.

---

**Happy Ticketing!**

---

*Versión: 1.0 | Última actualización: 15 de enero de 2026*

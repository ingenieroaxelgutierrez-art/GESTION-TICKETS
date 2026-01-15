ÍNDICE DE DOCUMENTACIÓN DEL PROYECTO
=======================================

Este proyecto incluye toda la documentación necesaria para entender, usar y modificar el sistema.

## ARCHIVOS PRINCIPALES DE DOCUMENTACIÓN

### 1. README.md COMIENZA AQUÍ
   Descripción: Resumen ejecutivo del proyecto
   Contenido:
   - Visión general del proyecto
   - Estado final (TODO FUNCIONAL)
   - Cómo iniciar el servidor
   - Endpoints principales
   - Componentes verificados
   - Características funcionales
   
   Lee esto primero para entender qué se ha hecho

### 2. RESUMEN_CORRECCIONES.md
   Descripción: Guía completa de todas las correcciones
   Contenido:
   - Objetivo completado
   - Correcciones implementadas (10 categorías)
   - Estado actual detallado
   - Cómo usar el sistema
   - Cambios técnicos resumidos
   - Estado final con checkboxes
   
   Lee esto para ver qué fue arreglado
# Documentación técnica — Gestión de Tickets

Esta documentación describe la arquitectura, componentes, guías de uso y procedimientos operativos del proyecto. Está pensada para desarrolladores, operadores y revisores técnicos.

Contenido principal
- Introducción y alcance
- Arquitectura y mapa de componentes
- Base de datos (resumen del schema)
- Rutas y API (endpoints más relevantes)
- Guía de desarrollo (añadir rutas, controladores, modelos)
- Scripts y testing
- Despliegue y checklist de producción
- Solución de problemas y logs

1) Arquitectura general
-----------------------
El sistema sigue un patrón MVC con un enrutador propio (`app/core/Router.php`) y clases base en `app/core/`:

- `Controller.php` — helpers comunes para controladores (view, redirect, json, onlyJson)
- `Model.php` — base para acceso a BD y helpers de consulta
- `Database.php` — singleton PDO para conexión a MySQL/MariaDB
- `Auth.php` — gestión de sesión, `attempt`, `check`, `requireRole`

Carpeta `app/`:
- `config/` — `database.php`, `routes.php`, `app.php`
- `controllers/` — controladores que procesan peticiones
- `models/` — acceso a datos y consultas específicas
- `helpers/` — funciones auxiliares (eg. CSRF)

2) Base de datos (resumen)
--------------------------
El esquema principal incluye tablas para:
- `users` — usuarios y roles (admin, agent, user)
- `departments` — departamentos de la organización
- `categories` — categorías por departamento
- `tickets` — registro principal de tickets
- `comments` — comentarios asociados a tickets
- `ticket_history` — historial de cambios
- `ticket_attachments` — archivos adjuntos (si está habilitado)

El fichero `sql/schema.sql` contiene DDL y datos iniciales usados en desarrollo.

3) Rutas y Endpoints
---------------------
Las rutas se encuentran en `app/config/routes.php`. El proyecto cuenta con ~26 rutas registradas. Endpoints clave:

- `GET  /` → Dashboard (si no autenticado redirige a `/login`)
- `GET  /login` → Mostrar formulario de login
- `POST /login` → Procesar login (AJAX)
- `GET  /logout` → Cerrar sesión
- `GET  /tickets` → Listar tickets (según rol)
- `POST /tickets/create` → Crear ticket
- `GET  /tickets/{id}` → Ver ticket
- `POST /tickets/{id}/comment` → Añadir comentario
- `/admin/*` → Rutas de administración (usuarios, departamentos, categorías)

4) CSRF y seguridad
-------------------
- Todos los formularios usan `csrf_token()` y `csrf_field()` desde `app/helpers/csrf.php`.
- Peticiones AJAX añaden cabecera `X-Requested-With: XMLHttpRequest` y token `X-CSRF-Token`.
- Contraseñas se almacenan con `password_hash()` (bcrypt).
- Control de acceso por rol implementado en `Auth::requireRole()` y llamadas desde controladores.

5) Cómo desarrollar / añadir funcionalidad
-----------------------------------------
- Añadir ruta: `app/config/routes.php` → `$router->add('GET','/mi-ruta','MiController@metodo');`
- Nuevo controlador: `app/controllers/MiController.php` — extender `Controller`
- Nueva vista: `views/mi_vista.php` y usar `$this->view('mi_vista', $data)`
- Nuevo modelo: `app/models/MiModelo.php` — extender `Model`

6) Tests y herramientas de verificación
--------------------------------------
- `tools/test-system.php` — test end-to-end (BD, models, controllers)
- `tools/test-routes-simple.php` — lista y verifica rutas
- `tools/test-login-flow.php` — prueba login y respuestas JSON
- `tools/reset-passwords-all.php` — script para reset masivo de contraseñas
- `tools/setup-db.php` — script de inicialización de BD

Comandos típicos (desde la raíz del proyecto):

```powershell
C:\xampp\php\php.exe tools\test-system.php
C:\xampp\php\php.exe tools\test-login-flow.php
C:\xampp\php\php.exe tools\reset-passwords-all.php --unique
```

7) Despliegue y checklist para producción
-----------------------------------------
- Configurar `app/config/database.php` con credenciales seguras
- Forzar HTTPS y certificados válidos
- Ajustar `RewriteBase` en `.htaccess` si la app está en subcarpeta
- Revisar permisos de ficheros y directorios
- Configurar rotación de logs y backups regulares
- Revisar y desactivar cualquier script de debugging en `tools/` si expone información sensible

8) Resolución de problemas comunes
----------------------------------
- Assets 404: asegurar que `RewriteBase` y rutas a `/gestion-tickets/assets/...` son correctas en `views/layouts/*`
- JSON inválido en AJAX: buscar BOM en archivos PHP o salida accidental antes de `json()` (usar `ob_get_clean()` si es necesario)
- CSRF token inválido: comprobar que la sesión está iniciada y que el token se inyecta en los formularios
- Headers ya enviados al redireccionar: evitar `requireRole()` que ejecuta `redirect()` en constructores; hacerlo en métodos de acción

9) Apéndices
------------
- `CAMBIOS_TECNICOS.md` contiene diff por archivo y explicación técnica de las modificaciones realizadas durante la puesta a punto.
- `RESUMEN_CORRECCIONES.md` es la hoja de entrega con los pasos realizados, decisiones y recomendaciones.

Contacto y soporte
------------------
Para soporte, enviar un ZIP con:
- Captura del error
- Output de `tools/test-system.php`
- Versión de PHP (`php -v`) y Apache

Fin de la documentación técnica.
│   │

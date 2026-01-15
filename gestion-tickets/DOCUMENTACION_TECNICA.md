#  Documentación Técnica - Cambios y Funcionalidades

**Última actualización:** 15 de enero de 2026

---

##  Índice

1. [Cambios Implementados](#cambios-implementados)
2. [Arquitectura del Proyecto](#arquitectura-del-proyecto)
3. [Funcionalidades Principales](#funcionalidades-principales)
4. [Sistema de Autenticación](#sistema-de-autenticación)
5. [Gestión de Tickets](#gestión-de-tickets)
6. [Archivos Adjuntos](#archivos-adjuntos)
7. [Sistema de Notificaciones](#sistema-de-notificaciones)
8. [Controladores Disponibles](#controladores-disponibles)
9. [Flujos de Negocio](#flujos-de-negocio)

---

## Cambios Implementados

###  Fase 1: Autenticación y Rutas

**Problema:** Ruta `/auth/change-password` no existía
**Solución:** Agregada en `app/config/routes.php`

```php
$router->add('POST', '/auth/change-password', 'AuthController@changePassword');
```

**Archivo:** `app/controllers/AuthController.php`
- Método: `changePassword()`
- Valida token CSRF
- Verifica contraseña actual
- Hash nueva contraseña con bcrypt
- Retorna JSON

---

###  Fase 2: Dropdown de Agentes

**Problema:** ApiController no inicializaba `$this->db`
**Error:** "Call to member function prepare() on null"

**Solución:** Agregar `parent::__construct()` en constructor

```php
// ANTES (línea 11)
public function __construct() {
    // NADA
}

// DESPUÉS
public function __construct() {
    parent::__construct(); // Ahora $this->db se inicializa
}
```

**Efecto:** Dropdown en "REASIGNAR A AGENTE" ahora carga usuarios correctamente

---

###  Fase 3: Agentes Compartidos

**Requisito:** Ambos TI (Armando y Luis) ven TODOS los tickets TI

**Implementación:**
```php
// Ticket.php - método getByReceiverDepartment()
public function getByReceiverDepartment($dept_id) {
    return $this->db->prepare("
        SELECT * FROM tickets 
        WHERE department_to_id = ? 
        ORDER BY created_at DESC
    ")->execute([$dept_id])->fetchAll();
}
```

**Resultado:** Los agentes del departamento TI ven todos los tickets asignados a TI, permitiendo reasignación entre ellos

---

###  Fase 4: Sistema de Archivos Adjuntos

**Problema:** No había código para procesar `$_FILES['attachments']` al crear tickets

**Archivos Modificados:**

#### 1. `sql/schema.sql` - Agregada columna `comment_id`
```sql
ALTER TABLE ticket_attachments 
ADD COLUMN comment_id INT NULL,
ADD FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE;
```

#### 2. `app/models/Ticket.php` - Métodos adjuntos

**`addAttachment($ticket_id, $file, $comment_id = null)`**
- Valida tipo de archivo (jpg, png, pdf, docx, xlsx, etc.)
- Verifica tamaño máximo (10MB)
- Genera nombre único: `attach_{uniqid()}.{ext}`
- Guarda en `assets/uploads/`
- Inserta registro en DB
- Retorna nombre del archivo o false

**`getAttachments($ticket_id)`**
```sql
SELECT * FROM ticket_attachments 
WHERE ticket_id = ? AND (comment_id IS NULL OR comment_id = 0)
ORDER BY id ASC
```

**`getCommentAttachments($comment_id)`**
```sql
SELECT * FROM ticket_attachments 
WHERE comment_id = ? 
ORDER BY id ASC
```

#### 3. `app/controllers/TicketController.php` - Método `ajaxStore()`

**Reemplazo del TODO comment (línea 314):**

```php
if ($ticketId) {
    // Procesar archivos adjuntos si los hay
    if (!empty($_FILES['attachments'])) {
        $files = $_FILES['attachments'];
        
        // Manejo de múltiples archivos
        $isMultiple = is_array($files['tmp_name']);
        
        if ($isMultiple) {
            for ($i = 0; $i < count($files['tmp_name']); $i++) {
                if (!empty($files['tmp_name'][$i])) {
                    $file = [
                        'name' => $files['name'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'size' => $files['size'][$i],
                        'type' => $files['type'][$i],
                        'error' => $files['error'][$i]
                    ];
                    
                    $this->ticketModel->addAttachment($ticketId, $file);
                }
            }
        } else {
            // Un solo archivo
            if (!empty($files['tmp_name']) && $files['error'] === UPLOAD_ERR_OK) {
                $this->ticketModel->addAttachment($ticketId, $files);
            }
        }
    }
    
    return $this->json(['success' => true, 'ticket_id' => $ticketId]);
}
```

#### 4. `views/tickets/lista.php` - Renderizado

Líneas 471-498 muestran los archivos si existen:
```html
${attachments && attachments.length > 0 ? `
    <div class="attachments-section">
        <h4>📎 Archivos Adjuntos</h4>
        ${attachments.map(att => `
            <a href="/gestion-tickets/assets/uploads/${att.filename}" 
               download="${att.original_name}">
                 ${att.original_name}
            </a>
        `).join('')}
    </div>
` : ''}
```

---

### Fase 5: Corrección de Auth

**Problema:** `Auth::requireAuth()` no existe
**Error en:** `app/controllers/ApiController.php` línea 15

**Solución:** Cambiar a método correcto
```php
// ANTES
Auth::check() or Auth::requireAuth();

// DESPUÉS
Auth::requireRole(['admin', 'agent', 'user']);
```

**Métodos disponibles en Auth.php:**
- `Auth::check()` - Verifica si hay sesión
- `Auth::user()` - Obtiene usuario actual
- `Auth::role()` - Obtiene rol del usuario
- `Auth::requireRole($roles)` - Middleware de protección
- `Auth::attempt($email, $password)` - Login
- `Auth::logout()` - Logout

---

## Arquitectura del Proyecto

### MVC Custom

```
MODEL ←→ DATABASE
  ↓
CONTROLLER ←→ REQUEST
  ↓
VIEW ← (HTML/JSON)
```

### Router Pattern

```php
// En app/config/routes.php:
$router->add('METHOD', 'PATH', 'ControllerName@method');

// Ejemplos:
$router->add('GET', '/tickets', 'TicketController@index');
$router->add('POST', '/tickets/create', 'TicketController@store');
$router->add('PUT', '/tickets/:id/status', 'TicketController@changeStatus');
```

### Autoload

```php
// index.php utiliza spl_autoload_register()
spl_autoload_register(function($class) {
    $path = __DIR__ . '/app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});
```

---

## Funcionalidades Principales

### Autenticación

**Flujo de Login:**
```
1. Usuario completa email + password
2. POST /login (AJAX)
3. AuthController::login() verifica credenciales
4. password_verify() comprueba hash
5. Session se regenera por seguridad
6. Usuario redirige a dashboard
```

**Métodos en AuthController:**
- `loginForm()` - Mostrar login
- `login()` - Procesar login
- `logout()` - Cerrar sesión
- `changePassword()` - Cambiar contraseña

### Creación de Tickets

**Flujo:**
```
1. Usuario llena formulario en /tickets/create
2. FormData incluye files en 'attachments[]'
3. POST /ajax/tickets/create (CSRF token requerido)
4. TicketController::ajaxStore() valida datos
5. Ticket::create() inserta en BD
6. Si hay archivos: Ticket::addAttachment() por cada uno
7. Respuesta JSON con ticket_id
```

**Validaciones:**
- Título y descripción obligatorios
- Departamento destino válido (isReceptor)
- Min 20 caracteres en descripción
- Archivos: jpg, jpeg, png, pdf, doc, docx, xls, xlsx (máx 10MB)

### Asignación de Tickets

**Automática:**
```php
// Al crear ticket, elegir random de agentes disponibles:
$agents = $this->db->prepare("
    SELECT id FROM users 
    WHERE department_id = ? AND role = 'agent' AND active = 1
")->execute([$dept_id])->fetchAll();

$assigned_to = $agents[array_rand($agents)]['id'];
```

**Manual:**
```
1. Agente ve botón "REASIGNAR A AGENTE"
2. Dropdown carga via API: /api/agents-by-department/{dept_id}
3. Selecciona otro agente
4. PUT /tickets/{id}/assign
5. Historial se actualiza
```

### Cambio de Estado

**Estados válidos:** open, in_progress, resolved, closed

**Regla especial para cierre:**
- Requiere motivo de cierre (campo obligatorio)
- Inserta en `closed_reason` y `closed_by`

```php
// En TicketController::changeStatus()
if ($status === 'closed' && empty(trim($reason))) {
    return $this->json(['error' => 'El motivo de cierre es obligatorio']);
}

if ($status === 'closed') {
    $success = $this->ticketModel->close($id, $reason, $_SESSION['user_id']);
} else {
    $success = $this->ticketModel->updateStatus($id, $status);
}

// Registrar en historial
$this->ticketModel->logHistory($id, $_SESSION['user_id'], 
    'status_changed', $oldStatus, $status);
```

---

## Sistema de Autenticación

### Tabla Users

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,           -- bcrypt hash
    department_id INT NOT NULL,
    role ENUM('admin', 'agent', 'user') DEFAULT 'user',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ...
)
```

### Roles y Permisos

| Acción                | User | Agent | Admin |
|-----------------------|------|-------|-------|
| Ver propios tickets   | OK   | -     | OK    |
| Ver tickets depto     | -    | OK    | OK    |
| Cambiar estado        | -    | OK    | OK    |
| Cambiar prioridad     | -    | OK    | OK    |
| Asignar tickets       | -    | OK    | OK    |
| Agregar comentarios   | OK   | OK    | OK    |
| Descargar adjuntos    | OK   | OK    | OK    |
| Gestionar usuarios    | -    | -     | OK    |
| Ver reportes          | -    | OK    | OK    |

### CSRF Protection

En cada formulario:
```html
<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
```

En controllers:
```php
if (!validate_csrf($_POST['csrf_token'] ?? '')) {
    $this->json(['error' => 'Token inválido'], 403);
}
```

---

## Gestión de Tickets

### Tabla Tickets

```sql
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    description TEXT,
    status ENUM('open','in_progress','resolved','closed'),
    priority ENUM('baja','media','alta','urgente'),
    user_id INT,              -- Creador
    assigned_to INT,          -- Agente asignado
    department_from_id INT,   -- Depto que lo crea
    department_to_id INT,     -- Depto que lo atiende
    category_id INT,
    closed_reason TEXT,
    closed_by INT,
    closed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    ...
)
```

### Métodos del Modelo Ticket

**Lectura:**
- `findWithDetails($id)` - Obtiene ticket con JOINs
- `getByUser($user_id)` - Tickets que crea un usuario
- `getByReceiverDepartment($dept_id)` - Todos del depto
- `getComments($ticket_id)` - Comentarios del ticket
- `getAttachments($ticket_id)` - Archivos iniciales
- `getCommentAttachments($comment_id)` - Archivos de comentario

**Escritura:**
- `create($data)` - Crear nuevo ticket
- `updateStatus($id, $status)` - Cambiar estado
- `updatePriority($id, $priority)` - Cambiar prioridad
- `assign($id, $user_id)` - Asignar a agente
- `close($id, $reason, $user_id)` - Cerrar con motivo
- `addComment($ticket_id, $user_id, $content, $is_private)` - Comentar
- `addAttachment($ticket_id, $file, $comment_id)` - Adjuntar archivo
- `logHistory($ticket_id, $user_id, $action, $old, $new)` - Registrar cambio

---

## Archivos Adjuntos

### Estructura Tabla

```sql
CREATE TABLE ticket_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NULL,        -- Archivo del ticket
    comment_id INT NULL,       -- O del comentario
    filename VARCHAR(255),     -- attach_xyz.pdf
    original_name VARCHAR(255),-- Nombre original
    created_at TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE
)
```

### Validaciones

```php
// En Ticket::addAttachment()

// 1. Tipos permitidos
$allowed = ['jpg','jpeg','png','gif','pdf','docx','doc','xlsx','xls','txt','zip'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    return false; // Rechazar tipo
}

// 2. Tamaño máximo (10MB)
if ($file['size'] > 10*1024*1024) {
    return false; // Rechazar tamaño
}

// 3. Mover a directorio seguro
$filename = uniqid('attach_') . '.' . $ext;
$uploadDir = __DIR__ . '/../../assets/uploads/';
mkdir($uploadDir, 0755, true); // Crear si no existe

if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    // Guardar en BD
    $stmt = $this->db->prepare(
        "INSERT INTO ticket_attachments (ticket_id, comment_id, filename, original_name) 
         VALUES (?, ?, ?, ?)"
    );
    return $stmt->execute([$ticket_id, $comment_id, $filename, $file['name']]);
}
```

### Seguridad

 Nombres únicos: evita sobreescrituras
 Directorio fuera del web: assets/uploads/ no ejecuta PHP
 Extensiones whitelist: solo tipos seguros
 Tamaño límite: evita llenar disco
 BD tracking: saber quién subió qué y cuándo

---

## Sistema de Notificaciones

### Tabla Notifications

```sql
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(200),
    message TEXT,
    type ENUM('ticket','comment','assignment','status_change','system'),
    related_ticket_id INT,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP
)
```

### Tipos de Notificación

| Tipo          | Cuándo                | Quién                 |
|---------------|-----------------------|-----------------------|
| ticket        | Nuevo ticket en depto | Agentes               |
| comment       | Comentario en ticket  | Usuario + asignados   |
| assignment    | Ticket reasignado     | Nuevo agente          |
| status_change | Cambio de estado      | Usuario + todos       |
| system        | Admin aviso           | Usuarios              |

---

## Controladores Disponibles

### 1. AuthController

```php
class AuthController extends Controller {
    public function loginForm()      // GET /login
    public function login()          // POST /login (AJAX)
    public function logout()         // GET /logout
    public function changePassword() // POST /auth/change-password
}
```

### 2. TicketController

```php
class TicketController extends Controller {
    public function index()          // GET /tickets
    public function show($id)        // GET /tickets/{id}
    public function createForm()     // GET /tickets/create
    public function store()          // POST /tickets/create
    public function ajaxStore()      // POST /ajax/tickets/create
    public function changeStatus($id)// PUT /tickets/{id}/status
    public function changePriority($id) // PUT /tickets/{id}/priority
    public function assign($id)      // PUT /tickets/{id}/assign
    public function addComment($id)  // POST /tickets/{id}/comment
}
```

### 3. ApiController

```php
class ApiController extends Controller {
    public function receptores()              // GET /api/receptores
    public function categoriasPorDepartamento($dept_id) // GET /api/categorias/{id}
    public function agentsByDepartment($dept_id)  // GET /api/agents-by-department/{id}
    public function tickets()                 // GET /api/tickets
    public function updateTicket($id)        // PUT /api/tickets/{id}
    public function exportTickets()          // GET /api/tickets/export (CSV)
}
```

### 4. AdminController

```php
class AdminController extends Controller {
    public function users()          // GET /admin/users
    public function departments()    // GET /admin/departments
    public function categories()     // GET /admin/categories
    // ... métodos de gestión
}
```

### 5. DashboardController

```php
class DashboardController extends Controller {
    public function index()          // GET /dashboard
    public function profile()        // GET /profile
    public function settings()       // GET /settings
}
```

---

## Flujos de Negocio

### Flujo 1: Crear Ticket

```
USUARIO
  ↓
  Rellena formulario en /tickets/create
  - Título (requerido)
  - Descripción (min 20 chars)
  - Depto destino (lista de receptores)
  - Categoría (cargada via AJAX según depto)
  - Prioridad (radio buttons)
  - Archivos (optional, drag & drop)
  ↓
  Click "Enviar Ticket"
  ↓
  JavaScript: POST /ajax/tickets/create (FormData con FILES)
  ↓
  TicketController::ajaxStore()
    - Valida CSRF
    - Valida datos (title, description, department)
    - Ticket::create() inserta ticket
    - Si hay archivos: Ticket::addAttachment() por cada uno
    - Retorna JSON { success: true, ticket_id: 123 }
  ↓
  Modal: "¡Ticket creado exitosamente!" (ID: #123)
  ↓
  Redirige a /tickets (panel de usuario)
```

### Flujo 2: Atender Ticket (Agente)

```
AGENTE
  ↓
  Abre /tickets (lista de depto)
  ↓
  Click en ticket → Abre modal con detalles
  ↓
  Puede:
    1. Cambiar ESTADO (open → in_progress → resolved → closed)
    2. Cambiar PRIORIDAD (baja/media/alta/urgente)
    3. REASIGNAR a otro agente del depto
    4. Agregar COMENTARIOS
    5. Ver/DESCARGAR adjuntos
  ↓
  Si Resuelto → Agente lo marca "In Progress"
  ↓
  Si Listo → Agente marca "Resolved"
  ↓
  Si Cerrable → Marca "Closed" + MOTIVO DE CIERRE (obligatorio)
  ↓
  Sistema registra en ticket_history cada cambio
  ↓
  Usuario recibe NOTIFICACIÓN de cambio de estado
```

### Flujo 3: Vista Usuario

```
USUARIO
  ↓
  Abre /tickets (sus tickets creados)
  ↓
  Ve:
    - Tickets creados (todos los estados)
    - Filtro por estado/prioridad
    - Búsqueda
  ↓
  Click en ticket:
    - VE detalle
    - VE comentarios (solo públicos)
    - VE archivos adjuntos iniciales
    - PUEDE comentar (si está abierto)
    - PUEDE descargar archivos
  ↓
  Si agente comenta → Usuario recibe notificación
  ↓
  Si ticket se resuelve → Usuario recibe alerta
```

### Flujo 4: Administración

```
ADMIN
  ↓
  /admin/users → Gestionar usuarios (CRUD)
  /admin/departments → Crear/editar deptos
  /admin/categories → Crear/editar categorías
  ↓
  Puede:
    - Crear/editar/desactivar usuarios
    - Asignar roles
    - Ver todos los tickets
    - Generar reportes
    - Exportar a CSV
```

---

## Ejemplos de Uso API

### Obtener Departamentos Receptores

```javascript
fetch('/gestion-tickets/api/receptores')
  .then(r => r.json())
  .then(data => {
    // data.data = [{id: 2, name: 'TI', ...}, ...]
    console.log(data.data);
  });
```

### Obtener Categorías de un Depto

```javascript
const deptId = 2; // TI
fetch(`/gestion-tickets/api/categorias/${deptId}`)
  .then(r => r.json())
  .then(data => {
    // data.data = [...categorías TI...]
  });
```

### Obtener Agentes de un Depto

```javascript
const deptId = 2; // TI
fetch(`/gestion-tickets/api/agents-by-department/${deptId}`)
  .then(r => r.json())
  .then(data => {
    // data.agents = [{id: 3, name: 'Armando TI', ...}, ...]
  });
```

### Cambiar Estado de Ticket

```javascript
fetch('/gestion-tickets/tickets/6/status', {
  method: 'PUT',
  headers: {
    'X-CSRF-Token': CSRF_TOKEN,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    status: 'in_progress'
  })
})
.then(r => r.json())
.then(data => {
  if (data.success) {
    console.log('Estado actualizado');
  }
});
```

---

**Fin de la documentación técnica**

Para preguntas específicas, revisar comentarios en el código o abrir un issue en GitHub.

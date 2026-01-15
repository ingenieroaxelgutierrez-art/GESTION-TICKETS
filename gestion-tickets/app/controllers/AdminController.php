<?php
// app/controllers/AdminController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Category.php';

class AdminController extends Controller
{
    private $userModel;
    private $deptModel;
    private $catModel;

    public function __construct()
    {
        Auth::requireRole(['admin']); // SOLO ADMIN
        $this->userModel = new User();
        $this->deptModel = new Department();
        $this->catModel  = new Category();
    }

    // ========================================
    // USUARIOS
    // ========================================
    public function users()
    {
        // Detectar si es AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Si la petición es AJAX: devolvemos JSON
        if ($isAjax) {
            try {
                $users = $this->userModel->allWithDepartment();
                $this->json(['success' => true, 'data' => $users]);
                return;
            } catch (Exception $e) {
                $this->json(['success' => false, 'error' => $e->getMessage()], 500);
                return;
            }
        }
        
        // Si NO es AJAX, mostramos la vista de usuarios (HTML)
        $pageTitle = 'Usuarios - Admin';
        $this->view('admin/users', ['pageTitle' => $pageTitle]);
    }

    public function storeUser()
    {
        $this->onlyJson();
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        $data = [
            'name'           => trim($_POST['name'] ?? ''),
            'email'          => trim($_POST['email'] ?? ''),
            'password'       => $_POST['password'] ?? '',
            'department_id'  => (int)($_POST['department_id'] ?? 0),
            'role'           => $_POST['role'] ?? 'user'
        ];

        if (empty($data['name']) || empty($data['email']) || empty($data['password']) || $data['department_id'] <= 0) {
            return $this->json(['success' => false, 'error' => 'Faltan datos obligatorios']);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Email inválido']);
        }

        if ($this->userModel->findByEmail($data['email'])) {
            return $this->json(['success' => false, 'error' => 'El email ya está registrado']);
        }

        if ($this->userModel->create($data)) {
            $this->json(['success' => true, 'message' => 'Usuario creado correctamente']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al crear usuario'], 500);
        }
    }

    public function updateUser($id)
    {
        $this->onlyJson();
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        $data = [
            'name'          => trim($_POST['name'] ?? ''),
            'department_id' => (int)($_POST['department_id'] ?? 0),
            'role'          => $_POST['role'] ?? 'user',
            'active'        => isset($_POST['active']) ? 1 : 0
        ];

        if (empty($data['name']) || $data['department_id'] <= 0) {
            return $this->json(['success' => false, 'error' => 'Datos inválidos']);
        }

        $sql = "UPDATE users SET name = ?, department_id = ?, role = ?, active = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql); // AQUÍ ESTABA EL ERROR: usamos $this->db
        $success = $stmt->execute([
            $data['name'], $data['department_id'], $data['role'], $data['active'], $id
        ]);

        if ($success) { // AQUÍ ESTABA EL OTRO ERROR: $success en vez de success
            $this->json(['success' => true, 'message' => 'Usuario actualizado']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar'], 500);
        }
    }

    public function changePassword($id)
    {
        $this->onlyJson();
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        $password = $_POST['password'] ?? '';
        if (strlen($password) < 6) {
            return $this->json(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $success = $stmt->execute([$hash, $id]);

        if ($success) {
            $this->json(['success' => true, 'message' => 'Contraseña cambiada']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al cambiar contraseña'], 500);
        }
    }

    public function deleteUser($id)
    {
        $this->onlyJson();
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        // No permitir que se elimine a sí mismo
        if ($id == $_SESSION['user_id']) {
            return $this->json(['success' => false, 'error' => 'No puedes eliminar tu propia cuenta']);
        }

        try {
            // Primero eliminar tickets asignados a este usuario
            $stmt = $this->db->prepare("DELETE FROM tickets WHERE assigned_to = ?");
            $stmt->execute([$id]);

            // Luego eliminar el usuario
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $success = $stmt->execute([$id]);

            if ($success && $stmt->rowCount() > 0) {
                $this->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
            } else {
                $this->json(['success' => false, 'error' => 'No se pudo eliminar el usuario o es administrador']);
            }
        } catch (PDOException $e) {
            $this->json(['success' => false, 'error' => 'Error en base de datos'], 500);
        }
    }

    // ========================================
    // DEPARTAMENTOS
    // ========================================
    public function departments()
    {
        // Detectar si es AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Si la petición es AJAX: devolvemos JSON
        if ($isAjax) {
            try {
                $departments = $this->deptModel->getAll();
                $this->json(['success' => true, 'data' => $departments]);
                return;
            } catch (Exception $e) {
                $this->json(['success' => false, 'error' => $e->getMessage()], 500);
                return;
            }
        }
        
        // Si NO es AJAX, mostramos la vista HTML
        $pageTitle = 'Departamentos - Admin';
        $this->view('admin/departments', ['pageTitle' => $pageTitle]);
    }

    public function storeDepartment()
    {
        $this->onlyJson();
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'emisor';

        if (empty($name) || !in_array($type, ['emisor', 'receptor'])) {
            return $this->json(['success' => false, 'error' => 'Datos inválidos']);
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO departments (name, type) VALUES (?, ?)");
            $stmt->execute([$name, $type]);
            $this->json(['success' => true, 'message' => 'Departamento creado']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // duplicate entry
                $this->json(['success' => false, 'error' => 'El nombre ya existe']);
            } else {
                $this->json(['success' => false, 'error' => 'Error en base de datos'], 500);
            }
        }
    }

    // ========================================
    // CATEGORÍAS
    // ========================================
    public function categories()
    {
        // Detectar si es AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Si la petición es AJAX: devolvemos JSON
        if ($isAjax) {
            try {
                $categories = $this->catModel->getAllWithDepartment();
                $this->json(['success' => true, 'data' => $categories]);
                return;
            } catch (Exception $e) {
                $this->json(['success' => false, 'error' => $e->getMessage()], 500);
                return;
            }
        }
        
        // Si NO es AJAX, mostramos la vista HTML
        $pageTitle = 'Categorías - Admin';
        $this->view('admin/categories', ['pageTitle' => $pageTitle]);
    }

    public function storeCategory()
    {
        $this->onlyJson();
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            return $this->json(['success' => false, 'error' => 'Token inválido'], 403);
        }

        $data = [
            'department_id' => (int)($_POST['department_id'] ?? 0),
            'name'          => trim($_POST['name'] ?? ''),
            'description'   => trim($_POST['description'] ?? '')
        ];

        if ($data['department_id'] <= 0 || empty($data['name'])) {
            return $this->json(['success' => false, 'error' => 'Faltan datos']);
        }

        $stmt = $this->db->prepare("INSERT INTO categories (department_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$data['department_id'], $data['name'], $data['description']]);
        $this->json(['success' => true, 'message' => 'Categoría creada']);
    }
}
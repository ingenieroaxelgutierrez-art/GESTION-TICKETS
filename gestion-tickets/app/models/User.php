<?php
// app/models/User.php

require_once __DIR__ . '/../core/Model.php';

class User extends Model {
    protected $table = 'users';

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function create($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, department_id, role) 
                VALUES (:name, :email, :password, :department_id, :role)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    public function allWithDepartment()
    {
        $stmt = $this->db->query("
            SELECT u.*, d.name as department_name 
            FROM users u 
            LEFT JOIN departments d ON u.department_id = d.id 
            ORDER BY u.name
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener usuarios por rol
     */
    public function getByRole($role) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = ? ORDER BY name");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener agentes de un departamento específico para auto-asignación
     * Prioriza agentes activos, y si no hay, busca admins del departamento
     */
    public function getAgentsByDepartment($departmentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE department_id = ? 
            AND role IN ('agent', 'admin') 
            AND active = 1 
            ORDER BY 
                CASE WHEN role = 'agent' THEN 1 ELSE 2 END,
                RAND()
            LIMIT 1
        ");
        $stmt->execute([$departmentId]);
        return $stmt->fetch();
    }
}
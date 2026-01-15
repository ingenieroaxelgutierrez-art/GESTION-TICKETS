<?php
// app/models/Category.php

require_once __DIR__ . '/../core/Model.php';

class Category extends Model
{
    protected $table = 'categories';

    // Categorías según el departamento receptor seleccionado
    public function getByDepartment($department_id)
    {
        $stmt = $this->db->prepare("
            SELECT id, name, description 
            FROM {$this->table} 
            WHERE department_id = ? 
            ORDER BY name
        ");
        $stmt->execute([$department_id]);
        return $stmt->fetchAll();
    }

    // Todas las categorías (para admin)
    public function getAllWithDepartment()
    {
        $stmt = $this->db->query("
            SELECT c.*, d.name as department_name 
            FROM {$this->table} c
            JOIN departments d ON c.department_id = d.id
            ORDER BY d.name, c.name
        ");
        return $stmt->fetchAll();
    }
}
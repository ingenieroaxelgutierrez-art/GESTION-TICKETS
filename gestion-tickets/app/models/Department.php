<?php
// app/models/Department.php

require_once __DIR__ . '/../core/Model.php';

class Department extends Model
{
    protected $table = 'departments';

    // Devuelve todos los departamentos emisores (para el formulario del usuario)
    public function getEmisores()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE type = 'emisor' ORDER BY name");
        return $stmt->fetchAll();
    }

    // Devuelve todos los departamentos receptores (TI, Procesos, etc.)
    public function getReceptores()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE type = 'receptor' ORDER BY name");
        return $stmt->fetchAll();
    }

    // Para validar que el departamento destino sea realmente un receptor
    public function isReceptor($dept_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE id = ? AND type = 'receptor' LIMIT 1");
        $stmt->execute([$dept_id]);
        return $stmt->rowCount() > 0;
    }

    // Opcional: todos los departamentos (admin los necesita)
    public function getAll()
    {
        return $this->all();
    }
}

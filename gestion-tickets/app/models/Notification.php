<?php
/**
 * Modelo para Notificaciones
 */
class Notification extends Model
{
    protected $table = 'notifications';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtener notificaciones del usuario
     */
    public function getByUser($userId, $limit = 50)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener notificaciones sin leer
     */
    public function getUnread($userId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND read_at IS NULL
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Contar notificaciones sin leer
     */
    public function countUnread($userId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE user_id = ? AND read_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    /**
     * Marcar como leída
     */
    public function markAsRead($id)
    {
        $sql = "UPDATE {$this->table} SET read_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Marcar todas como leídas
     */
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE {$this->table} SET read_at = NOW() 
                WHERE user_id = ? AND read_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }

    /**
     * Eliminar notificación
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Eliminar todas las notificaciones del usuario
     */
    public function deleteAll($userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }

    /**
     * Crear notificación
     */
    public function create($userId, $title, $message, $type = 'system', $ticketId = null)
    {
        $sql = "INSERT INTO {$this->table} (user_id, title, message, type, related_ticket_id) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$userId, $title, $message, $type, $ticketId]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Obtener notificaciones por tipo
     */
    public function getByType($userId, $type)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND type = ?
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $type]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener notificación por ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}

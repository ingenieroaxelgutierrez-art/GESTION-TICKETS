<?php
/**
 * CONFIGURACIÓN DE EJEMPLO - Copiar a database.php en producción
 * 
 * Para usar en producción:
 * 1. Copiar este archivo a app/config/database.php
 * 2. Actualizar las credenciales
 */

return [
    'host' => '127.0.0.1',          // Host de la BD
    'port' => '3306',             // Puerto de la BD
    'dbname' => 'gestion_tickets',  // Nombre de la BD
    'user' => 'root',               // Usuario de BD
    'pass' => ''                    // Contraseña (vacío para desarrollo local)
];

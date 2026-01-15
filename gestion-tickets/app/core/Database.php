<?php
// app/core/Database.php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // Cargar configuración desde app/config/database.php
        $configPath = __DIR__ . '/../config/database.php';
        if (!file_exists($configPath)) {
            throw new \Exception('Archivo de configuración de base de datos no encontrado: ' . $configPath);
        }

        $config = require $configPath;

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $dbname = $config['dbname'] ?? '';
        $user = $config['user'] ?? 'root';
        $pass = $config['pass'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            $this->pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (\PDOException $e) {
            throw new \Exception("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    // Evitar clonación
    private function __clone() {}

    // Evitar deserialización
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
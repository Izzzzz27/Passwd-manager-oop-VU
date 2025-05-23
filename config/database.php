<?php
class DatabaseConfig {
    private const HOST = 'localhost';
    private const DB_NAME = 'password_manager';
    private const USERNAME = 'password_manager';
    private const PASSWORD = 'secure_password';
    
    public static function getConnection() {
        try {
            $conn = new PDO(
                "mysql:host=" . self::HOST . ";dbname=" . self::DB_NAME,
                self::USERNAME,
                self::PASSWORD
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch(PDOException $e) {
            die("Connection Error: " . $e->getMessage());
        }
    }
} 
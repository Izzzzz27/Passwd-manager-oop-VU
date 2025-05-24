<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $id;
    private $username;
    private $password;
    private $encryptionKey;
    private $db;

    public function __construct() {
        try {
            $this->db = DatabaseConfig::getConnection();
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    public function register($username, $password) {
        try {
            // Generate a random IV for AES encryption
            $iv = random_bytes(16);
            
            // Encrypt the password using AES-256-CBC
            $encryptedPassword = openssl_encrypt(
                $password,
                'AES-256-CBC',
                $password, // Use the password as both the key and the data
                OPENSSL_RAW_DATA,
                $iv
            );
            
            // Combine IV and encrypted password for storage
            $hashedPassword = base64_encode($iv . $encryptedPassword);
            
            // Generate encryption key using AES
            $encryptionKey = $this->generateEncryptionKey($password);
            
            $stmt = $this->db->prepare("INSERT INTO users (username, password, encryption_key) VALUES (?, ?, ?)");
            return $stmt->execute([$username, $hashedPassword, $encryptionKey]);
        } catch(PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return false;
        }
    }

    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Decrypt the stored password
                $storedData = base64_decode($user['password']);
                $iv = substr($storedData, 0, 16);
                $encryptedPassword = substr($storedData, 16);
                
                $decryptedPassword = openssl_decrypt(
                    $encryptedPassword,
                    'AES-256-CBC',
                    $password,
                    OPENSSL_RAW_DATA,
                    $iv
                );
                
                if ($decryptedPassword === $password) {
                    $this->id = $user['id'];
                    $this->username = $user['username'];
                    $this->password = $user['password'];
                    $this->encryptionKey = $user['encryption_key'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['encryption_key'] = $user['encryption_key'];
                    return true;
                }
            }
            return false;
        } catch(PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return false;
        }
    }

    private function generateEncryptionKey($password) {
        // Generate a random IV for AES encryption
        $iv = random_bytes(16);
        
        // Encrypt the password using AES-256-CBC
        $encryptedKey = openssl_encrypt(
            $password,
            'AES-256-CBC',
            $password, // Use the password as both the key and the data
            OPENSSL_RAW_DATA,
            $iv
        );
        
        // Combine IV and encrypted key
        return base64_encode($iv . $encryptedKey);
    }

    public function getEncryptionKey($password) {
        if (!$this->encryptionKey) {
            error_log("No encryption key available for user");
            return false;
        }

        try {
            // Extract IV from stored key
            $storedData = base64_decode($this->encryptionKey);
            if ($storedData === false) {
                error_log("Failed to decode encryption key");
                return false;
            }
            
            $iv = substr($storedData, 0, 16);
            $encryptedKey = substr($storedData, 16);
            
            // Decrypt the key using the password
            $key = openssl_decrypt(
                $encryptedKey,
                'AES-256-CBC',
                $password,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            if ($key === false) {
                error_log("Failed to decrypt encryption key");
                return false;
            }
            
            return $key;
        } catch (Exception $e) {
            error_log("Encryption Key Error: " . $e->getMessage());
            return false;
        }
    }

    public function getId() {
        return $this->id;
    }
} 
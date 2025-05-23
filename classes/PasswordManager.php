<?php
require_once __DIR__ . '/../config/database.php';

class PasswordManager {
    private $db;
    private $userId;
    private $encryptionKey;

    public function __construct($userId, $encryptionKey) {
        try {
            $this->db = DatabaseConfig::getConnection();
            $this->userId = $userId;
            $this->encryptionKey = $encryptionKey;
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function savePassword($website, $password) {
        try {
            if (!$this->encryptionKey) {
                throw new Exception("Encryption key not set");
            }

            $encryptedPassword = $this->encryptPassword($password);
            if ($encryptedPassword === false) {
                throw new Exception("Password encryption failed");
            }

            $stmt = $this->db->prepare("INSERT INTO passwords (user_id, website, password, created_at) VALUES (?, ?, ?, NOW())");
            return $stmt->execute([$this->userId, $website, $encryptedPassword]);
        } catch(Exception $e) {
            error_log("Save Error: " . $e->getMessage());
            return false;
        }
    }

    public function getPasswords() {
        try {
            if (!$this->encryptionKey) {
                throw new Exception("Encryption key not set");
            }

            $stmt = $this->db->prepare("SELECT * FROM passwords WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$this->userId]);
            $passwords = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Decrypt passwords
            foreach ($passwords as &$password) {
                $decryptedPassword = $this->decryptPassword($password['password']);
                if ($decryptedPassword === false) {
                    throw new Exception("Password decryption failed");
                }
                $password['password'] = $decryptedPassword;
            }

            return $passwords;
        } catch(Exception $e) {
            error_log("Retrieve Error: " . $e->getMessage());
            return [];
        }
    }

    public function updatePassword($website, $newPassword) {
        try {
            if (!$this->encryptionKey) {
                throw new Exception("Encryption key not set");
            }

            $encryptedPassword = $this->encryptPassword($newPassword);
            if ($encryptedPassword === false) {
                throw new Exception("Password encryption failed");
            }

            $stmt = $this->db->prepare("UPDATE passwords SET password = ?, created_at = NOW() WHERE user_id = ? AND website = ?");
            return $stmt->execute([$encryptedPassword, $this->userId, $website]);
        } catch(Exception $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }

    private function encryptPassword($password) {
        try {
            // Decode the stored encryption key (which includes IV)
            $keyData = base64_decode($this->encryptionKey);
            if ($keyData === false) {
                throw new Exception("Invalid encryption key format");
            }

            $iv = substr($keyData, 0, 16);
            $key = substr($keyData, 16);
            
            // Generate new IV for this password encryption
            $newIv = random_bytes(16);
            $encrypted = openssl_encrypt(
                $password,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $newIv
            );

            if ($encrypted === false) {
                throw new Exception("Encryption failed");
            }

            return base64_encode($newIv . $encrypted);
        } catch (Exception $e) {
            error_log("Encryption Error: " . $e->getMessage());
            return false;
        }
    }

    private function decryptPassword($encryptedPassword) {
        try {
            // Decode the stored encryption key (which includes IV)
            $keyData = base64_decode($this->encryptionKey);
            if ($keyData === false) {
                throw new Exception("Invalid encryption key format");
            }

            $iv = substr($keyData, 0, 16);
            $key = substr($keyData, 16);
            
            // Decode the password data
            $data = base64_decode($encryptedPassword);
            if ($data === false) {
                throw new Exception("Invalid encrypted password format");
            }

            $newIv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            
            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $newIv
            );

            if ($decrypted === false) {
                throw new Exception("Decryption failed");
            }

            return $decrypted;
        } catch (Exception $e) {
            error_log("Decryption Error: " . $e->getMessage());
            return false;
        }
    }
} 
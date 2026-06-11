<?php
/**
 * UserModel - PHP 5.5/5.6 Compatible
 * Handles user-related database operations
 */

class UserModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find user by username or email
     * @param string $identifier Username or email
     * @return array|false User data or false if not found
     */
    public function findByUsernameOrEmail($identifier) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute(array($identifier, $identifier));
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Find user by ID
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare('SELECT id, username, email, created_at FROM users WHERE id = ? LIMIT 1');
            $stmt->execute(array($id));
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Create new user
     * @param string $username Username
     * @param string $email Email
     * @param string $password Plain text password (will be hashed)
     * @return int|false Last insert ID or false on failure
     */
    public function create($username, $email, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare('INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute(array($username, $email, $hashedPassword));
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Update user last login timestamp
     * @param int $userId User ID
     * @return bool Success status
     */
    public function updateLastLogin($userId) {
        try {
            $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
            return $stmt->execute(array($userId));
        } catch (PDOException $e) {
            return false;
        }
    }
}

<?php
/**
 * User Model
 * PHP 5.6 Compatible
 */

class UserModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find user by username
     * @param string $username
     * @return array|null
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute(array($username));
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
    
    /**
     * Find user by email
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute(array($email));
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
    
    /**
     * Find user by ID
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $stmt = $this->db->prepare('SELECT id, username, email, role, created_at FROM users WHERE id = ?');
        $stmt->execute(array($id));
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
    
    /**
     * Create new user
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string $role
     * @return int Last insert ID
     */
    public function create($username, $email, $password, $role = 'user') {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)'
        );
        
        $stmt->execute(array($username, $email, $hashedPassword, $role));
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Verify user password
     * @param string $username
     * @param string $password
     * @return array|null User data if valid, null otherwise
     */
    public function verifyPassword($username, $password) {
        $user = $this->findByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            // Return user data without password
            unset($user['password']);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Update user last login
     * @param int $userId
     */
    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        $stmt->execute(array($userId));
    }
}

<?php
/**
 * Base Controller - PHP 5.5/5.6 Compatible
 * Provides common methods for all controllers
 * 
 * Rules Applied:
 * - No null coalescing (??)
 * - No arrow functions (fn())
 * - No scalar type declarations
 * - No return type declarations
 * - Uses openssl_random_pseudo_bytes() instead of random_bytes()
 */

class Controller {
    protected $db;
    protected $api;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->api = new ApiService();
    }
    
    /**
     * Load view file
     * @param string $view View name (without .php extension)
     * @param array $data Data to pass to view
     */
    protected function view($view, $data = array()) {
        extract($data);
        $viewPath = BASE_PATH . '/app/views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception('View file not found: ' . $view);
        }
    }
    
    /**
     * Redirect to URL
     * @param string $url URL to redirect to
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Return JSON response
     * @param mixed $data Data to encode
     */
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Require login
     */
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
            $this->redirect(BASE_URL . '/auth/login');
        }
    }
    
    /**
     * Generate CSRF token using PHP 5.3+ compatible method
     * Uses openssl_random_pseudo_bytes() instead of random_bytes()
     * @return string
     */
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            // PHP 5.3+ compatible alternative to random_bytes()
            if (function_exists('openssl_random_pseudo_bytes')) {
                $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
            } else {
                // Fallback for systems without openssl
                $_SESSION['csrf_token'] = hash('sha256', uniqid(mt_rand(), true));
            }
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     * Note: hash_equals() is available since PHP 5.6
     * For PHP 5.5, we use a timing-safe comparison fallback
     * @param string $token Token to verify
     * @return bool
     */
    protected function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        $expected = $_SESSION['csrf_token'];
        
        // Use hash_equals if available (PHP 5.6+)
        if (function_exists('hash_equals')) {
            return hash_equals($expected, $token);
        }
        
        // Fallback for PHP 5.5: timing-safe comparison
        if (strlen($expected) !== strlen($token)) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < strlen($expected); $i++) {
            $result |= ord($expected[$i]) ^ ord($token[$i]);
        }
        
        return $result === 0;
    }
}

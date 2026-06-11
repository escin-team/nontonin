<?php
/**
 * Base Controller
 * PHP 5.6 Compatible
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
     * @param string $view View name
     * @param array $data Data to pass to view
     */
    protected function view($view, $data = array()) {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("View file not found: " . $view);
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
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect(BASE_URL . '/auth/login');
        }
    }
    
    /**
     * Generate CSRF token
     * @return string
     */
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            // Use openssl_random_pseudo_bytes for PHP 5.6 compatibility
            if (function_exists('openssl_random_pseudo_bytes')) {
                $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
            } else {
                // Fallback for systems without openssl
                $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
            }
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     * @param string $token Token to verify
     * @return bool
     */
    protected function verifyCsrfToken($token) {
        // Use hash_equals if available (PHP 5.6+), otherwise fallback to simple comparison
        if (function_exists('hash_equals')) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        } else {
            // Fallback for older PHP versions (less secure against timing attacks)
            return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
        }
    }
}

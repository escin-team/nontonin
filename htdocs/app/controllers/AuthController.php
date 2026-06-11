<?php
/**
 * AuthController - PHP 5.5/5.6 Compatible
 * Handles user authentication (login, register, logout)
 */

class AuthController extends Controller {
    
    /**
     * Show login page
     */
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/home');
            return;
        }
        
        $csrfToken = $this->generateCsrfToken();
        $error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
        unset($_SESSION['login_error']);
        
        $this->view('auth/login', array(
            'csrf_token' => $csrfToken,
            'error' => $error,
            'page_title' => 'Login'
        ));
    }
    
    /**
     * Process login form submission
     */
    public function loginPost() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['login_error'] = 'Invalid security token. Please try again.';
            $this->redirect(BASE_URL . '/auth/login');
            return;
        }
        
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Username and password are required.';
            $this->redirect(BASE_URL . '/auth/login');
            return;
        }
        
        try {
            // Check user in database
            $stmt = $this->db->prepare('SELECT id, username, email, password FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute(array($username, $username));
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect to intended page or home
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirectUrl = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    $this->redirect(BASE_URL . $redirectUrl);
                } else {
                    $this->redirect(BASE_URL . '/home');
                }
                return;
            }
            
            $_SESSION['login_error'] = 'Invalid username or password.';
            $this->redirect(BASE_URL . '/auth/login');
            
        } catch (PDOException $e) {
            $_SESSION['login_error'] = 'An error occurred. Please try again later.';
            $this->redirect(BASE_URL . '/auth/login');
        }
    }
    
    /**
     * Show registration page
     */
    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/home');
            return;
        }
        
        $csrfToken = $this->generateCsrfToken();
        $error = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : '';
        unset($_SESSION['register_error']);
        
        $this->view('auth/register', array(
            'csrf_token' => $csrfToken,
            'error' => $error,
            'page_title' => 'Register'
        ));
    }
    
    /**
     * Process registration form submission
     */
    public function registerPost() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['register_error'] = 'Invalid security token. Please try again.';
            $this->redirect(BASE_URL . '/auth/register');
            return;
        }
        
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['register_error'] = 'All fields are required.';
            $this->redirect(BASE_URL . '/auth/register');
            return;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['register_error'] = 'Password must be at least 6 characters long.';
            $this->redirect(BASE_URL . '/auth/register');
            return;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['register_error'] = 'Passwords do not match.';
            $this->redirect(BASE_URL . '/auth/register');
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = 'Invalid email address.';
            $this->redirect(BASE_URL . '/auth/register');
            return;
        }
        
        try {
            // Check if username or email already exists
            $stmt = $this->db->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute(array($username, $email));
            
            if ($stmt->fetch()) {
                $_SESSION['register_error'] = 'Username or email already exists.';
                $this->redirect(BASE_URL . '/auth/register');
                return;
            }
            
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare('INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute(array($username, $email, $hashedPassword));
            
            // Auto login after registration
            $_SESSION['user_id'] = $this->db->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            
            $this->redirect(BASE_URL . '/home');
            
        } catch (PDOException $e) {
            $_SESSION['register_error'] = 'An error occurred. Please try again later.';
            $this->redirect(BASE_URL . '/auth/register');
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Clear session data
        $_SESSION = array();
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        $this->redirect(BASE_URL . '/auth/login');
    }
}

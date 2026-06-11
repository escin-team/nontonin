<?php
/**
 * Auth Controller
 * Handles user registration and login
 * PHP 5.6 Compatible
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Show login page
     */
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/home');
        }
        
        $error = '';
        $csrfToken = $this->generateCsrfToken();
        
        $this->view('auth/login', array(
            'error' => $error,
            'csrf_token' => $csrfToken
        ));
    }
    
    /**
     * Process login form
     */
    public function loginPost() {
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/home');
        }
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $this->view('auth/login', array(
                'error' => 'Invalid security token. Please try again.',
                'csrf_token' => $this->generateCsrfToken()
            ));
            return;
        }
        
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->view('auth/login', array(
                'error' => 'Username and password are required.',
                'csrf_token' => $this->generateCsrfToken()
            ));
            return;
        }
        
        // Verify credentials
        $user = $this->userModel->verifyPassword($username, $password);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login
            $this->userModel->updateLastLogin($user['id']);
            
            // Redirect to intended page or home
            $redirectUrl = isset($_SESSION['redirect_after_login']) ? 
                $_SESSION['redirect_after_login'] : BASE_URL . '/home';
            unset($_SESSION['redirect_after_login']);
            
            $this->redirect($redirectUrl);
        } else {
            $this->view('auth/login', array(
                'error' => 'Invalid username or password.',
                'csrf_token' => $this->generateCsrfToken()
            ));
        }
    }
    
    /**
     * Show registration page
     */
    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/home');
        }
        
        $error = '';
        $csrfToken = $this->generateCsrfToken();
        
        $this->view('auth/register', array(
            'error' => $error,
            'csrf_token' => $csrfToken
        ));
    }
    
    /**
     * Process registration form
     */
    public function registerPost() {
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/home');
        }
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $this->view('auth/register', array(
                'error' => 'Invalid security token. Please try again.',
                'csrf_token' => $this->generateCsrfToken()
            ));
            return;
        }
        
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validate input
        $errors = array();
        
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        // Check if username or email already exists
        if ($this->userModel->findByUsername($username)) {
            $errors[] = 'Username already taken.';
        }
        
        if ($this->userModel->findByEmail($email)) {
            $errors[] = 'Email already registered.';
        }
        
        if (!empty($errors)) {
            $this->view('auth/register', array(
                'error' => implode(' ', $errors),
                'csrf_token' => $this->generateCsrfToken()
            ));
            return;
        }
        
        // Create user
        try {
            $userId = $this->userModel->create($username, $email, $password);
            
            // Auto login after registration
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            
            $this->redirect(BASE_URL . '/home');
        } catch (Exception $e) {
            $this->view('auth/register', array(
                'error' => 'Registration failed. Please try again.',
                'csrf_token' => $this->generateCsrfToken()
            ));
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_start();
        $_SESSION = array();
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        
        session_destroy();
        $this->redirect(BASE_URL . '/auth/login');
    }
}

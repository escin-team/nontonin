<?php
/**
 * Application Entry Point - DramaStream
 * PHP 5.5/5.6 Compatible (AeonFree Hosting)
 * 
 * Rules Applied:
 * - No null coalescing (??)
 * - No arrow functions (fn())
 * - No scalar type declarations
 * - No random_bytes() - using openssl_random_pseudo_bytes()
 * - No anonymous classes
 * - Manual autoloader with spl_autoload_register
 */

// Define base path
define('BASE_PATH', dirname(__FILE__));

// Load configuration
require_once BASE_PATH . '/config/config.php';

// Register manual autoloader for PHP 5.5/5.6
spl_autoload_register(function($className) {
    // Core classes
    $corePath = BASE_PATH . '/app/core/' . $className . '.php';
    if (file_exists($corePath)) {
        require_once $corePath;
        return;
    }
    
    // Controllers
    $controllerPath = BASE_PATH . '/app/controllers/' . $className . '.php';
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        return;
    }
    
    // Models
    $modelPath = BASE_PATH . '/app/models/' . $className . '.php';
    if (file_exists($modelPath)) {
        require_once $modelPath;
        return;
    }
});

// Start session (PHP 5.4+ compatible check)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize router and dispatch
$router = new Router();

// ==================== ROUTES ====================

// Auth Routes
$router->get('/auth/login', 'AuthController@login');
$router->post('/auth/login', 'AuthController@loginPost');
$router->get('/auth/register', 'AuthController@register');
$router->post('/auth/register', 'AuthController@registerPost');
$router->get('/auth/logout', 'AuthController@logout');

// Home Route
$router->get('/home', 'HomeController@index');

// Drama Routes
$router->get('/drama/{slug}', 'DramaController@detail');
$router->get('/watch/{slug}/{episodeId}', 'DramaController@watch');
$router->post('/drama/update-progress', 'DramaController@updateProgress');

// Default route
$router->get('/', function() {
    if (isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/home');
    } else {
        header('Location: ' . BASE_URL . '/auth/login');
    }
});

// Dispatch routes
$router->dispatch();

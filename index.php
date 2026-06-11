<?php
/**
 * Application Entry Point - Nontonin (DramaStream)
 * PHP 5.5/5.6 - 8.3 Compatible (AeonFree/ByetHost Hosting)
 * 
 * Rules Applied:
 * - No null coalescing (??) - using isset() instead
 * - No arrow functions (fn()) - using traditional anonymous functions
 * - No scalar type declarations
 * - No short array syntax [] - using array() instead
 * - Using __DIR__ for all file paths
 * - SSL verification bypassed for cURL
 * - All URLs use url() helper to prevent double-slash 404 errors
 */

// Define base path using __DIR__ (PHP 5.3+)
define('BASE_PATH', __DIR__);

// Load configuration (includes url(), redirect(), e() helpers)
require_once __DIR__ . '/config/config.php';

// Register manual autoloader for PHP 5.5/5.6
spl_autoload_register(function($className) {
    // Core classes in /app/core/
    $corePath = __DIR__ . '/app/core/' . $className . '.php';
    if (file_exists($corePath)) {
        require_once $corePath;
        return;
    }
    
    // Controllers in /app/controllers/
    $controllerPath = __DIR__ . '/app/controllers/' . $className . '.php';
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        return;
    }
    
    // Models in /app/models/
    $modelPath = __DIR__ . '/app/models/' . $className . '.php';
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

// Drama Routes - Updated for DramaBos API with provider parameter
// Route: /drama/{provider}/{drama_id}
$router->get('/drama/{provider}/{id}', 'HomeController@detail');
$router->get('/drama/{provider}/{dramaId}', 'DramaController@detail');

// Watch/Streaming Routes
// Route: /watch/{provider}/{episode_id}
$router->get('/watch/{provider}/{episodeId}', 'DramaController@watch');

// AJAX Routes
$router->post('/drama/update-progress', 'DramaController@updateProgress');

// Default route
$router->get('/', function() {
    if (isset($_SESSION['user_id'])) {
        redirect('home');
    } else {
        redirect('auth/login');
    }
});

// Dispatch routes
$router->dispatch();

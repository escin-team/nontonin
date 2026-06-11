<?php
/**
 * Application Entry Point
 * PHP 5.6 Compatible
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Load core classes
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/ApiService.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize router
$router = new Router();

// ==================== ROUTES ====================

// Auth Routes
$router->get('/auth/login', function() {
    $controller = new AuthController();
    $controller->login();
});

$router->post('/auth/login', function() {
    $controller = new AuthController();
    $controller->loginPost();
});

$router->get('/auth/register', function() {
    $controller = new AuthController();
    $controller->register();
});

$router->post('/auth/register', function() {
    $controller = new AuthController();
    $controller->registerPost();
});

$router->get('/auth/logout', function() {
    $controller = new AuthController();
    $controller->logout();
});

// Home Route
$router->get('/home', function() {
    $controller = new HomeController();
    $controller->index();
});

// Drama Routes
$router->get('/drama/{slug}', function($slug) {
    $controller = new DramaController();
    $controller->detail($slug);
});

$router->get('/watch/{slug}/{episodeId}', function($slug, $episodeId) {
    $controller = new DramaController();
    $controller->watch($slug, $episodeId);
});

$router->post('/drama/update-progress', function() {
    $controller = new DramaController();
    $controller->updateProgress();
});

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

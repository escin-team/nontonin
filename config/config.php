<?php
/**
 * Database Configuration
 * PHP 5.6 Compatible
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'streaming_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

// DramaBos API Configuration
define('API_BASE_URL', 'https://prod-api.dramabos.live');
define('API_TOKEN', 'YOUR_BEARER_TOKEN_HERE');

// Cache Configuration
// Path is relative to config folder, going up one level to storage/cache
define('CACHE_PATH', __DIR__ . '/../storage/cache/');
define('CACHE_DURATION', 3600); // Cache duration in seconds (1 hour)
define('CACHE_DURATION_LONG', 21600); // Long cache duration (6 hours) for drama details
define('CACHE_DURATION_SHORT', 900); // Short cache duration (15 minutes) for stream URLs

// Application settings
// Dynamically determine BASE_URL based on server configuration
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = 'https';
} else {
    $protocol = 'http';
}
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$basePath = dirname($scriptName);
// Remove trailing slash if not root
if ($basePath !== '/' && substr($basePath, -1) === '/') {
    $basePath = rtrim($basePath, '/');
}
define('BASE_URL', $protocol . '://' . $host . $basePath);

define('APP_NAME', 'DramaStream');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);

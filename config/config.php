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

// API Configuration
define('API_BASE_URL', 'https://api.dramabos.com'); // Replace with actual API URL
define('API_KEY', ''); // Your API key if required
define('CACHE_DIR', __DIR__ . '/../storage/cache');
define('CACHE_DURATION', 3600); // Cache duration in seconds (1 hour)

// Application settings
define('BASE_URL', 'http://localhost/streaming/public');
define('APP_NAME', 'DramaStream');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);

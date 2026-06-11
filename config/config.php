<?php
/**
 * Database Configuration
 * PHP 5.6 - 8.3 Compatible
 * Anti-Crash for htmlspecialchars(null) on PHP 8.3
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'streaming_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

// DramaBos API Configuration
// Base URL: https://prod-api.dramabos.live
// Pattern: /{provider}/api/v1/{endpoint}
define('API_BASE_URL', 'https://prod-api.dramabos.live');
define('API_TOKEN', 'YOUR_BEARER_TOKEN_HERE'); // Replace with your actual token

// List of supported providers (30+ from DramaBos)
$GLOBALS['DRAMABOS_PROVIDERS'] = array(
    'dramabox', 'shortmax', 'reelshort', 'starshort', 'dramabite', 
    'freereels', 'fundrama', 'microdrama', 'vigloo', 'bilitv',
    'dramanice', 'kissasian', 'myasiantv', 'asiansister', 'newasiantv',
    'asianload', 'kdramaid', 'dramacool', 'watchasian', 'asiandramas',
    'rakuten', 'viki', 'netflix', 'disney', 'hulu', 'primevideo',
    'wetv', 'iqiyi', 'youku', 'mango', 'sohu', 'letv'
);

// Cache Configuration
// Path is relative to config folder, going up one level to storage/cache
define('CACHE_PATH', __DIR__ . '/../storage/cache/');
define('CACHE_DURATION', 3600); // Cache duration in seconds (1 hour)
define('CACHE_DURATION_LONG', 21600); // Long cache duration (6 hours) for drama details
define('CACHE_DURATION_SHORT', 900); // Short cache duration (15 minutes) for stream URLs

// Application settings
// Dynamically determine BASE_URL based on server configuration
// FIXED: Use rtrim() to ensure no trailing slash (prevents ByetHost 404 on double-slash)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = 'https';
} else {
    $protocol = 'http';
}
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$basePath = dirname($scriptName);
// Remove trailing slash if not root - CRITICAL for ByetHost
if ($basePath !== '/' && substr($basePath, -1) === '/') {
    $basePath = rtrim($basePath, '/');
}
define('BASE_URL', $protocol . '://' . $host . $basePath);

define('APP_NAME', 'Nontonin');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);

/**
 * Helper function: Generate URL without double-slash
 * CRITICAL: Prevents ByetHost 404 errors on URLs like //auth/login
 * @param string $path Relative path
 * @return string Full URL
 */
function url($path) {
    // Ensure path starts with single slash
    if (empty($path)) {
        return BASE_URL;
    }
    // Remove leading slashes from path to avoid double-slash
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

/**
 * Helper function: Redirect without double-slash
 * CRITICAL: Prevents ByetHost 404 errors
 * @param string $path Relative path
 */
function redirect($path) {
    $url = url($path);
    header('Location: ' . $url);
    exit;
}

/**
 * Helper function: Safe htmlspecialchars for PHP 8.3
 * PREVENTS: Fatal Error on htmlspecialchars(null) in PHP 8.3
 * @param mixed $string Input string
 * @param int $flags Flags for htmlspecialchars
 * @param string $encoding Character encoding
 * @param bool $doubleEncode Double encode flag
 * @return string Escaped string or empty string if null
 */
function e($string, $flags = ENT_QUOTES | ENT_HTML5, $encoding = 'UTF-8', $doubleEncode = false) {
    // Handle null, arrays, and objects safely
    if ($string === null) {
        return '';
    }
    if (is_array($string) || is_object($string)) {
        return '';
    }
    // Convert to string if needed (for integers, booleans, etc.)
    if (!is_string($string)) {
        $string = (string)$string;
    }
    return htmlspecialchars($string, $flags, $encoding, $doubleEncode);
}

/**
 * Get list of DramaBos providers
 * @return array List of provider slugs
 */
function getDramaBosProviders() {
    return isset($GLOBALS['DRAMABOS_PROVIDERS']) ? $GLOBALS['DRAMABOS_PROVIDERS'] : array();
}

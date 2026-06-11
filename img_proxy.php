<?php
/**
 * Image Proxy for DramaBos CDN Hotlink Protection Bypass
 * PHP 5.6 - 8.3 Compatible
 * 
 * Problem: cdn.dramabos.live blocks images if referer is not their domain
 * Solution: Proxy images through this script with fake referer + caching
 * 
 * Usage: <img src="img_proxy.php?url=<?php echo urlencode($imageUrl); ?>">
 * 
 * Features:
 * - Caches images for 7 days (604800 seconds)
 * - Fake referer header to bypass hotlink protection
 * - Fallback to no-poster.jpg on failure
 * - Atomic write to prevent corrupt cache files
 */

// Load configuration
require_once __DIR__ . '/config/config.php';

// Define cache directory for images
define('IMAGE_CACHE_PATH', CACHE_PATH . 'images/');

// Ensure image cache directory exists
if (!is_dir(IMAGE_CACHE_PATH)) {
    mkdir(IMAGE_CACHE_PATH, 0777, true);
}

// Get URL parameter
$imageUrl = isset($_GET['url']) ? $_GET['url'] : '';

// Validate URL
if (empty($imageUrl)) {
    // Return placeholder image
    header('Content-Type: image/jpeg');
    readfile(__DIR__ . '/assets/images/no-poster.jpg');
    exit;
}

// Decode URL if encoded
$imageUrl = urldecode($imageUrl);

// Validate that it's a valid HTTP/HTTPS URL
if (!preg_match('/^https?:\/\//i', $imageUrl)) {
    // Invalid URL, return placeholder
    header('Content-Type: image/jpeg');
    if (file_exists(__DIR__ . '/assets/images/no-poster.jpg')) {
        readfile(__DIR__ . '/assets/images/no-poster.jpg');
    } else {
        // Create simple placeholder
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    }
    exit;
}

// Generate cache key from URL (MD5 for safe filename)
$cacheKey = md5($imageUrl);
$cacheFile = IMAGE_CACHE_PATH . $cacheKey . '.jpg';

// Check if cached file exists and is fresh (7 days = 604800 seconds)
$cacheDuration = 604800;
if (file_exists($cacheFile)) {
    $fileModTime = filemtime($cacheFile);
    if ((time() - $fileModTime) < $cacheDuration) {
        // Serve from cache
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=' . $cacheDuration);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheDuration) . ' GMT');
        readfile($cacheFile);
        exit;
    }
}

// Fetch image from remote URL using cURL
function fetchImage($url) {
    $ch = curl_init();
    
    // cURL options - CRITICAL for ByetHost/AeonFree
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    // BYPASS SSL VERIFICATION - Required for ByetHost/AeonFree
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // FAKE REFERER - Critical to bypass hotlink protection
    // Set referer to the CDN domain itself
    curl_setopt($ch, CURLOPT_REFERER, 'https://cdn.dramabos.live/');
    
    // Fake user agent
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    
    // Request headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Connection: keep-alive'
    ));
    
    // Execute request
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    
    // Check for errors
    if ($curlErrno != 0 || !$imageData) {
        error_log('Image Proxy cURL Error [' . $curlErrno . ']: ' . $curlError . ' - URL: ' . $url);
        return null;
    }
    
    // Check HTTP status code
    if ($httpCode != 200) {
        error_log('Image Proxy HTTP Error: ' . $httpCode . ' - URL: ' . $url);
        return null;
    }
    
    // Verify that response is actually an image
    $finfo = new finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_buffer($finfo, $imageData);
    finfo_close($finfo);
    
    // Only accept image types
    $allowedMimeTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
    if (!in_array($mimeType, $allowedMimeTypes)) {
        error_log('Image Proxy: Invalid MIME type: ' . $mimeType . ' - URL: ' . $url);
        return null;
    }
    
    return $imageData;
}

// Fetch the image
$imageData = fetchImage($imageUrl);

if ($imageData === null) {
    // Failed to fetch, serve placeholder
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=3600');
    if (file_exists(__DIR__ . '/assets/images/no-poster.jpg')) {
        readfile(__DIR__ . '/assets/images/no-poster.jpg');
    } else {
        // Simple 1x1 transparent GIF as last resort
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    }
    exit;
}

// Save to cache atomically (write to temp file first, then rename)
$tempFile = $cacheFile . '.tmp';
if (file_put_contents($tempFile, $imageData, LOCK_EX) !== false) {
    // Atomic rename
    if (rename($tempFile, $cacheFile)) {
        // Successfully cached
        error_log('Image Proxy: Cached image - ' . $imageUrl);
    } else {
        // Rename failed, clean up temp file
        @unlink($tempFile);
    }
}

// Serve the image
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=' . $cacheDuration);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheDuration) . ' GMT');
echo $imageData;

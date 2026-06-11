<?php
/**
 * API Service Class
 * Handles communication with DramaBos API (30+ providers)
 * PHP 5.6 - 8.3 Compatible
 * 
 * DramaBos API Documentation:
 * - Base URL: https://prod-api.dramabos.live
 * - Pattern: /{provider}/api/v1/{endpoint}
 * - Auth Header: Authorization: Bearer {API_TOKEN}
 * 
 * Endpoints:
 * - Feed/Trending: /{provider}/api/v1/feed
 * - Detail Drama: /{provider}/api/v1/drama/{drama_id}
 * - List Episode: /{provider}/api/v1/drama/{drama_id}/episodes
 * - Streaming URL: /{provider}/api/v1/stream/{episode_id}
 */

class ApiService {
    private $baseUrl;
    private $apiToken;
    
    public function __construct() {
        $this->baseUrl = API_BASE_URL;
        $this->apiToken = API_TOKEN;
    }
    
    /**
     * Make HTTP request using cURL to DramaBos API
     * CRITICAL: SSL verification bypassed for ByetHost/AeonFree compatibility
     * @param string $provider Provider slug (dramabox, shortmax, reelshort, etc.)
     * @param string $endpoint API endpoint (feed, drama/{id}, stream/{id}, etc.)
     * @param int $cacheTime Cache duration in seconds (default 6 hours = 21600)
     * @return array|null JSON decoded response or null on failure
     */
    public function request($provider, $endpoint, $cacheTime = 21600) {
        // Build full URL: https://prod-api.dramabos.live/{provider}/api/v1/{endpoint}
        $url = $this->baseUrl . '/' . $provider . '/api/v1/' . $endpoint;
        
        // Generate cache key from URL (MD5 for filename safety)
        $cacheKey = md5($url);
        $cacheFile = CACHE_PATH . $cacheKey . '.json';
        
        // Ensure cache directory exists
        if (!is_dir(CACHE_PATH)) {
            mkdir(CACHE_PATH, 0755, true);
        }
        
        // Check cache first if cacheTime > 0
        if ($cacheTime > 0 && file_exists($cacheFile)) {
            $fileModTime = filemtime($cacheFile);
            if ((time() - $fileModTime) < $cacheTime) {
                $cachedData = file_get_contents($cacheFile);
                $data = json_decode($cachedData, true);
                if ($data !== null) {
                    return $data;
                }
            }
        }
        
        // Initialize cURL
        $ch = curl_init();
        
        // cURL options - CRITICAL for ByetHost/AeonFree
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Prevent hanging
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        // BYPASS SSL VERIFICATION - Required for ByetHost/AeonFree
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // Set headers with Bearer Token Authentication
        $headers = array(
            'Authorization: Bearer ' . $this->apiToken,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: en-US,en;q=0.9',
            'Content-Type: application/json'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($curlErrno != 0 || !$response) {
            error_log('API cURL Error [' . $curlErrno . ']: ' . $curlError . ' - URL: ' . $url);
            return null;
        }
        
        // Handle HTTP errors
        if ($httpCode != 200) {
            error_log('API HTTP Error: ' . $httpCode . ' - URL: ' . $url . ' - Response: ' . substr($response, 0, 200));
            return null;
        }
        
        // Decode JSON response
        $data = json_decode($response, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg() . ' - URL: ' . $url);
            return null;
        }
        
        // Save to cache if cacheTime > 0
        if ($cacheTime > 0) {
            $jsonData = json_encode($data);
            if ($jsonData !== false) {
                file_put_contents($cacheFile, $jsonData, LOCK_EX);
            }
        }
        
        return $data;
    }
    
    /**
     * Get feed/trending from a specific provider
     * Endpoint: /{provider}/api/v1/feed
     * @param string $provider Provider slug
     * @param int $cacheTime Cache duration in seconds
     * @return array|null List of dramas
     */
    public function getFeed($provider, $cacheTime = 21600) {
        return $this->request($provider, 'feed', $cacheTime);
    }
    
    /**
     * Get trending from a specific provider
     * Endpoint: /{provider}/api/v1/trending
     * @param string $provider Provider slug
     * @param int $cacheTime Cache duration in seconds
     * @return array|null List of trending dramas
     */
    public function getTrending($provider, $cacheTime = 21600) {
        return $this->request($provider, 'trending', $cacheTime);
    }
    
    /**
     * Get drama details by ID
     * Endpoint: /{provider}/api/v1/drama/{drama_id}
     * @param string $provider Provider slug
     * @param string $dramaId Drama ID from API
     * @param int $cacheTime Cache duration in seconds
     * @return array|null Drama details
     */
    public function getDramaDetails($provider, $dramaId, $cacheTime = 21600) {
        $endpoint = 'drama/' . urlencode($dramaId);
        return $this->request($provider, $endpoint, $cacheTime);
    }
    
    /**
     * Get episodes list for a drama
     * Endpoint: /{provider}/api/v1/drama/{drama_id}/episodes
     * @param string $provider Provider slug
     * @param string $dramaId Drama ID from API
     * @param int $cacheTime Cache duration in seconds
     * @return array|null List of episodes
     */
    public function getEpisodes($provider, $dramaId, $cacheTime = 21600) {
        $endpoint = 'drama/' . urlencode($dramaId) . '/episodes';
        return $this->request($provider, $endpoint, $cacheTime);
    }
    
    /**
     * Get streaming URL for an episode (HLS .m3u8)
     * Endpoint: /{provider}/api/v1/stream/{episode_id}
     * @param string $provider Provider slug
     * @param string $episodeId Episode ID from API
     * @param int $cacheTime Cache duration in seconds (short cache for streams)
     * @return array|null Stream data with m3u8 URL
     */
    public function getStreamUrl($provider, $episodeId, $cacheTime = 900) {
        $endpoint = 'stream/' . urlencode($episodeId);
        return $this->request($provider, $endpoint, $cacheTime);
    }
    
    /**
     * Clear all cached files
     * @return boolean Success status
     */
    public function clearCache() {
        if (!is_dir(CACHE_PATH)) {
            return false;
        }
        
        $files = glob(CACHE_PATH . '*.json');
        if ($files === false) {
            return false;
        }
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
    
    /**
     * Get cache file info
     * @param string $provider Provider slug
     * @param string $endpoint API endpoint
     * @return array|null Cache info or null if not cached
     */
    public function getCacheInfo($provider, $endpoint) {
        $url = $this->baseUrl . '/' . $provider . '/api/v1/' . $endpoint;
        $cacheKey = md5($url);
        $cacheFile = CACHE_PATH . $cacheKey . '.json';
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        return array(
            'file' => $cacheFile,
            'size' => filesize($cacheFile),
            'created' => filectime($cacheFile),
            'modified' => filemtime($cacheFile)
        );
    }
}

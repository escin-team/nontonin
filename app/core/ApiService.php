<?php
/**
 * API Service Class
 * Handles communication with DramaBos API (32+ providers)
 * PHP 5.6 Compatible - No modern syntax allowed
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
     * @param string $provider Provider slug (dramabox, shortmax, reelshort, etc.)
     * @param string $endpoint API endpoint (feed, trending, drama/{id}, etc.)
     * @param boolean $useCache Whether to use file caching
     * @param int $cacheTime Cache duration in seconds (default 6 hours)
     * @return mixed JSON decoded response or null on failure
     */
    public function request($provider, $endpoint, $useCache = true, $cacheTime = 21600) {
        // Build full URL: https://prod-api.dramabos.live/{provider}/api/v1/{endpoint}
        $url = $this->baseUrl . '/' . $provider . '/api/v1/' . $endpoint;
        
        // Generate cache key from URL
        $cacheKey = md5($url);
        $cacheFile = CACHE_PATH . $cacheKey . '.json';
        
        // Check cache first if enabled
        if ($useCache && file_exists($cacheFile)) {
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
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL on AeonFree
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Prevent hanging
        
        // Set headers with Bearer Token
        $headers = array(
            'Authorization: Bearer ' . $this->apiToken,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept: application/json',
            'Content-Type: application/json'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Handle errors
        if ($httpCode != 200 || !$response) {
            error_log('API Request failed: ' . $url . ' - HTTP ' . $httpCode . ' - ' . $curlError);
            return null;
        }
        
        // Decode JSON response
        $data = json_decode($response, true);
        if ($data === null) {
            error_log('JSON decode failed for: ' . $url);
            return null;
        }
        
        // Save to cache if enabled
        if ($useCache) {
            if (!is_dir(CACHE_PATH)) {
                mkdir(CACHE_PATH, 0755, true);
            }
            file_put_contents($cacheFile, json_encode($data));
        }
        
        return $data;
    }
    
    /**
     * Get feed/trending from a specific provider
     * @param string $provider Provider slug
     * @param string $type Feed type ('feed' or 'trending')
     * @param boolean $useCache Whether to cache
     * @param int $cacheTime Cache duration
     * @return array|null List of dramas
     */
    public function getProviderFeed($provider, $type = 'feed', $useCache = true, $cacheTime = 21600) {
        $endpoint = $type;
        return $this->request($provider, $endpoint, $useCache, $cacheTime);
    }
    
    /**
     * Get drama details by ID
     * @param string $provider Provider slug
     * @param string $dramaId Drama ID from API
     * @param boolean $useCache Whether to cache
     * @param int $cacheTime Cache duration (default 1 hour)
     * @return array|null Drama details
     */
    public function getDramaDetails($provider, $dramaId, $useCache = true, $cacheTime = 3600) {
        $endpoint = 'drama/' . $dramaId;
        return $this->request($provider, $endpoint, $useCache, $cacheTime);
    }
    
    /**
     * Get episodes list for a drama
     * @param string $provider Provider slug
     * @param string $dramaId Drama ID from API
     * @param boolean $useCache Whether to cache
     * @param int $cacheTime Cache duration (default 1 hour)
     * @return array|null List of episodes
     */
    public function getDramaEpisodes($provider, $dramaId, $useCache = true, $cacheTime = 3600) {
        $endpoint = 'drama/' . $dramaId . '/episodes';
        return $this->request($provider, $endpoint, $useCache, $cacheTime);
    }
    
    /**
     * Get streaming URL for an episode
     * @param string $provider Provider slug
     * @param string $episodeId Episode ID from API
     * @param boolean $useCache Whether to cache
     * @param int $cacheTime Cache duration (default 15 minutes)
     * @return array|null Stream data with m3u8 URL
     */
    public function getEpisodeStream($provider, $episodeId, $useCache = true, $cacheTime = 900) {
        $endpoint = 'stream/' . $episodeId;
        return $this->request($provider, $endpoint, $useCache, $cacheTime);
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
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
}

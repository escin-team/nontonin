<?php
/**
 * API Service Class
 * Handles communication with DramaBos API
 * PHP 5.6 Compatible
 */

class ApiService {
    private $baseUrl;
    private $apiKey;
    
    public function __construct() {
        $this->baseUrl = API_BASE_URL;
        $this->apiKey = API_KEY;
    }
    
    /**
     * Make HTTP request using cURL
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @return mixed JSON decoded response
     */
    private function request($endpoint, $params = array()) {
        $url = $this->baseUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable for development only
        
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json'
        );
        
        if (!empty($this->apiKey)) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200 && $response) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Get cached data or fetch from API
     * @param string $cacheKey Cache file identifier
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @return mixed Cached or fresh data
     */
    public function getCached($cacheKey, $endpoint, $params = array()) {
        $cacheFile = CACHE_DIR . '/' . md5($cacheKey) . '.json';
        
        // Check if cache exists and is not expired
        if (file_exists($cacheFile)) {
            $cacheTime = filemtime($cacheFile);
            if ((time() - $cacheTime) < CACHE_DURATION) {
                $cachedData = file_get_contents($cacheFile);
                $data = json_decode($cachedData, true);
                if ($data !== null) {
                    return $data;
                }
            }
        }
        
        // Fetch fresh data from API
        $data = $this->request($endpoint, $params);
        
        if ($data !== null) {
            // Save to cache
            if (!is_dir(CACHE_DIR)) {
                mkdir(CACHE_DIR, 0755, true);
            }
            file_put_contents($cacheFile, json_encode($data));
        }
        
        return $data;
    }
    
    /**
     * Get trending/popular dramas
     * @return array List of dramas
     */
    public function getTrendingDramas() {
        return $this->getCached('trending_dramas', '/dramas/trending', array(
            'category' => 'china',
            'limit' => 20
        ));
    }
    
    /**
     * Get drama details by ID
     * @param string $showId Drama ID from API
     * @return array Drama details
     */
    public function getDramaDetails($showId) {
        return $this->getCached('drama_' . $showId, '/dramas/' . $showId);
    }
    
    /**
     * Get episode streaming URL
     * @param string $showId Drama ID
     * @param string $episodeId Episode ID
     * @return array Streaming URLs
     */
    public function getEpisodeStream($showId, $episodeId) {
        // Don't cache streaming URLs as they may expire
        return $this->request('/dramas/' . $showId . '/episodes/' . $episodeId . '/stream');
    }
    
    /**
     * Search dramas
     * @param string $query Search query
     * @return array Search results
     */
    public function searchDramas($query) {
        return $this->getCached('search_' . md5($query), '/dramas/search', array(
            'q' => $query
        ));
    }
}

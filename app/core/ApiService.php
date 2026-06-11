<?php
/**
 * API Service Class
 * Handles communication with DramaBos API (30+ providers)
 * PHP 5.6 - 8.3 Compatible
 * 
 * PENTING: SETIAP PROVIDER PUNYA ENDPOINT UNIK!
 * Jangan asumsikan semua provider pakai pattern /{provider}/api/v1/feed
 * 
 * DramaBos API Documentation:
 * - Base URL: https://prod-api.dramabos.live
 * - Auth Header: Authorization: Bearer {API_TOKEN}
 * 
 * Mapping Endpoint per Provider (FEED/TRENDING):
 * - ShortMax: /shortmax/api/v1/home, /shortmax/api/v1/foryou, /shortmax/api/v1/popular
 * - FlickReels: /flickreels/api/flickreels/trending?lang=en, /flickreels/api/flickreels/hotrank?lang=en
 * - DramaBox: /dramabox/api/v1/discover, /dramabox/api/v1/rank
 * - ReelShort: /reelshort/api/v1/featured
 * - StarShort: /starshort/api/v1/trending
 * - DramaBite: /dramabite/api/v1/recommend
 * - GoodShort: /goodshort/api/v1/toppicks
 * - ReelBuzz: /reelbuzz/api/v1/buzz
 * 
 * STRUKTUR RESPONSE:
 * Bisa berupa ARRAY LANGSUNG tanpa wrapper: [{...}, {...}]
 * Atau object dengan wrapper: {"data": [{...}, {...}]}
 */

class ApiService {
    private $baseUrl;
    private $apiToken;
    
    // Mapping endpoint feed/trending per provider berdasarkan dokumentasi resmi (32 providers)
    private $feedMap = array(
        'shortmax' => '/shortmax/api/v1/home',
        'flickreels' => '/flickreels/api/flickreels/trending?lang=en',
        'dramabox' => '/dramabox/api/v1/discover',
        'reelshort' => '/reelshort/api/v1/featured',
        'starshort' => '/starshort/api/v1/trending',
        'dramabite' => '/dramabite/api/v1/recommend',
        'goodshort' => '/goodshort/api/v1/toppicks',
        'reelbuzz' => '/reelbuzz/api/v1/buzz',
        'freereels' => '/freereels/api/v1/trending',
        'vigloo' => '/vigloo/api/v1/trending',
        'dramawave' => '/dramawave/api/v1/featured',
        'microdrama' => '/microdrama/api/v1/feed',
        'bilitv' => '/bilitv/api/v1/home',
        'netshort' => '/netshort/api/v1/hot',
        'melolo' => '/melolo/api/v1/hot',
        'velolo' => '/velolo/api/v1/trending',
        'stardusttv' => '/stardusttv/api/v1/stellar',
        'serialplus' => '/serialplus/api/v1/weekly',
        'dotdrama' => '/dotdrama/api/v1/featured',
        'rapidtv' => '/rapidtv/api/v1/trending',
        'shortswave' => '/shortswave/api/v1/wave',
        'dramanova' => '/dramanova/api/v1/nova',
        'cubetv' => '/cubetv/api/v1/hot',
        'flareflow' => '/flareflow/api/v1/flare',
        'moboreels' => '/moboreels/api/v1/hot',
        'happyshort' => '/happyshort/api/v1/happy',
        'reelife' => '/reelife/api/v1/daily',
        'pinedrama' => '/pinedrama/api/v1/hot',
        'flextv' => '/flextv/api/v1/trending',
        'reelala' => '/reelala/api/v1/hot',
        'idrama' => '/idrama/home?lang=id'
    );
    
    // Mapping endpoint detail per provider
    private $detailMap = array(
        'flickreels' => '/flickreels/api/flickreels/detail?id=',
        'idrama' => '/idrama/drama/',
        'dramabox' => '/dramabox/api/v1/detail/',
        'shortmax' => '/shortmax/api/v1/detail/',
        'reelshort' => '/reelshort/api/v1/detail/',
        'starshort' => '/starshort/api/v1/detail/',
        'dramabite' => '/dramabite/api/v1/detail/',
        'goodshort' => '/goodshort/api/v1/detail/',
        'reelbuzz' => '/reelbuzz/api/v1/detail/',
        'freereels' => '/freereels/api/v1/detail/',
        'vigloo' => '/vigloo/api/v1/detail/',
        'dramawave' => '/dramawave/api/v1/detail/',
        'microdrama' => '/microdrama/api/v1/detail/',
        'bilitv' => '/bilitv/api/v1/detail/',
        'netshort' => '/netshort/api/v1/detail/',
        'melolo' => '/melolo/api/v1/detail/',
        'velolo' => '/velolo/api/v1/detail/',
        'stardusttv' => '/stardusttv/api/v1/detail/',
        'serialplus' => '/serialplus/api/v1/detail/',
        'dotdrama' => '/dotdrama/api/v1/detail/',
        'rapidtv' => '/rapidtv/api/v1/detail/',
        'shortswave' => '/shortswave/api/v1/detail/',
        'dramanova' => '/dramanova/api/v1/detail/',
        'cubetv' => '/cubetv/api/v1/detail/',
        'flareflow' => '/flareflow/api/v1/detail/',
        'moboreels' => '/moboreels/api/v1/detail/',
        'happyshort' => '/happyshort/api/v1/detail/',
        'reelife' => '/reelife/api/v1/detail/',
        'pinedrama' => '/pinedrama/api/v1/detail/',
        'flextv' => '/flextv/api/v1/detail/',
        'reelala' => '/reelala/api/v1/detail/'
    );
    
    // Mapping endpoint episodes per provider
    private $episodesMap = array(
        'flickreels' => '/flickreels/api/flickreels/allepisode?id=',
        'idrama' => '/idrama/episodes/',
        'dramabox' => '/dramabox/api/v1/episodes/',
        'shortmax' => '/shortmax/api/v1/episodes/',
        'reelshort' => '/reelshort/api/v1/episodes/',
        'starshort' => '/starshort/api/v1/episodes/',
        'dramabite' => '/dramabite/api/v1/episodes/',
        'goodshort' => '/goodshort/api/v1/episodes/',
        'reelbuzz' => '/reelbuzz/api/v1/episodes/',
        'freereels' => '/freereels/api/v1/episodes/',
        'vigloo' => '/vigloo/api/v1/episodes/',
        'dramawave' => '/dramawave/api/v1/episodes/',
        'microdrama' => '/microdrama/api/v1/episodes/',
        'bilitv' => '/bilitv/api/v1/episodes/',
        'netshort' => '/netshort/api/v1/episodes/',
        'melolo' => '/melolo/api/v1/episodes/',
        'velolo' => '/velolo/api/v1/episodes/',
        'stardusttv' => '/stardusttv/api/v1/episodes/',
        'serialplus' => '/serialplus/api/v1/episodes/',
        'dotdrama' => '/dotdrama/api/v1/episodes/',
        'rapidtv' => '/rapidtv/api/v1/episodes/',
        'shortswave' => '/shortswave/api/v1/episodes/',
        'dramanova' => '/dramanova/api/v1/episodes/',
        'cubetv' => '/cubetv/api/v1/episodes/',
        'flareflow' => '/flareflow/api/v1/episodes/',
        'moboreels' => '/moboreels/api/v1/episodes/',
        'happyshort' => '/happyshort/api/v1/episodes/',
        'reelife' => '/reelife/api/v1/episodes/',
        'pinedrama' => '/pinedrama/api/v1/episodes/',
        'flextv' => '/flextv/api/v1/episodes/',
        'reelala' => '/reelala/api/v1/episodes/'
    );
    
    // Mapping endpoint stream per provider
    private $streamMap = array(
        'flickreels' => '/flickreels/api/flickreels/episode?id=',
        'idrama' => '/idrama/play/',
        'dramabox' => '/dramabox/api/v1/play/',
        'shortmax' => '/shortmax/api/v1/play/',
        'reelshort' => '/reelshort/api/v1/play/',
        'starshort' => '/starshort/api/v1/play/',
        'dramabite' => '/dramabite/api/v1/play/',
        'goodshort' => '/goodshort/api/v1/play/',
        'reelbuzz' => '/reelbuzz/api/v1/play/',
        'freereels' => '/freereels/api/v1/play/',
        'vigloo' => '/vigloo/api/v1/play/',
        'dramawave' => '/dramawave/api/v1/play/',
        'microdrama' => '/microdrama/api/v1/play/',
        'bilitv' => '/bilitv/api/v1/play/',
        'netshort' => '/netshort/api/v1/play/',
        'melolo' => '/melolo/api/v1/play/',
        'velolo' => '/velolo/api/v1/play/',
        'stardusttv' => '/stardusttv/api/v1/play/',
        'serialplus' => '/serialplus/api/v1/play/',
        'dotdrama' => '/dotdrama/api/v1/play/',
        'rapidtv' => '/rapidtv/api/v1/play/',
        'shortswave' => '/shortswave/api/v1/play/',
        'dramanova' => '/dramanova/api/v1/play/',
        'cubetv' => '/cubetv/api/v1/play/',
        'flareflow' => '/flareflow/api/v1/play/',
        'moboreels' => '/moboreels/api/v1/play/',
        'happyshort' => '/happyshort/api/v1/play/',
        'reelife' => '/reelife/api/v1/play/',
        'pinedrama' => '/pinedrama/api/v1/play/',
        'flextv' => '/flextv/api/v1/play/',
        'reelala' => '/reelala/api/v1/play/'
    );
    
    public function __construct() {
        $this->baseUrl = API_BASE_URL;
        $this->apiToken = API_TOKEN;
    }
    
    /**
     * Normalize response dari berbagai format ke format konsisten
     * Response bisa berupa array langsung ATAU object dengan wrapper 'data'
     * @param mixed $response Raw response dari API
     * @return array Format konsisten: array('data' => [...])
     */
    private function normalizeResponse($response) {
        // Jika response kosong atau bukan array, kembalikan format standar
        if (empty($response) || !is_array($response)) {
            return array('data' => array());
        }
        
        // Cek apakah response adalah array langsung (tanpa wrapper)
        // Ciri-ciri: index pertama adalah integer (0, 1, 2, ...)
        $keys = array_keys($response);
        $isIndexedArray = (count($keys) > 0 && isset($keys[0]) && is_int($keys[0]));
        
        if ($isIndexedArray) {
            // Ini adalah array langsung, bungkus dengan key 'data'
            return array('data' => $response);
        }
        
        // Cek apakah ada wrapper 'data'
        if (isset($response['data']) && is_array($response['data'])) {
            return array('data' => $response['data']);
        }
        
        // Cek wrapper alternatif: 'items', 'list', 'result', 'items'
        $wrapperKeys = array('items', 'list', 'result', 'movies', 'videos');
        foreach ($wrapperKeys as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                return array('data' => $response[$key]);
            }
        }
        
        // Jika tidak ada wrapper yang dikenali, kembalikan apa adanya
        return array('data' => $response);
    }
    
    /**
     * Make HTTP request using cURL to DramaBos API
     * CRITICAL: SSL verification bypassed for ByetHost/AeonFree compatibility
     * @param string $url Full URL untuk request
     * @param int $cacheTime Cache duration in seconds (default 6 hours = 21600)
     * @return array|null JSON decoded response atau null pada failure
     */
    private function makeRequest($url, $cacheTime = 21600) {
        // Generate cache key dari URL (MD5 untuk keamanan filename)
        $cacheKey = md5($url);
        $cacheFile = CACHE_PATH . $cacheKey . '.json';
        
        // Pastikan direktori cache ada
        if (!is_dir(CACHE_PATH)) {
            mkdir(CACHE_PATH, 0755, true);
        }
        
        // Cek cache dulu jika cacheTime > 0
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
        
        // Inisialisasi cURL
        $ch = curl_init();
        
        // Opsi cURL - CRITICAL untuk ByetHost/AeonFree
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Mencegah hanging
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        // BYPASS SSL VERIFICATION - Diperlukan untuk ByetHost/AeonFree
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // Set headers dengan Bearer Token Authentication
        $headers = array(
            'Authorization: Bearer ' . $this->apiToken,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: en-US,en;q=0.9',
            'Content-Type: application/json'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Eksekusi request
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
        
        // Simpan ke cache jika cacheTime > 0
        if ($cacheTime > 0) {
            $jsonData = json_encode($data);
            if ($jsonData !== false) {
                file_put_contents($cacheFile, $jsonData, LOCK_EX);
            }
        }
        
        return $data;
    }
    
    /**
     * Generic request method dengan custom endpoint
     * @param string $provider Provider slug
     * @param string $endpoint Custom endpoint path
     * @param int $cacheTime Cache duration in seconds
     * @return array|null Response ternormalisasi
     */
    public function request($provider, $endpoint, $cacheTime = 21600) {
        $url = $this->baseUrl . '/' . $endpoint;
        $rawResponse = $this->makeRequest($url, $cacheTime);
        
        if ($rawResponse === null) {
            return null;
        }
        
        return $this->normalizeResponse($rawResponse);
    }
    
    /**
     * Get trending dari provider spesifik dengan endpoint unik per provider
     * Menggunakan mapping endpoint berdasarkan dokumentasi resmi DramaBos
     * @param string $provider Provider slug (dramabox, shortmax, reelshort, dll)
     * @param int $cacheTime Cache duration in seconds (default 6 jam = 21600)
     * @return array Format konsisten: array('data' => [...])
     */
    public function getTrending($provider, $cacheTime = 21600) {
        // Cek apakah provider punya endpoint khusus dalam mapping
        $providerLower = strtolower($provider);
        
        if (isset($this->feedMap[$providerLower])) {
            // Gunakan endpoint khusus dari mapping
            $endpoint = $this->feedMap[$providerLower];
        } else {
            // Fallback ke pattern default untuk provider yang belum di-mapping
            $endpoint = '/' . $providerLower . '/api/v1/feed';
        }
        
        // Buat request dan normalisasi response
        $rawResponse = $this->makeRequest($this->baseUrl . $endpoint, $cacheTime);
        
        if ($rawResponse === null) {
            return array('data' => array());
        }
        
        return $this->normalizeResponse($rawResponse);
    }
    
    /**
     * Get drama detail by ID dengan endpoint unik per provider
     * @param string $provider Provider slug
     * @param string $dramaId Drama ID dari API
     * @param int $cacheTime Cache duration in seconds
     * @return array|null Drama details ternormalisasi
     */
    public function getDramaDetail($provider, $dramaId, $cacheTime = 21600) {
        $providerLower = strtolower($provider);
        $encodedId = urlencode($dramaId);
        
        // Cek mapping endpoint detail per provider
        if (isset($this->detailMap[$providerLower])) {
            $endpointPattern = $this->detailMap[$providerLower];
            
            // Khusus flickreels yang pakai query parameter ?id=
            if ($providerLower === 'flickreels') {
                $endpoint = $endpointPattern . $encodedId;
            } else {
                $endpoint = $endpointPattern . $encodedId;
            }
        } else {
            // Fallback ke pattern default
            $endpoint = '/' . $providerLower . '/api/v1/detail/' . $encodedId;
        }
        
        $rawResponse = $this->makeRequest($this->baseUrl . $endpoint, $cacheTime);
        
        if ($rawResponse === null) {
            return null;
        }
        
        return $this->normalizeResponse($rawResponse);
    }
    
    /**
     * Get episodes list for a drama dengan endpoint unik per provider
     * Endpoint: sesuai mapping episodesMap atau fallback ke pattern default
     * @param string $provider Provider slug
     * @param string $dramaId Drama ID from API
     * @param int $cacheTime Cache duration in seconds
     * @return array|null List of episodes
     */
    public function getEpisodes($provider, $dramaId, $cacheTime = 21600) {
        $providerLower = strtolower($provider);
        $encodedId = urlencode($dramaId);
        
        // Cek mapping endpoint episodes per provider
        if (isset($this->episodesMap[$providerLower])) {
            $endpointPattern = $this->episodesMap[$providerLower];
            
            // Khusus flickreels yang pakai query parameter ?id=
            if ($providerLower === 'flickreels') {
                $endpoint = $endpointPattern . $encodedId;
            } else {
                $endpoint = $endpointPattern . $encodedId;
            }
        } else {
            // Fallback ke pattern default
            $endpoint = '/' . $providerLower . '/api/v1/episodes/' . $encodedId;
        }
        
        $rawResponse = $this->makeRequest($this->baseUrl . $endpoint, $cacheTime);
        
        if ($rawResponse === null) {
            return null;
        }
        
        return $this->normalizeResponse($rawResponse);
    }
    
    /**
     * Get streaming URL for an episode (HLS .m3u8) dengan endpoint unik per provider
     * Endpoint: sesuai mapping streamMap atau fallback ke pattern default
     * @param string $provider Provider slug
     * @param string $dramaId Drama ID from API
     * @param string $episodeNum Episode number
     * @param int $cacheTime Cache duration in seconds (short cache for streams)
     * @return array|null Stream data with m3u8 URL
     */
    public function getStreamUrl($provider, $dramaId, $episodeNum, $cacheTime = 900) {
        $providerLower = strtolower($provider);
        $encodedId = urlencode($dramaId);
        $encodedEp = urlencode($episodeNum);
        
        // Cek mapping endpoint stream per provider
        if (isset($this->streamMap[$providerLower])) {
            $endpointPattern = $this->streamMap[$providerLower];
            
            // Khusus flickreels yang pakai query parameter ?id=&ep=
            if ($providerLower === 'flickreels') {
                $endpoint = $endpointPattern . $encodedId . '&ep=' . $encodedEp;
            } elseif ($providerLower === 'idrama') {
                // iDrama format: /idrama/play/{id}/{ep}?lang=id
                $endpoint = $endpointPattern . $encodedId . '/' . $encodedEp . '?lang=id';
            } else {
                // Standard format: /{provider}/api/v1/play/{id}/{ep}
                $endpoint = $endpointPattern . $encodedId . '/' . $encodedEp;
            }
        } else {
            // Fallback ke pattern default
            $endpoint = '/' . $providerLower . '/api/v1/play/' . $encodedId . '/' . $encodedEp;
        }
        
        $rawResponse = $this->makeRequest($this->baseUrl . $endpoint, $cacheTime);
        
        if ($rawResponse === null) {
            return null;
        }
        
        return $this->normalizeResponse($rawResponse);
    }
    
    /**
     * Get all providers list
     * @return array Array of all provider names
     */
    public function getAllProviders() {
        return array_keys($this->feedMap);
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
    
    /**
     * Search drama by keyword from DramaBos API
     * Endpoint: /{provider}/api/v1/search?keyword={query}
     * @param string $keyword Search keyword
     * @param string $provider Provider slug (default: dramabox)
     * @param int $cacheTime Cache duration in seconds (default 1 hour)
     * @return array|null List of dramas matching the keyword
     */
    public function searchDrama($keyword, $provider = 'dramabox', $cacheTime = 3600) {
        // Encode keyword for URL
        $encodedKeyword = urlencode($keyword);
        $endpoint = 'search?keyword=' . $encodedKeyword;
        return $this->request($provider, $endpoint, $cacheTime);
    }
    
    /**
     * Get drama list by genre/category from DramaBos API
     * Endpoint: /{provider}/api/v1/category/{genre_id}
     * @param string|int $genreId Genre/Category ID
     * @param string $provider Provider slug (default: dramabox)
     * @param int $cacheTime Cache duration in seconds (default 6 hours)
     * @return array|null List of dramas in the genre
     */
    public function getDramaByGenre($genreId, $provider = 'dramabox', $cacheTime = 21600) {
        $endpoint = 'category/' . urlencode($genreId);
        return $this->request($provider, $endpoint, $cacheTime);
    }
    
    /**
     * Get feed with custom endpoint - USED BY CRON AGGREGATOR
     * This method does NOT depend on other ApiService methods (self-contained)
     * @param string $endpoint Full endpoint path (e.g., '/shortmax/api/v1/popular')
     * @param int $cacheTime Cache duration in seconds
     * @return array|null Normalized response or null on failure
     */
    public function getFeedCustom($endpoint, $cacheTime = 21600) {
        // Build full URL
        $url = $this->baseUrl . $endpoint;
        
        // Make request using internal makeRequest method
        $rawResponse = $this->makeRequest($url, $cacheTime);
        
        if ($rawResponse === null) {
            return null;
        }
        
        // Normalize response to consistent format
        return $this->normalizeResponse($rawResponse);
    }
    
    /**
     * Get data from custom endpoint with optional params - FOR FUTURE USE
     * Self-contained method that doesn't depend on other ApiService methods
     * @param string $endpoint Full endpoint path
     * @param array $params Optional query parameters
     * @param int $cacheTime Cache duration in seconds
     * @return array|null Normalized response or null on failure
     */
    public function getCustomEndpoint($endpoint, $params = array(), $cacheTime = 21600) {
        // Build full URL with optional query parameters
        $url = $this->baseUrl . $endpoint;
        
        // Add query parameters if provided
        if (!empty($params) && is_array($params)) {
            $queryString = http_build_query($params);
            if (!empty($queryString)) {
                $url .= (strpos($endpoint, '?') !== false ? '&' : '?') . $queryString;
            }
        }
        
        // Make request using internal makeRequest method
        $rawResponse = $this->makeRequest($url, $cacheTime);
        
        if ($rawResponse === null) {
            return null;
        }
        
        // Normalize response to consistent format
        return $this->normalizeResponse($rawResponse);
    }
}

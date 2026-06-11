<?php
/**
 * Home Controller
 * Displays homepage with cached global feed from aggregator
 * PHP 5.6 Compatible - No modern syntax allowed
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ShowModel.php';
require_once __DIR__ . '/../core/ApiService.php';

class HomeController extends Controller {
    private $showModel;
    private $apiService;
    
    public function __construct() {
        parent::__construct();
        $this->showModel = new ShowModel();
        $this->apiService = new ApiService();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Display homepage - READS FROM CACHE ONLY (no real-time API calls)
     */
    public function index() {
        $this->requireLogin();
        
        // Path to global feed cache
        $cacheFile = CACHE_PATH . 'global_feed.json';
        
        $trendingShows = array();
        $cacheStatus = 'empty';
        $lastUpdated = null;
        
        // Read from global_feed.json cache
        if (file_exists($cacheFile)) {
            $cacheContent = file_get_contents($cacheFile);
            $cacheData = json_decode($cacheContent, true);
            
            if ($cacheData !== null && isset($cacheData['data']) && is_array($cacheData['data'])) {
                $trendingShows = $cacheData['data'];
                $cacheStatus = 'hit';
                $lastUpdated = isset($cacheData['last_updated']) ? $cacheData['last_updated'] : null;
                
                // Get China drama category ID for database mapping
                $chinaCategory = $this->getCategoryBySlug('drama-china');
                $categoryId = $chinaCategory ? $chinaCategory['id'] : 1;
                
                // Add local_id to each show by saving to database
                foreach ($trendingShows as &$show) {
                    if (is_array($show) && isset($show['id'])) {
                        $showId = $this->showModel->upsertFromApi($categoryId, $show);
                        $show['local_id'] = $showId;
                    }
                }
                unset($show); // Break reference
            }
        }
        
        // Get recently added shows from database
        $recentShows = $this->showModel->getAll(null, 12, 0);
        
        $this->view('home/index', array(
            'trending' => $trendingShows,
            'recent' => $recentShows,
            'page_title' => 'Home - ' . APP_NAME,
            'cache_status' => $cacheStatus,
            'last_updated' => $lastUpdated
        ));
    }
    
    /**
     * Display drama detail page
     * @param string $provider Provider slug (dramabox, shortmax, etc.)
     * @param string $dramaId Drama ID from API
     */
    public function detail($provider, $dramaId) {
        $this->requireLogin();
        
        // Validate provider
        $validProviders = array('dramabox', 'shortmax', 'reelshort', 'starshort', 'dramabite', 'freereels', 'fundrama', 'microdrama', 'vigloo', 'bilitv');
        if (!in_array($provider, $validProviders)) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }
        
        // Get drama details (cache 1 hour)
        $dramaDetails = $this->apiService->getDramaDetails($provider, $dramaId, true, 3600);
        
        // Get episodes list (cache 1 hour)
        $episodes = $this->apiService->getDramaEpisodes($provider, $dramaId, true, 3600);
        
        if (empty($dramaDetails)) {
            // Show error if drama not found
            http_response_code(404);
            $this->view('errors/404', array(
                'page_title' => 'Drama Not Found - ' . APP_NAME
            ));
            return;
        }
        
        // Add provider info to drama details
        $dramaDetails['source_provider'] = $provider;
        $dramaDetails['episodes'] = isset($episodes['data']) ? $episodes['data'] : (isset($episodes['episodes']) ? $episodes['episodes'] : array());
        
        // Get category for database mapping
        $category = $this->getCategoryBySlug('drama-china');
        $categoryId = $category ? $category['id'] : 1;
        
        // Save to database and get local ID
        $localId = $this->showModel->upsertFromApi($categoryId, $dramaDetails);
        $dramaDetails['local_id'] = $localId;
        
        $this->view('home/detail', array(
            'drama' => $dramaDetails,
            'provider' => $provider,
            'drama_id' => $dramaId,
            'page_title' => (isset($dramaDetails['title']) ? $dramaDetails['title'] : 'Detail') . ' - ' . APP_NAME
        ));
    }
    
    /**
     * Get streaming URL for an episode
     * @param string $provider Provider slug
     * @param string $episodeId Episode ID from API
     */
    public function stream($provider, $episodeId) {
        $this->requireLogin();
        
        // Validate provider
        $validProviders = array('dramabox', 'shortmax', 'reelshort', 'starshort', 'dramabite', 'freereels', 'fundrama', 'microdrama', 'vigloo', 'bilitv');
        if (!in_array($provider, $validProviders)) {
            header('Location: ' . BASE_URL . '/home');
            exit;
        }
        
        // Get stream URL (cache 15 minutes only)
        $streamData = $this->apiService->getEpisodeStream($provider, $episodeId, true, 900);
        
        if (empty($streamData)) {
            // Return JSON error
            header('Content-Type: application/json');
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Stream URL not found or expired'
            ));
            exit;
        }
        
        // Extract m3u8 URL from response
        $m3u8Url = null;
        $qualityUrls = array();
        
        if (isset($streamData['url'])) {
            $m3u8Url = $streamData['url'];
        } elseif (isset($streamData['play_url'])) {
            $m3u8Url = $streamData['play_url'];
        } elseif (isset($streamData['stream_url'])) {
            $m3u8Url = $streamData['stream_url'];
        } elseif (isset($streamData['data']['url'])) {
            $m3u8Url = $streamData['data']['url'];
        }
        
        // Handle multiple quality URLs if present
        if (isset($streamData['qualities']) && is_array($streamData['qualities'])) {
            $qualityUrls = $streamData['qualities'];
        }
        
        // Return JSON response with stream data
        header('Content-Type: application/json');
        echo json_encode(array(
            'status' => 'success',
            'provider' => $provider,
            'episode_id' => $episodeId,
            'm3u8_url' => $m3u8Url,
            'qualities' => $qualityUrls,
            'cached' => true
        ));
        exit;
    }
    
    /**
     * Get category by slug
     * @param string $slug
     * @return array|null
     */
    private function getCategoryBySlug($slug) {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = ?');
        $stmt->execute(array($slug));
        $category = $stmt->fetch();
        
        return $category ?: null;
    }
}

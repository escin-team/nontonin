<?php
/**
 * Home Controller
 * Displays homepage with cached global feed from multiple providers
 * PHP 5.6 - 8.3 Compatible
 * 
 * Uses url() and redirect() helpers to prevent ByetHost 404 errors
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
     * Display homepage - Fetches feed from multiple DramaBos providers
     * Cache duration: 6 hours (21600 seconds)
     */
    public function index() {
        $this->requireLogin();
        
        // Get list of providers
        $providers = getDramaBosProviders();
        
        // Limit to first 5 providers for performance on free hosting
        $providers = array_slice($providers, 0, 5);
        
        $allShows = array();
        $trendingShows = array();
        
        // Loop through providers and fetch feed data
        foreach ($providers as $provider) {
            // Get feed from each provider (cached for 6 hours)
            $feedData = $this->apiService->getFeed($provider, 21600);
            
            if (!empty($feedData) && isset($feedData['data']) && is_array($feedData['data'])) {
                // Add provider info to each show
                foreach ($feedData['data'] as &$show) {
                    if (is_array($show)) {
                        $show['source_provider'] = $provider;
                        // Generate unique slug using provider and ID
                        if (isset($show['id'])) {
                            $show['slug'] = $provider . '-' . $show['id'];
                        }
                    }
                }
                unset($show); // Break reference
                
                // Merge shows from all providers
                $allShows = array_merge($allShows, $feedData['data']);
            }
            
            // Also get trending data
            $trendingData = $this->apiService->getTrending($provider, 21600);
            if (!empty($trendingData) && isset($trendingData['data']) && is_array($trendingData['data'])) {
                foreach ($trendingData['data'] as &$show) {
                    if (is_array($show)) {
                        $show['source_provider'] = $provider;
                        if (isset($show['id'])) {
                            $show['slug'] = $provider . '-' . $show['id'];
                        }
                    }
                }
                unset($show);
                $trendingShows = array_merge($trendingShows, $trendingData['data']);
            }
        }
        
        // Limit results to avoid overwhelming display
        $trendingShows = array_slice($trendingShows, 0, 12);
        $allShows = array_slice($allShows, 0, 12);
        
        // Get recently added shows from database
        $recentShows = array();
        if (method_exists($this->showModel, 'getAll')) {
            $recentShows = $this->showModel->getAll(null, 12, 0);
        }
        
        $this->view('home/index', array(
            'trending' => $trendingShows,
            'recent' => $recentShows,
            'shows' => $allShows,
            'page_title' => 'Home - ' . APP_NAME,
            'providers' => $providers
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
        $validProviders = getDramaBosProviders();
        if (!in_array($provider, $validProviders)) {
            // Redirect to home using safe redirect helper
            redirect('home');
        }
        
        // Get drama details (cache 6 hours)
        $dramaDetails = $this->apiService->getDramaDetails($provider, $dramaId, 21600);
        
        // Get episodes list (cache 6 hours)
        $episodes = $this->apiService->getEpisodes($provider, $dramaId, 21600);
        
        if (empty($dramaDetails)) {
            // Show error if drama not found
            http_response_code(404);
            $this->view('errors/404', array(
                'page_title' => 'Drama Not Found - ' . APP_NAME
            ));
            return;
        }
        
        // Process episodes data
        $episodesList = array();
        if (!empty($episodes)) {
            if (isset($episodes['data']) && is_array($episodes['data'])) {
                $episodesList = $episodes['data'];
            } elseif (isset($episodes['episodes']) && is_array($episodes['episodes'])) {
                $episodesList = $episodes['episodes'];
            }
        }
        
        // Add provider info to drama details
        $dramaDetails['source_provider'] = $provider;
        $dramaDetails['episodes'] = $episodesList;
        
        // Save to database if model exists
        if (method_exists($this->showModel, 'upsertFromApi')) {
            $category = $this->getCategoryBySlug('drama-china');
            $categoryId = $category ? $category['id'] : 1;
            $localId = $this->showModel->upsertFromApi($categoryId, $dramaDetails);
            $dramaDetails['local_id'] = $localId;
        }
        
        $this->view('home/detail', array(
            'drama' => $dramaDetails,
            'provider' => $provider,
            'drama_id' => $dramaId,
            'episodes' => $episodesList,
            'page_title' => (isset($dramaDetails['title']) ? e($dramaDetails['title']) : 'Detail') . ' - ' . APP_NAME
        ));
    }
    
    /**
     * Get category by slug
     * @param string $slug
     * @return array|null
     */
    private function getCategoryBySlug($slug) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = ?');
            $stmt->execute(array($slug));
            $category = $stmt->fetch();
            return $category ?: null;
        } catch (Exception $e) {
            error_log('Error getting category: ' . $e->getMessage());
            return null;
        }
    }
}

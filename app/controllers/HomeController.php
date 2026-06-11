<?php
/**
 * Home Controller
 * Displays homepage with trending dramas from multiple DramaBos providers
 * PHP 5.6 - 8.3 Compatible
 * 
 * Uses url() and redirect() helpers to prevent ByetHost 404 errors
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/ApiService.php';

class HomeController extends Controller {
    private $apiService;
    
    public function __construct() {
        parent::__construct();
        $this->apiService = new ApiService();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Display homepage - Fetches trending from multiple DramaBos providers
     * Loops through 3-5 providers, combines results, shuffles, takes top 30
     */
    public function index() {
        // Check if user is logged in (optional - can be removed for public access)
        // $this->requireLogin();
        
        // Define providers to fetch from (3-5 providers for performance)
        $providers = array('dramabox', 'shortmax', 'reelshort', 'starshort', 'dramabite');
        
        $allDramas = array();
        
        // Loop through each provider and fetch trending dramas
        foreach ($providers as $provider) {
            try {
                // Get trending dramas from API (cached for 1 hour)
                $trendingData = $this->apiService->getTrending($provider, 3600);
                
                // Check if response has data
                if (!empty($trendingData)) {
                    // Handle different response formats
                    $dramas = array();
                    if (isset($trendingData['data']) && is_array($trendingData['data'])) {
                        $dramas = $trendingData['data'];
                    } elseif (isset($trendingData['list']) && is_array($trendingData['list'])) {
                        $dramas = $trendingData['list'];
                    } elseif (is_array($trendingData)) {
                        $dramas = $trendingData;
                    }
                    
                    // Add provider info to each drama
                    foreach ($dramas as &$drama) {
                        if (is_array($drama)) {
                            $drama['provider'] = $provider;
                            // Ensure ID exists for URL generation
                            if (!isset($drama['id'])) {
                                $drama['id'] = isset($drama['drama_id']) ? $drama['drama_id'] : '';
                            }
                        }
                    }
                    unset($drama); // Break reference
                    
                    // Merge into all dramas array
                    $allDramas = array_merge($allDramas, $dramas);
                }
            } catch (Exception $e) {
                // Log error but continue with other providers
                error_log('HomeController Error fetching from ' . $provider . ': ' . $e->getMessage());
                continue;
            }
        }
        
        // Shuffle and limit to 30 dramas
        if (!empty($allDramas)) {
            shuffle($allDramas);
            $allDramas = array_slice($allDramas, 0, 30);
        }
        
        // Pass data to view
        $this->view('home/index', array(
            'dramas' => $allDramas,
            'page_title' => 'Home - ' . APP_NAME,
            'providers' => $providers
        ));
    }
}

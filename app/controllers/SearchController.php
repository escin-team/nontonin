<?php
/**
 * Search Controller
 * Handles drama search functionality across multiple DramaBos providers
 * PHP 5.6 - 8.3 Compatible
 * 
 * Features:
 * - index(): Display empty search form
 * - results(): Search across dramabox, shortmax, reelshort providers
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/ApiService.php';

class SearchController extends Controller {
    private $apiService;
    
    public function __construct() {
        parent::__construct();
        $this->apiService = new ApiService();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Display search form page
     */
    public function index() {
        $this->view('search/index', array(
            'page_title' => 'Search - ' . APP_NAME
        ));
    }
    
    /**
     * Display search results
     * Searches across 3 providers: dramabox, shortmax, reelshort
     * Combines and deduplicates results
     */
    public function results() {
        // Get keyword from query string
        $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        // Validate keyword is not empty
        if (empty($keyword)) {
            // Redirect back to search form if no keyword
            $this->redirect(url('search'));
            return;
        }
        
        // Define providers to search
        $providers = array('dramabox', 'shortmax', 'reelshort');
        
        $allDramas = array();
        $seenIds = array();
        
        // Loop through each provider and search
        foreach ($providers as $provider) {
            try {
                // Search API (cached for 1 hour)
                $searchData = $this->apiService->searchDrama($keyword, $provider, 3600);
                
                // Check if response has data
                if (!empty($searchData)) {
                    // Handle different response formats
                    $dramas = array();
                    if (isset($searchData['data']) && is_array($searchData['data'])) {
                        $dramas = $searchData['data'];
                    } elseif (isset($searchData['list']) && is_array($searchData['list'])) {
                        $dramas = $searchData['list'];
                    } elseif (is_array($searchData)) {
                        $dramas = $searchData;
                    }
                    
                    // Add provider info and deduplicate
                    foreach ($dramas as &$drama) {
                        if (is_array($drama)) {
                            $dramaId = isset($drama['id']) ? $drama['id'] : '';
                            
                            // Skip if no ID or already seen
                            if (empty($dramaId)) continue;
                            
                            // Create unique key for deduplication
                            $uniqueKey = $provider . '_' . $dramaId;
                            if (isset($seenIds[$uniqueKey])) continue;
                            
                            $seenIds[$uniqueKey] = true;
                            
                            $drama['provider'] = $provider;
                            $drama['search_keyword'] = $keyword;
                        }
                    }
                    unset($drama); // Break reference
                    
                    // Merge into all dramas array
                    $allDramas = array_merge($allDramas, $dramas);
                }
            } catch (Exception $e) {
                // Log error but continue with other providers
                error_log('SearchController Error searching ' . $provider . ': ' . $e->getMessage());
                continue;
            }
        }
        
        // Pass data to view
        $this->view('search/results', array(
            'dramas' => $allDramas,
            'keyword' => $keyword,
            'page_title' => 'Search Results: ' . $keyword . ' - ' . APP_NAME,
            'providers' => $providers
        ));
    }
}


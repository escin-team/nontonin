<?php
/**
 * Home Controller
 * Displays homepage with trending dramas
 * PHP 5.6 Compatible
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ShowModel.php';

class HomeController extends Controller {
    private $showModel;
    
    public function __construct() {
        parent::__construct();
        $this->showModel = new ShowModel();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Display homepage
     */
    public function index() {
        $this->requireLogin();
        
        // Get trending dramas from API
        $trendingData = $this->api->getTrendingDramas();
        $trendingShows = array();
        
        if ($trendingData && isset($trendingData['data'])) {
            // Get China drama category ID
            $chinaCategory = $this->getCategoryBySlug('drama-china');
            $categoryId = $chinaCategory ? $chinaCategory['id'] : 1;
            
            foreach ($trendingData['data'] as $drama) {
                // Save to database and get local ID
                $showId = $this->showModel->upsertFromApi($categoryId, $drama);
                $drama['local_id'] = $showId;
                $trendingShows[] = $drama;
            }
        }
        
        // Get recently added shows from database
        $recentShows = $this->showModel->getAll(null, 12, 0);
        
        $this->view('home/index', array(
            'trending' => $trendingShows,
            'recent' => $recentShows,
            'page_title' => 'Home - ' . APP_NAME
        ));
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

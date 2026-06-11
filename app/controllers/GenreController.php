<?php
/**
 * Genre Controller
 * Handles genre/category browsing functionality
 * PHP 5.6 - 8.3 Compatible
 * 
 * Features:
 * - index(): Display list of hardcoded genres
 * - show($genre_id): Display dramas for a specific genre
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/ApiService.php';

class GenreController extends Controller {
    private $apiService;
    
    // Hardcoded genre list with IDs
    private $genres = array(
        array('id' => 'romance', 'name' => 'Romance', 'icon' => 'fa-heart'),
        array('id' => 'action', 'name' => 'Action', 'icon' => 'fa-fist-raised'),
        array('id' => 'thriller', 'name' => 'Thriller', 'icon' => 'fa-mask'),
        array('id' => 'comedy', 'name' => 'Comedy', 'icon' => 'fa-laugh'),
        array('id' => 'fantasy', 'name' => 'Fantasy', 'icon' => 'fa-dragon'),
        array('id' => 'ceo', 'name' => 'CEO', 'icon' => 'fa-briefcase'),
        array('id' => 'revenge', 'name' => 'Revenge', 'icon' => 'fa-fire')
    );
    
    public function __construct() {
        parent::__construct();
        $this->apiService = new ApiService();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Display genre list page
     */
    public function index() {
        $this->view('genre/index', array(
            'genres' => $this->genres,
            'page_title' => 'Browse by Genre - ' . APP_NAME
        ));
    }
    
    /**
     * Display dramas for a specific genre
     * @param string $genreId Genre ID from URL
     */
    public function show($genreId) {
        // Validate genre ID exists in our list
        $validGenre = false;
        $genreName = '';
        foreach ($this->genres as $genre) {
            if (isset($genre['id']) && $genre['id'] === $genreId) {
                $validGenre = true;
                $genreName = isset($genre['name']) ? $genre['name'] : $genreId;
                break;
            }
        }
        
        if (!$validGenre) {
            // Invalid genre, redirect to genre list
            $this->redirect(url('genre'));
            return;
        }
        
        // Define default provider
        $provider = 'dramabox';
        
        $allDramas = array();
        
        try {
            // Get dramas by genre from API (cached for 6 hours)
            $genreData = $this->apiService->getDramaByGenre($genreId, $provider, 21600);
            
            // Check if response has data
            if (!empty($genreData)) {
                // Handle different response formats
                $dramas = array();
                if (isset($genreData['data']) && is_array($genreData['data'])) {
                    $dramas = $genreData['data'];
                } elseif (isset($genreData['list']) && is_array($genreData['list'])) {
                    $dramas = $genreData['list'];
                } elseif (is_array($genreData)) {
                    $dramas = $genreData;
                }
                
                // Add provider info to each drama
                foreach ($dramas as &$drama) {
                    if (is_array($drama)) {
                        $drama['provider'] = $provider;
                        $drama['genre_id'] = $genreId;
                        $drama['genre_name'] = $genreName;
                        // Ensure ID exists for URL generation
                        if (!isset($drama['id'])) {
                            $drama['id'] = isset($drama['drama_id']) ? $drama['drama_id'] : '';
                        }
                    }
                }
                unset($drama); // Break reference
                
                $allDramas = $dramas;
            }
        } catch (Exception $e) {
            // Log error but continue with empty results
            error_log('GenreController Error fetching genre ' . $genreId . ': ' . $e->getMessage());
        }
        
        // Pass data to view
        $this->view('genre/show', array(
            'dramas' => $allDramas,
            'genre_id' => $genreId,
            'genre_name' => $genreName,
            'page_title' => $genreName . ' Dramas - ' . APP_NAME,
            'provider' => $provider
        ));
    }
}


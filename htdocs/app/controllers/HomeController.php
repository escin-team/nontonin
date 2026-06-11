<?php
/**
 * HomeController - PHP 5.5/5.6 Compatible
 * Handles the home/dashboard page
 */

class HomeController extends Controller {
    
    /**
     * Show home/dashboard page
     */
    public function index() {
        $this->requireLogin();
        
        try {
            // Get trending dramas from API
            $trendingDramas = $this->api->getTrendingDramas();
            
            // Get user's watch history (if any)
            $stmt = $this->db->prepare('
                SELECT d.*, w.last_watched_at, w.episode_number 
                FROM watch_history w 
                JOIN dramas d ON w.drama_id = d.id 
                WHERE w.user_id = ? 
                ORDER BY w.last_watched_at DESC 
                LIMIT 10
            ');
            $stmt->execute(array($_SESSION['user_id']));
            $watchHistory = $stmt->fetchAll();
            
            $this->view('home/index', array(
                'page_title' => 'Home',
                'trending_dramas' => isset($trendingDramas) ? $trendingDramas : array(),
                'watch_history' => $watchHistory,
                'user' => array(
                    'username' => $_SESSION['username'],
                    'email' => $_SESSION['email']
                )
            ));
            
        } catch (Exception $e) {
            // Show home even if API fails
            $this->view('home/index', array(
                'page_title' => 'Home',
                'trending_dramas' => array(),
                'watch_history' => array(),
                'user' => array(
                    'username' => $_SESSION['username'],
                    'email' => $_SESSION['email']
                ),
                'error_message' => 'Unable to load trending dramas. Please try again later.'
            ));
        }
    }
}

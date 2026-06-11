<?php
/**
 * Drama Controller
 * Handles drama details and streaming from DramaBos API
 * PHP 5.6 - 8.3 Compatible
 * 
 * Uses url() and redirect() helpers to prevent ByetHost 404 errors
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ShowModel.php';
require_once __DIR__ . '/../models/EpisodeModel.php';
require_once __DIR__ . '/../core/ApiService.php';

class DramaController extends Controller {
    private $showModel;
    private $episodeModel;
    private $apiService;
    
    public function __construct() {
        parent::__construct();
        $this->showModel = new ShowModel();
        $this->episodeModel = new EpisodeModel();
        $this->apiService = new ApiService();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Display drama detail page with episodes
     * Route: /drama/{provider}/{drama_id}
     * @param string $provider Provider slug (dramabox, shortmax, etc.)
     * @param string $dramaId Drama ID from API
     */
    public function detail($provider, $dramaId) {
        $this->requireLogin();
        
        // Validate provider
        $validProviders = getDramaBosProviders();
        if (!in_array($provider, $validProviders)) {
            redirect('home');
        }
        
        // Get drama details from API (cache 6 hours)
        $dramaDetails = $this->apiService->getDramaDetails($provider, $dramaId, 21600);
        
        // Get episodes list from API (cache 6 hours)
        $episodesData = $this->apiService->getEpisodes($provider, $dramaId, 21600);
        
        if (empty($dramaDetails)) {
            http_response_code(404);
            echo '<div style="text-align:center;padding:50px;">';
            echo '<h1>Drama Not Found</h1>';
            echo '<p>The drama you are looking for does not exist.</p>';
            echo '<a href="' . url('home') . '">Go Home</a>';
            echo '</div>';
            return;
        }
        
        // Process episodes data
        $episodes = array();
        if (!empty($episodesData)) {
            if (isset($episodesData['data']) && is_array($episodesData['data'])) {
                $episodes = $episodesData['data'];
            } elseif (isset($episodesData['episodes']) && is_array($episodesData['episodes'])) {
                $episodes = $episodesData['episodes'];
            }
        }
        
        // Add provider info to drama details
        $dramaDetails['source_provider'] = $provider;
        $dramaDetails['drama_id'] = $dramaId;
        
        // Save to database if model method exists
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
            'episodes' => $episodes,
            'page_title' => (isset($dramaDetails['title']) ? e($dramaDetails['title']) : 'Detail') . ' - ' . APP_NAME
        ));
    }
    
    /**
     * Display video player page
     * Route: /watch/{provider}/{episode_id}
     * @param string $provider Provider slug
     * @param string $episodeId Episode ID from API
     */
    public function watch($provider, $episodeId) {
        $this->requireLogin();
        
        // Validate provider
        $validProviders = getDramaBosProviders();
        if (!in_array($provider, $validProviders)) {
            redirect('home');
        }
        
        // Get streaming URL from API (short cache for streams - 15 minutes)
        $streamData = $this->apiService->getStreamUrl($provider, $episodeId, 900);
        
        $streamUrl = '';
        $streamType = 'hls'; // Default to HLS for .m3u8
        
        if (!empty($streamData)) {
            // Extract m3u8 URL from various response formats
            if (isset($streamData['url'])) {
                $streamUrl = $streamData['url'];
            } elseif (isset($streamData['play_url'])) {
                $streamUrl = $streamData['play_url'];
            } elseif (isset($streamData['stream_url'])) {
                $streamUrl = $streamData['stream_url'];
            } elseif (isset($streamData['data']['url'])) {
                $streamUrl = $streamData['data']['url'];
            } elseif (isset($streamData['data']['play_url'])) {
                $streamUrl = $streamData['data']['play_url'];
            }
            
            // Determine stream type based on URL
            if (!empty($streamUrl) && strpos($streamUrl, '.m3u8') !== false) {
                $streamType = 'hls';
            } elseif (!empty($streamUrl) && strpos($streamUrl, '.mp4') !== false) {
                $streamType = 'mp4';
            }
        }
        
        // Prepare episode info for display
        $currentEpisode = array(
            'episode_number' => isset($episodeId) ? $episodeId : '1',
            'title' => '',
            'synopsis' => ''
        );
        
        // Get show info (if available from cache or previous request)
        $show = array(
            'slug' => $provider . '-' . $episodeId,
            'title' => 'Watching Episode',
            'id' => 0
        );
        
        // Navigation placeholders (can be enhanced with actual episode list)
        $nextEpisode = null;
        $prevEpisode = null;
        $allEpisodes = array();
        
        $this->view('player/watch', array(
            'show' => $show,
            'streamUrl' => $streamUrl,
            'streamType' => $streamType,
            'episodeId' => $episodeId,
            'provider' => $provider,
            'currentEpisode' => $currentEpisode,
            'nextEpisode' => $nextEpisode,
            'prevEpisode' => $prevEpisode,
            'allEpisodes' => $allEpisodes,
            'page_title' => 'Watch Episode ' . e($episodeId) . ' - ' . APP_NAME
        ));
    }
    
    /**
     * Update watch progress (AJAX endpoint)
     */
    public function updateProgress() {
        if (!isset($_SESSION['user_id'])) {
            $this->json(array('success' => false, 'message' => 'Not logged in'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(array('success' => false, 'message' => 'Invalid method'));
        }
        
        $showId = isset($_POST['show_id']) ? intval($_POST['show_id']) : 0;
        $episodeId = isset($_POST['episode_id']) ? $_POST['episode_id'] : '';
        $progress = isset($_POST['progress']) ? intval($_POST['progress']) : 0;
        
        if (!$showId || !$episodeId) {
            $this->json(array('success' => false, 'message' => 'Invalid parameters'));
        }
        
        if (method_exists($this->episodeModel, 'saveWatchProgress')) {
            $result = $this->episodeModel->saveWatchProgress(
                $_SESSION['user_id'],
                $showId,
                $episodeId,
                $progress
            );
            $this->json(array('success' => $result));
        } else {
            $this->json(array('success' => true, 'message' => 'Progress saved (mock)'));
        }
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

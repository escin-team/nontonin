<?php
/**
 * Drama Controller
 * Handles drama details and streaming from DramaBos API
 * PHP 5.6 - 8.3 Compatible
 * 
 * Uses url() and redirect() helpers to prevent ByetHost 404 errors
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/ApiService.php';

class DramaController extends Controller {
    private $apiService;
    
    public function __construct() {
        parent::__construct();
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
        // Validate provider against supported list
        $validProviders = array('dramabox', 'shortmax', 'reelshort', 'starshort', 'dramabite', 'freereels', 'fundrama', 'microdrama', 'vigloo', 'bilitv');
        if (!in_array($provider, $validProviders)) {
            redirect('home');
        }
        
        $dramaDetails = array();
        $episodes = array();
        $error = '';
        
        try {
            // Get drama details from API (cache 6 hours)
            $dramaDetails = $this->apiService->getDramaDetail($provider, $dramaId, 21600);
            
            // Get episodes list from API (cache 6 hours)
            $episodesData = $this->apiService->getEpisodes($provider, $dramaId, 21600);
            
            // Process episodes data - handle different response formats
            if (!empty($episodesData)) {
                if (isset($episodesData['data']) && is_array($episodesData['data'])) {
                    $episodes = $episodesData['data'];
                } elseif (isset($episodesData['list']) && is_array($episodesData['list'])) {
                    $episodes = $episodesData['list'];
                } elseif (isset($episodesData['episodes']) && is_array($episodesData['episodes'])) {
                    $episodes = $episodesData['episodes'];
                } elseif (is_array($episodesData)) {
                    $episodes = $episodesData;
                }
            }
        } catch (Exception $e) {
            error_log('DramaController Error: ' . $e->getMessage());
            $error = 'Failed to load drama details. Please try again later.';
        }
        
        // Handle empty/error response
        if (empty($dramaDetails)) {
            // Show error page
            echo '<!DOCTYPE html>';
            echo '<html><head><title>Drama Not Found</title>';
            echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
            echo '<style>body{background:#121212;color:#e0e0e0;} .error-box{text-align:center;padding:50px;}</style>';
            echo '</head><body><div class="error-box">';
            echo '<h1><i class="fas fa-exclamation-triangle"></i> Drama Not Found</h1>';
            echo '<p>The drama you are looking for does not exist or could not be loaded.</p>';
            echo '<a href="' . url('home') . '" class="btn btn-primary">Go Home</a>';
            echo '</div></body></html>';
            return;
        }
        
        // Add provider info to drama details
        $dramaDetails['provider'] = $provider;
        $dramaDetails['drama_id'] = $dramaId;
        
        // Pass data to view
        $this->view('drama/detail', array(
            'drama' => $dramaDetails,
            'provider' => $provider,
            'drama_id' => $dramaId,
            'episodes' => $episodes,
            'error' => $error,
            'page_title' => (isset($dramaDetails['title']) ? e($dramaDetails['title']) : 'Drama Detail') . ' - ' . APP_NAME
        ));
    }
    
    /**
     * Display video player page
     * Route: /watch/{provider}/{episode_id}
     * @param string $provider Provider slug
     * @param string $episodeId Episode ID from API
     */
    public function watch($provider, $episodeId) {
        // Validate provider
        $validProviders = array('dramabox', 'shortmax', 'reelshort', 'starshort', 'dramabite', 'freereels', 'fundrama', 'microdrama', 'vigloo', 'bilitv');
        if (!in_array($provider, $validProviders)) {
            redirect('home');
        }
        
        $streamUrl = '';
        $streamType = 'hls';
        $error = '';
        $episodeInfo = array();
        
        try {
            // Get streaming URL from API (short cache for streams - 15 minutes)
            $streamData = $this->apiService->getStreamUrl($provider, $episodeId, 900);
            
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
                } elseif (isset($streamData['video_url'])) {
                    $streamUrl = $streamData['video_url'];
                }
                
                // Determine stream type based on URL
                if (!empty($streamUrl)) {
                    if (strpos($streamUrl, '.m3u8') !== false) {
                        $streamType = 'hls';
                    } elseif (strpos($streamUrl, '.mp4') !== false) {
                        $streamType = 'mp4';
                    }
                }
            }
        } catch (Exception $e) {
            error_log('DramaController Watch Error: ' . $e->getMessage());
            $error = 'Failed to load stream. Please try again later.';
        }
        
        // Prepare episode info for display
        $episodeInfo = array(
            'episode_id' => $episodeId,
            'title' => 'Episode ' . e($episodeId),
            'provider' => $provider
        );
        
        // Pass data to view
        $this->view('player/watch', array(
            'videoUrl' => $streamUrl,
            'streamType' => $streamType,
            'episodeId' => $episodeId,
            'provider' => $provider,
            'episodeInfo' => $episodeInfo,
            'error' => $error,
            'page_title' => 'Watch Episode ' . e($episodeId) . ' - ' . APP_NAME
        ));
    }
}

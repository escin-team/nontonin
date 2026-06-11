<?php
/**
 * Drama Controller
 * Handles drama details and streaming
 * PHP 5.6 Compatible
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ShowModel.php';
require_once __DIR__ . '/../models/EpisodeModel.php';

class DramaController extends Controller {
    private $showModel;
    private $episodeModel;
    
    public function __construct() {
        parent::__construct();
        $this->showModel = new ShowModel();
        $this->episodeModel = new EpisodeModel();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Display drama detail page
     * @param string $slug Drama slug
     */
    public function detail($slug) {
        $this->requireLogin();
        
        // Get show from database
        $show = $this->showModel->findBySlug($slug);
        
        if (!$show) {
            // Show not found in DB, try to fetch from API
            http_response_code(404);
            echo '<div style="text-align:center;padding:50px;">';
            echo '<h1>Drama Not Found</h1>';
            echo '<p>The drama you are looking for does not exist.</p>';
            echo '<a href="' . BASE_URL . '/home">Go Home</a>';
            echo '</div>';
            return;
        }
        
        // Get episodes from API (cached for 6 hours)
        $episodesData = $this->api->getDramaDetails($show['api_show_id']);
        $episodes = array();
        
        if ($episodesData && isset($episodesData['episodes'])) {
            $episodes = $episodesData['episodes'];
            
            // Save episodes to database
            foreach ($episodes as $episode) {
                $this->episodeModel->upsertFromApi($show['id'], $episode);
            }
        }
        
        // Get saved episodes from database as fallback
        if (empty($episodes)) {
            $episodes = $this->episodeModel->getByShowId($show['id']);
        }
        
        $this->view('home/detail', array(
            'show' => $show,
            'episodes' => $episodes,
            'page_title' => htmlspecialchars($show['title']) . ' - ' . APP_NAME
        ));
    }
    
    /**
     * Display video player page
     * @param string $slug Drama slug
     * @param string $episodeId Episode ID
     */
    public function watch($slug, $episodeId) {
        $this->requireLogin();
        
        // Get show from database
        $show = $this->showModel->findBySlug($slug);
        
        if (!$show) {
            http_response_code(404);
            echo '<div style="text-align:center;padding:50px;">';
            echo '<h1>Drama Not Found</h1>';
            echo '<p>The drama you are looking for does not exist.</p>';
            echo '<a href="' . BASE_URL . '/home">Go Home</a>';
            echo '</div>';
            return;
        }
        
        // Get streaming URL from API (real-time, no cache)
        $streamData = $this->api->getEpisodeStream($show['api_show_id'], $episodeId);
        $streamUrl = '';
        $streamType = 'mp4';
        
        if ($streamData) {
            if (isset($streamData['url'])) {
                $streamUrl = $streamData['url'];
            } elseif (isset($streamData['sources']) && is_array($streamData['sources'])) {
                // Try to get HLS stream first, then fallback to MP4
                foreach ($streamData['sources'] as $source) {
                    if (isset($source['type']) && $source['type'] === 'hls') {
                        $streamUrl = $source['url'];
                        $streamType = 'hls';
                        break;
                    } elseif (isset($source['file'])) {
                        $streamUrl = $source['file'];
                        if (strpos($streamUrl, '.m3u8') !== false) {
                            $streamType = 'hls';
                        }
                    }
                }
            }
        }
        
        // Get all episodes for navigation
        $allEpisodes = $this->episodeModel->getByShowId($show['id']);
        $currentEpisodeIndex = -1;
        $nextEpisode = null;
        $prevEpisode = null;
        
        foreach ($allEpisodes as $index => $ep) {
            if ($ep['api_episode_id'] == $episodeId || $ep['episode_number'] == $episodeId) {
                $currentEpisodeIndex = $index;
                break;
            }
        }
        
        if ($currentEpisodeIndex >= 0) {
            if ($currentEpisodeIndex > 0) {
                $prevEpisode = $allEpisodes[$currentEpisodeIndex - 1];
            }
            if ($currentEpisodeIndex < count($allEpisodes) - 1) {
                $nextEpisode = $allEpisodes[$currentEpisodeIndex + 1];
            }
        }
        
        // Get current episode info
        $currentEpisode = $this->episodeModel->findByApiId($episodeId);
        
        // Save watch progress
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        if ($userId) {
            $this->episodeModel->saveWatchProgress($userId, $show['id'], $episodeId, 0);
        }
        
        $this->view('player/watch', array(
            'show' => $show,
            'streamUrl' => $streamUrl,
            'streamType' => $streamType,
            'episodeId' => $episodeId,
            'currentEpisode' => $currentEpisode,
            'nextEpisode' => $nextEpisode,
            'prevEpisode' => $prevEpisode,
            'allEpisodes' => $allEpisodes,
            'page_title' => 'Watch ' . htmlspecialchars($show['title']) . ' Episode ' . $episodeId . ' - ' . APP_NAME
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
        
        $result = $this->episodeModel->saveWatchProgress(
            $_SESSION['user_id'],
            $showId,
            $episodeId,
            $progress
        );
        
        $this->json(array('success' => $result));
    }
}

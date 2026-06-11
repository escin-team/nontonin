<?php
/**
 * DramaController - PHP 5.5/5.6 Compatible
 * Handles drama details, watching, and progress tracking
 */

class DramaController extends Controller {
    
    /**
     * Show drama detail page
     * @param string $slug Drama slug
     */
    public function detail($slug) {
        $this->requireLogin();
        
        try {
            // Get drama details from API
            $dramaDetails = $this->api->getDramaDetails($slug);
            
            if (empty($dramaDetails)) {
                // Try to get from database if API fails
                $stmt = $this->db->prepare('SELECT * FROM dramas WHERE slug = ? LIMIT 1');
                $stmt->execute(array($slug));
                $dramaDetails = $stmt->fetch();
            }
            
            if (empty($dramaDetails)) {
                throw new Exception('Drama not found');
            }
            
            $this->view('drama/detail', array(
                'page_title' => isset($dramaDetails['title']) ? $dramaDetails['title'] : 'Drama Details',
                'drama' => $dramaDetails,
                'csrf_token' => $this->generateCsrfToken()
            ));
            
        } catch (Exception $e) {
            $this->view('errors/404', array(
                'page_title' => 'Drama Not Found',
                'message' => 'The drama you are looking for does not exist.'
            ));
        }
    }
    
    /**
     * Show watch page for specific episode
     * @param string $slug Drama slug
     * @param string $episodeId Episode ID
     */
    public function watch($slug, $episodeId) {
        $this->requireLogin();
        
        try {
            // Get drama details
            $dramaDetails = $this->api->getDramaDetails($slug);
            
            if (empty($dramaDetails)) {
                $stmt = $this->db->prepare('SELECT * FROM dramas WHERE slug = ? LIMIT 1');
                $stmt->execute(array($slug));
                $dramaDetails = $stmt->fetch();
            }
            
            if (empty($dramaDetails)) {
                throw new Exception('Drama not found');
            }
            
            // Get streaming URL from API
            $streamData = $this->api->getEpisodeStream($slug, $episodeId);
            
            // Update watch history
            $this->updateWatchHistory($dramaDetails, $episodeId);
            
            $this->view('drama/watch', array(
                'page_title' => 'Watch - ' . isset($dramaDetails['title']) ? $dramaDetails['title'] : '',
                'drama' => $dramaDetails,
                'episode_id' => $episodeId,
                'stream_url' => isset($streamData['url']) ? $streamData['url'] : '',
                'stream_sources' => isset($streamData['sources']) ? $streamData['sources'] : array(),
                'csrf_token' => $this->generateCsrfToken()
            ));
            
        } catch (Exception $e) {
            $this->view('errors/404', array(
                'page_title' => 'Error',
                'message' => 'Unable to load video. Please try again later.'
            ));
        }
    }
    
    /**
     * Update watch progress (AJAX endpoint)
     */
    public function updateProgress() {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $this->json(array('success' => false, 'message' => 'Invalid security token'));
            return;
        }
        
        $dramaId = isset($_POST['drama_id']) ? intval($_POST['drama_id']) : 0;
        $episodeId = isset($_POST['episode_id']) ? trim($_POST['episode_id']) : '';
        $episodeNumber = isset($_POST['episode_number']) ? intval($_POST['episode_number']) : 0;
        $watchedDuration = isset($_POST['watched_duration']) ? intval($_POST['watched_duration']) : 0;
        
        if (empty($dramaId) || empty($episodeId)) {
            $this->json(array('success' => false, 'message' => 'Invalid parameters'));
            return;
        }
        
        try {
            // Insert or update watch history
            $stmt = $this->db->prepare('
                INSERT INTO watch_history (user_id, drama_id, episode_id, episode_number, last_watched_at, watched_duration)
                VALUES (?, ?, ?, ?, NOW(), ?)
                ON DUPLICATE KEY UPDATE 
                    episode_number = VALUES(episode_number),
                    last_watched_at = VALUES(last_watched_at),
                    watched_duration = VALUES(watched_duration)
            ');
            $stmt->execute(array(
                $_SESSION['user_id'],
                $dramaId,
                $episodeId,
                $episodeNumber,
                $watchedDuration
            ));
            
            $this->json(array('success' => true, 'message' => 'Progress updated'));
            
        } catch (PDOException $e) {
            $this->json(array('success' => false, 'message' => 'Failed to update progress'));
        }
    }
    
    /**
     * Update watch history in database
     * @param array $drama Drama data
     * @param string $episodeId Episode ID
     */
    private function updateWatchHistory($drama, $episodeId) {
        try {
            $dramaId = isset($drama['id']) ? $drama['id'] : 0;
            $episodeNumber = isset($drama['episode_number']) ? $drama['episode_number'] : 1;
            
            $stmt = $this->db->prepare('
                INSERT INTO watch_history (user_id, drama_id, episode_id, episode_number, last_watched_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    episode_number = VALUES(episode_number),
                    last_watched_at = VALUES(last_watched_at)
            ');
            $stmt->execute(array($_SESSION['user_id'], $dramaId, $episodeId, $episodeNumber));
            
        } catch (PDOException $e) {
            // Silently fail - don't break the viewing experience
        }
    }
}

<?php
/**
 * Episode Model
 * Handles episode data and watch progress
 * PHP 5.6 Compatible
 */

class EpisodeModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find episode by API ID
     * @param string $apiEpisodeId
     * @return array|null
     */
    public function findByApiId($apiEpisodeId) {
        $stmt = $this->db->prepare('SELECT * FROM episodes WHERE api_episode_id = ?');
        $stmt->execute(array($apiEpisodeId));
        $episode = $stmt->fetch();
        
        return $episode ?: null;
    }
    
    /**
     * Get all episodes for a show
     * @param int $showId
     * @return array
     */
    public function getByShowId($showId) {
        $stmt = $this->db->prepare(
            'SELECT * FROM episodes WHERE show_id = ? ORDER BY episode_number ASC'
        );
        $stmt->execute(array($showId));
        
        return $stmt->fetchAll();
    }
    
    /**
     * Create or update episode from API data
     * @param int $showId
     * @param array $apiData Data from API
     * @return int Episode ID
     */
    public function upsertFromApi($showId, $apiData) {
        $existing = $this->findByApiId($apiData['id']);
        
        if ($existing) {
            // Update existing episode
            $stmt = $this->db->prepare(
                'UPDATE episodes SET 
                    title = ?, 
                    synopsis = ?, 
                    thumbnail_url = ?, 
                    duration = ?, 
                    air_date = ? 
                 WHERE id = ?'
            );
            
            $stmt->execute(array(
                isset($apiData['title']) ? $apiData['title'] : 'Episode ' . $apiData['number'],
                isset($apiData['synopsis']) ? $apiData['synopsis'] : null,
                isset($apiData['thumbnail']) ? $apiData['thumbnail'] : null,
                isset($apiData['duration']) ? $apiData['duration'] : null,
                isset($apiData['air_date']) ? $apiData['air_date'] : null,
                $existing['id']
            ));
            
            return $existing['id'];
        } else {
            // Create new episode
            $stmt = $this->db->prepare(
                'INSERT INTO episodes (show_id, episode_number, api_episode_id, title, synopsis, thumbnail_url, duration, air_date) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            
            $stmt->execute(array(
                $showId,
                $apiData['number'],
                $apiData['id'],
                isset($apiData['title']) ? $apiData['title'] : 'Episode ' . $apiData['number'],
                isset($apiData['synopsis']) ? $apiData['synopsis'] : null,
                isset($apiData['thumbnail']) ? $apiData['thumbnail'] : null,
                isset($apiData['duration']) ? $apiData['duration'] : null,
                isset($apiData['air_date']) ? $apiData['air_date'] : null
            ));
            
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * Save watch progress for user
     * @param int $userId
     * @param int $showId
     * @param string $episodeId
     * @param int $progress Progress in seconds
     * @return bool
     */
    public function saveWatchProgress($userId, $showId, $episodeId, $progress) {
        // Check if record exists
        $stmt = $this->db->prepare(
            'SELECT id FROM watch_history WHERE user_id = ? AND show_id = ? AND episode_id = ?'
        );
        $stmt->execute(array($userId, $showId, $episodeId));
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing record
            $updateStmt = $this->db->prepare(
                'UPDATE watch_history SET watch_progress = ?, last_watched = NOW() WHERE id = ?'
            );
            return $updateStmt->execute(array($progress, $existing['id']));
        } else {
            // Insert new record
            $insertStmt = $this->db->prepare(
                'INSERT INTO watch_history (user_id, show_id, episode_id, watch_progress) VALUES (?, ?, ?, ?)'
            );
            return $insertStmt->execute(array($userId, $showId, $episodeId, $progress));
        }
    }
    
    /**
     * Get watch progress for user
     * @param int $userId
     * @param int $showId
     * @param string $episodeId
     * @return array|null
     */
    public function getWatchProgress($userId, $showId, $episodeId) {
        $stmt = $this->db->prepare(
            'SELECT watch_progress, completed FROM watch_history WHERE user_id = ? AND show_id = ? AND episode_id = ?'
        );
        $stmt->execute(array($userId, $showId, $episodeId));
        $record = $stmt->fetch();
        
        return $record ?: null;
    }
    
    /**
     * Mark episode as completed
     * @param int $userId
     * @param int $showId
     * @param string $episodeId
     * @return bool
     */
    public function markAsCompleted($userId, $showId, $episodeId) {
        $stmt = $this->db->prepare(
            'UPDATE watch_history SET completed = TRUE, last_watched = NOW() WHERE user_id = ? AND show_id = ? AND episode_id = ?'
        );
        return $stmt->execute(array($userId, $showId, $episodeId));
    }
}

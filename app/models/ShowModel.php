<?php
/**
 * Show Model
 * PHP 5.6 Compatible
 */

class ShowModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find show by API ID
     * @param string $apiShowId
     * @return array|null
     */
    public function findByApiId($apiShowId) {
        $stmt = $this->db->prepare('SELECT * FROM shows WHERE api_show_id = ?');
        $stmt->execute(array($apiShowId));
        $show = $stmt->fetch();
        
        return $show ?: null;
    }
    
    /**
     * Find show by slug
     * @param string $slug
     * @return array|null
     */
    public function findBySlug($slug) {
        $stmt = $this->db->prepare('SELECT s.*, c.name as category_name, c.slug as category_slug 
                                    FROM shows s 
                                    JOIN categories c ON s.category_id = c.id 
                                    WHERE s.slug = ?');
        $stmt->execute(array($slug));
        $show = $stmt->fetch();
        
        return $show ?: null;
    }
    
    /**
     * Get all shows with pagination
     * @param int $categoryId Filter by category (optional)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAll($categoryId = null, $limit = 20, $offset = 0) {
        if ($categoryId) {
            $stmt = $this->db->prepare(
                'SELECT s.*, c.name as category_name FROM shows s 
                 JOIN categories c ON s.category_id = c.id 
                 WHERE s.category_id = ? 
                 ORDER BY s.created_at DESC 
                 LIMIT ? OFFSET ?'
            );
            $stmt->execute(array($categoryId, $limit, $offset));
        } else {
            $stmt = $this->db->prepare(
                'SELECT s.*, c.name as category_name FROM shows s 
                 JOIN categories c ON s.category_id = c.id 
                 ORDER BY s.created_at DESC 
                 LIMIT ? OFFSET ?'
            );
            $stmt->execute(array($limit, $offset));
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Create or update show from API data
     * @param int $categoryId
     * @param array $apiData Data from API
     * @return int Show ID
     */
    public function upsertFromApi($categoryId, $apiData) {
        $existing = $this->findByApiId($apiData['id']);
        
        if ($existing) {
            // Update existing show
            $stmt = $this->db->prepare(
                'UPDATE shows SET 
                    title = ?, 
                    slug = ?, 
                    synopsis = ?, 
                    poster_url = ?, 
                    release_year = ?, 
                    status = ? 
                 WHERE id = ?'
            );
            
            $stmt->execute(array(
                $apiData['title'],
                $this->generateSlug($apiData['title']),
                isset($apiData['synopsis']) ? $apiData['synopsis'] : null,
                isset($apiData['poster']) ? $apiData['poster'] : null,
                isset($apiData['year']) ? $apiData['year'] : null,
                isset($apiData['status']) ? $apiData['status'] : 'ongoing',
                $existing['id']
            ));
            
            return $existing['id'];
        } else {
            // Create new show
            $stmt = $this->db->prepare(
                'INSERT INTO shows (category_id, api_show_id, title, slug, synopsis, poster_url, release_year, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            
            $stmt->execute(array(
                $categoryId,
                $apiData['id'],
                $apiData['title'],
                $this->generateSlug($apiData['title']),
                isset($apiData['synopsis']) ? $apiData['synopsis'] : null,
                isset($apiData['poster']) ? $apiData['poster'] : null,
                isset($apiData['year']) ? $apiData['year'] : null,
                isset($apiData['status']) ? $apiData['status'] : 'ongoing'
            ));
            
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * Generate URL-friendly slug
     * @param string $title
     * @return string
     */
    private function generateSlug($title) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        
        // Check if slug exists and add unique suffix if needed
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->findBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}

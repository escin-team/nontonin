<div class="content">
    <div style="margin-bottom: 20px;">
        <a href="<?php echo BASE_URL; ?>/home" style="color: #e74c3c; text-decoration: none;">&larr; Back to Home</a>
    </div>
    
    <h2 style="margin-bottom: 20px;"><?php echo htmlspecialchars(isset($drama['title']) ? $drama['title'] : 'Drama Details'); ?></h2>
    
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <div style="flex: 0 0 250px;">
            <div style="height: 350px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                <span style="color: #999;">No Image</span>
            </div>
        </div>
        
        <div style="flex: 1; min-width: 300px;">
            <p style="margin-bottom: 15px; line-height: 1.6;">
                <?php echo htmlspecialchars(isset($drama['description']) ? $drama['description'] : 'No description available.'); ?>
            </p>
            
            <p><strong>Year:</strong> <?php echo htmlspecialchars(isset($drama['year']) ? $drama['year'] : 'N/A'); ?></p>
            <p><strong>Country:</strong> <?php echo htmlspecialchars(isset($drama['country']) ? $drama['country'] : 'N/A'); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars(isset($drama['status']) ? $drama['status'] : 'N/A'); ?></p>
            
            <?php if (isset($drama['episodes']) && !empty($drama['episodes'])): ?>
                <h3 style="margin-top: 30px; margin-bottom: 15px;">Episodes</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php foreach ($drama['episodes'] as $episode): ?>
                        <a href="<?php echo BASE_URL; ?>/watch/<?php echo urlencode(isset($drama['slug']) ? $drama['slug'] : ''); ?>/<?php echo urlencode(isset($episode['id']) ? $episode['id'] : ''); ?>" 
                           class="btn" 
                           style="font-size: 12px; padding: 8px 15px;">
                            Episode <?php echo htmlspecialchars(isset($episode['number']) ? $episode['number'] : '?'); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="content">
    <h2 style="margin-bottom: 20px;">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($watch_history)): ?>
        <h3 style="margin-bottom: 15px;">Continue Watching</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <?php foreach ($watch_history as $drama): ?>
                <div style="border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                    <div style="height: 280px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #999;">No Image</span>
                    </div>
                    <div style="padding: 15px;">
                        <h4 style="font-size: 14px; margin-bottom: 5px;"><?php echo htmlspecialchars(isset($drama['title']) ? $drama['title'] : 'Unknown'); ?></h4>
                        <p style="font-size: 12px; color: #666;">Episode <?php echo htmlspecialchars(isset($drama['episode_number']) ? $drama['episode_number'] : '?'); ?></p>
                        <a href="<?php echo BASE_URL; ?>/watch/<?php echo urlencode(isset($drama['slug']) ? $drama['slug'] : ''); ?>/<?php echo urlencode(isset($drama['episode_id']) ? $drama['episode_id'] : ''); ?>" class="btn" style="display: block; text-align: center; margin-top: 10px; font-size: 12px;">Continue</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <h3 style="margin-bottom: 15px;">Trending Dramas</h3>
    <?php if (!empty($trending_dramas)): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
            <?php foreach ($trending_dramas as $drama): ?>
                <div style="border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                    <div style="height: 280px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #999;">No Image</span>
                    </div>
                    <div style="padding: 15px;">
                        <h4 style="font-size: 14px; margin-bottom: 5px;"><?php echo htmlspecialchars(isset($drama['title']) ? $drama['title'] : 'Unknown'); ?></h4>
                        <p style="font-size: 12px; color: #666;"><?php echo htmlspecialchars(isset($drama['year']) ? $drama['year'] : ''); ?></p>
                        <a href="<?php echo BASE_URL; ?>/drama/<?php echo urlencode(isset($drama['slug']) ? $drama['slug'] : ''); ?>" class="btn" style="display: block; text-align: center; margin-top: 10px; font-size: 12px;">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: #666;">No trending dramas available at the moment.</p>
    <?php endif; ?>
</div>

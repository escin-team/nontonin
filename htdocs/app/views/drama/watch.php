<div class="content">
    <div style="margin-bottom: 20px;">
        <a href="<?php echo BASE_URL; ?>/drama/<?php echo urlencode(isset($drama['slug']) ? $drama['slug'] : ''); ?>" style="color: #e74c3c; text-decoration: none;">&larr; Back to Drama Details</a>
    </div>
    
    <h2 style="margin-bottom: 10px;"><?php echo htmlspecialchars(isset($drama['title']) ? $drama['title'] : 'Watch'); ?></h2>
    <p style="margin-bottom: 20px; color: #666;">Episode <?php echo htmlspecialchars(isset($episode_id) ? $episode_id : ''); ?></p>
    
    <div style="background: #000; height: 400px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; border-radius: 5px;">
        <?php if (!empty($stream_url)): ?>
            <video controls style="width: 100%; height: 100%;" id="videoPlayer">
                <source src="<?php echo htmlspecialchars($stream_url); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        <?php else: ?>
            <span style="color: #fff;">Video player loading...</span>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($stream_sources)): ?>
        <h3 style="margin-bottom: 15px;">Other Sources</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 30px;">
            <?php foreach ($stream_sources as $source): ?>
                <a href="<?php echo htmlspecialchars(isset($source['url']) ? $source['url'] : '#'); ?>" 
                   class="btn" 
                   target="_blank"
                   style="font-size: 12px; padding: 8px 15px;">
                    <?php echo htmlspecialchars(isset($source['name']) ? $source['name'] : 'Source'); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <input type="hidden" id="dramaId" value="<?php echo htmlspecialchars(isset($drama['id']) ? $drama['id'] : ''); ?>">
    <input type="hidden" id="episodeId" value="<?php echo htmlspecialchars(isset($episode_id) ? $episode_id : ''); ?>">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    
    <script type="text/javascript">
        // Simple progress tracking (PHP 5.6 compatible - no arrow functions)
        var videoPlayer = document.getElementById('videoPlayer');
        if (videoPlayer) {
            videoPlayer.addEventListener('timeupdate', function() {
                var currentTime = Math.floor(videoPlayer.currentTime);
                var dramaId = document.getElementById('dramaId').value;
                var episodeId = document.getElementById('episodeId').value;
                var csrfToken = document.querySelector('input[name="csrf_token"]').value;
                
                // Send progress update every 30 seconds
                if (currentTime % 30 === 0 && currentTime > 0) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo BASE_URL; ?>/drama/update-progress', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send('drama_id=' + encodeURIComponent(dramaId) + 
                             '&episode_id=' + encodeURIComponent(episodeId) + 
                             '&episode_number=1' + 
                             '&watched_duration=' + encodeURIComponent(currentTime) + 
                             '&csrf_token=' + encodeURIComponent(csrfToken));
                }
            });
        }
    </script>
</div>

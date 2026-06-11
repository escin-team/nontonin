<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : APP_NAME; ?></title>
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- DPlayer CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dplayer/dist/DPlayer.min.css">
    
    <style>
        body {
            background-color: #0d0d0d;
            color: #eee;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: #1a1a1a !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #e94560 !important;
        }
        
        .player-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        #dplayer {
            width: 100%;
            height: 500px;
            background-color: #000;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(233, 69, 96, 0.3);
        }
        
        @media (max-width: 768px) {
            #dplayer {
                height: 250px;
            }
        }
        
        .episode-info {
            background-color: #1a1a1a;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .episode-list {
            background-color: #1a1a1a;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .episode-btn {
            margin: 5px;
            border-radius: 5px;
        }
        
        .episode-btn.active {
            background-color: #e94560;
            border-color: #e94560;
            color: #fff;
        }
        
        .btn-back {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
            border-color: #545b62;
            color: #fff;
        }
        
        .btn-next {
            background-color: #e94560;
            border-color: #e94560;
            color: #fff;
        }
        
        .btn-next:hover {
            background-color: #c73e54;
            border-color: #c73e54;
            color: #fff;
        }
        
        .navigation-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        footer {
            background-color: #1a1a1a;
            padding: 20px 0;
            margin-top: 40px;
            border-top: 1px solid #333;
        }
        
        /* Custom scrollbar */
        .episode-list::-webkit-scrollbar {
            width: 8px;
        }
        
        .episode-list::-webkit-scrollbar-track {
            background: #2a2a2a;
            border-radius: 4px;
        }
        
        .episode-list::-webkit-scrollbar-thumb {
            background: #e94560;
            border-radius: 4px;
        }
        
        .episode-list::-webkit-scrollbar-thumb:hover {
            background: #c73e54;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/home">
                <i class="fas fa-play-circle"></i> <?php echo APP_NAME; ?>
            </a>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/home">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="player-container">
        <!-- Back Button & Title -->
        <div class="row mb-3">
            <div class="col-12">
                <a href="<?php echo BASE_URL; ?>/drama/<?php echo htmlspecialchars($show['slug']); ?>" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail
                </a>
            </div>
        </div>
        
        <!-- Video Player -->
        <div class="row">
            <div class="col-12">
                <div id="dplayer"></div>
            </div>
        </div>
        
        <!-- Episode Info & Navigation -->
        <div class="episode-info">
            <div class="row">
                <div class="col-md-8">
                    <h4>
                        <?php echo htmlspecialchars($show['title']); ?>
                        <?php if (isset($currentEpisode['episode_number'])): ?>
                            - Episode <?php echo $currentEpisode['episode_number']; ?>
                        <?php endif; ?>
                        <?php if (isset($currentEpisode['title']) && !empty($currentEpisode['title'])): ?>
                            <small class="text-muted"><?php echo htmlspecialchars($currentEpisode['title']); ?></small>
                        <?php endif; ?>
                    </h4>
                    <?php if (isset($currentEpisode['synopsis']) && !empty($currentEpisode['synopsis'])): ?>
                        <p class="text-muted mt-2"><?php echo nl2br(htmlspecialchars($currentEpisode['synopsis'])); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-right">
                    <div class="navigation-buttons">
                        <?php if ($prevEpisode): ?>
                        <a href="<?php echo BASE_URL; ?>/watch/<?php echo $show['slug']; ?>/<?php echo isset($prevEpisode['api_episode_id']) ? urlencode($prevEpisode['api_episode_id']) : $prevEpisode['episode_number']; ?>" 
                           class="btn btn-back mr-2">
                            <i class="fas fa-chevron-left"></i> Prev
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($nextEpisode): ?>
                        <a href="<?php echo BASE_URL; ?>/watch/<?php echo $show['slug']; ?>/<?php echo isset($nextEpisode['api_episode_id']) ? urlencode($nextEpisode['api_episode_id']) : $nextEpisode['episode_number']; ?>" 
                           class="btn btn-next">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Episode List -->
        <div class="episode-list">
            <h5 class="mb-3"><i class="fas fa-list"></i> Pilih Episode</h5>
            <div>
                <?php if (!empty($allEpisodes)): ?>
                    <?php foreach ($allEpisodes as $ep): ?>
                        <?php 
                        $isActive = false;
                        if (isset($currentEpisode['api_episode_id']) && $currentEpisode['api_episode_id'] == $ep['api_episode_id']) {
                            $isActive = true;
                        }
                        ?>
                        <a href="<?php echo BASE_URL; ?>/watch/<?php echo $show['slug']; ?>/<?php echo isset($ep['api_episode_id']) ? urlencode($ep['api_episode_id']) : $ep['episode_number']; ?>" 
                           class="btn btn-sm <?php echo $isActive ? 'active' : 'btn-outline-light'; ?> episode-btn">
                            <?php echo $ep['episode_number']; ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Daftar episode tidak tersedia.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            <p class="text-muted small">This site does not store any files on our server, we only linked to the media which is hosted on 3rd party services.</p>
        </div>
    </footer>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Hls.js for HLS streaming support -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    
    <!-- DPlayer -->
    <script src="https://cdn.jsdelivr.net/npm/dplayer/dist/DPlayer.min.js"></script>
    
    <script>
        $(document).ready(function() {
            var streamUrl = '<?php echo addslashes(isset($streamUrl) ? $streamUrl : ''); ?>';
            var streamType = '<?php echo addslashes(isset($streamType) ? $streamType : 'mp4'); ?>';
            
            // Check if URL is valid
            if (!streamUrl || streamUrl === '') {
                $('#dplayer').html('<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#fff;"><p><i class="fas fa-exclamation-triangle"></i> Stream URL tidak tersedia. Silakan coba lagi nanti.</p></div>');
                return;
            }
            
            // Initialize DPlayer with HLS support
            var dp = new DPlayer({
                container: document.getElementById('dplayer'),
                autoplay: false,
                theme: '#e94560',
                lang: 'en',
                video: {
                    url: streamUrl,
                    type: streamType === 'hls' ? 'customHls' : 'auto'
                },
                contextmenu: [
                    {
                        text: 'DramaStream',
                        link: '<?php echo BASE_URL; ?>'
                    }
                ]
            });
            
            // Custom HLS handler for better compatibility
            if (streamType === 'hls' && Hls.isSupported()) {
                dp.on('destory', function() {
                    if (dp.hls) {
                        dp.hls.destroy();
                    }
                });
            }
            
            // Save watch progress periodically
            var showId = <?php echo isset($show['id']) ? intval($show['id']) : 0; ?>;
            var episodeId = '<?php echo addslashes(isset($episodeId) ? $episodeId : ''); ?>';
            var saveProgressInterval;
            
            // Save progress every 30 seconds
            function saveProgress() {
                if (dp && dp.video && dp.video.currentTime) {
                    var progress = Math.floor(dp.video.currentTime);
                    $.ajax({
                        url: '<?php echo BASE_URL; ?>/drama/update-progress',
                        method: 'POST',
                        data: {
                            show_id: showId,
                            episode_id: episodeId,
                            progress: progress
                        },
                        success: function(response) {
                            console.log('Progress saved:', progress);
                        }
                    });
                }
            }
            
            // Save progress when video is playing
            dp.on('play', function() {
                saveProgressInterval = setInterval(saveProgress, 30000);
            });
            
            dp.on('pause', function() {
                clearInterval(saveProgressInterval);
                saveProgress();
            });
            
            dp.on('ended', function() {
                clearInterval(saveProgressInterval);
                saveProgress();
                
                // Auto navigate to next episode if available
                <?php if ($nextEpisode): ?>
                if (confirm('Episode selesai! Lanjut ke episode berikutnya?')) {
                    window.location.href = '<?php echo BASE_URL; ?>/watch/<?php echo $show['slug']; ?>/<?php echo isset($nextEpisode['api_episode_id']) ? urlencode($nextEpisode['api_episode_id']) : $nextEpisode['episode_number']; ?>';
                }
                <?php endif; ?>
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // F for fullscreen
                if (e.key === 'f' || e.key === 'F') {
                    dp.fullScreen.toggle();
                }
                // Space for play/pause
                if (e.key === ' ') {
                    e.preventDefault();
                    dp.toggle();
                }
                // Arrow keys for seek
                if (e.key === 'ArrowLeft') {
                    dp.video.currentTime -= 5;
                }
                if (e.key === 'ArrowRight') {
                    dp.video.currentTime += 5;
                }
            });
        });
    </script>
</body>
</html>

<?php
/**
 * Player Watch View
 * Video player with DPlayer + hls.js integration
 * PHP 5.6 - 8.3 Compatible
 * 
 * Features:
 * - DPlayer with hls.js for HLS (.m3u8) support
 * - Fallback for native HLS (Safari/iOS)
 * - Dark mode UI
 * - Max-width 900px centered container
 * - Error handling for empty videoUrl
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? e($page_title) : APP_NAME; ?></title>
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- DPlayer CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dplayer/dist/DPlayer.min.css">
    
    <style>
        /* Dark Mode Base Styles */
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        /* Navbar Styles */
        .navbar {
            background-color: #1a1a1a !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
            padding: 15px 0;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: #e94560 !important;
        }
        
        .nav-link {
            color: #e0e0e0 !important;
        }
        
        .nav-link:hover {
            color: #e94560 !important;
        }
        
        /* Main Container */
        .main-container {
            padding: 30px 15px;
        }
        
        /* Back Button */
        .btn-back {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
            border-radius: 20px;
            padding: 10px 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
            border-color: #545b62;
            color: #fff;
            transform: translateX(-5px);
        }
        
        /* Video Player Container */
        .player-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background-color: #1a1a1a;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(233, 69, 96, 0.2);
        }
        
        #dplayer {
            width: 100%;
            height: 500px;
            background-color: #000;
        }
        
        /* Episode Info Box */
        .episode-info-box {
            max-width: 900px;
            margin: 20px auto;
            background-color: #1a1a1a;
            border-radius: 15px;
            padding: 20px;
        }
        
        .episode-title {
            color: #e94560;
            font-weight: bold;
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        .episode-meta {
            color: #888;
            font-size: 0.9rem;
        }
        
        /* Error Message */
        .error-message {
            text-align: center;
            padding: 60px 20px;
            background-color: #1a1a1a;
            border-radius: 15px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .error-message i {
            font-size: 4rem;
            color: #e94560;
            margin-bottom: 20px;
        }
        
        .error-message h3 {
            color: #e94560;
            margin-bottom: 15px;
        }
        
        .error-message p {
            color: #888;
            margin-bottom: 20px;
        }
        
        /* Footer */
        footer {
            background-color: #1a1a1a;
            padding: 30px 0;
            margin-top: 50px;
            border-top: 1px solid #333;
        }
        
        footer p {
            margin: 0;
            color: #888;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            #dplayer {
                height: 250px;
            }
            
            .episode-title {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-container {
                padding: 20px 10px;
            }
            
            #dplayer {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo url('home'); ?>">
                <i class="fas fa-play-circle"></i> Nontonin
            </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('home'); ?>">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-container">
        <div class="container">
            <!-- Back Button -->
            <div class="row">
                <div class="col-12">
                    <a href="<?php echo url('home'); ?>" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali ke Home
                    </a>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo e($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Check if video URL exists -->
            <?php if (!empty($videoUrl)): ?>
                <!-- Video Player -->
                <div class="player-wrapper">
                    <div id="dplayer"></div>
                </div>
                
                <!-- Episode Info -->
                <div class="episode-info-box">
                    <h2 class="episode-title">
                        <i class="fas fa-play-circle"></i>
                        <?php echo isset($episodeInfo['title']) ? e($episodeInfo['title']) : 'Watching Episode'; ?>
                    </h2>
                    <div class="episode-meta">
                        <?php if (isset($provider) && !empty($provider)): ?>
                            <span class="mr-3">
                                <i class="fas fa-tv"></i> Provider: <?php echo e($provider); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (isset($episodeId) && !empty($episodeId)): ?>
                            <span>
                                <i class="fas fa-film"></i> Episode ID: <?php echo e($episodeId); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Error State: No Video URL -->
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Video Tidak Tersedia</h3>
                    <p>Maaf, link streaming untuk episode ini tidak dapat dimuat atau sudah kadaluarsa.</p>
                    <p class="small">Silakan coba kembali ke halaman detail drama dan pilih episode lain.</p>
                    <a href="<?php echo url('home'); ?>" class="btn btn-back" style="display: inline-block;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Home
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Nontonin. All rights reserved.</p>
            <p class="small mt-2">This site does not store any files on our server.</p>
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
    
    <!-- Player Initialization Script -->
    <?php if (!empty($videoUrl)): ?>
    <script>
        $(document).ready(function() {
            // Get video URL from PHP (safely escaped)
            var videoUrl = '<?php echo addslashes($videoUrl); ?>';
            var streamType = '<?php echo isset($streamType) ? addslashes($streamType) : 'hls'; ?>';
            
            // Check if URL is valid
            if (!videoUrl || videoUrl === '') {
                console.error('No video URL provided');
                return;
            }
            
            // Initialize DPlayer with HLS support
            var dp = null;
            
            // Check for HLS support
            if (streamType === 'hls' && Hls.isSupported()) {
                // Use hls.js for browsers that support it
                dp = new DPlayer({
                    container: document.getElementById('dplayer'),
                    autoplay: false,
                    theme: '#e94560',
                    lang: 'en',
                    video: {
                        url: videoUrl,
                        type: 'customHls',
                        customType: {
                            'customHls': function(video, player) {
                                var hls = new Hls({
                                    // HLS configuration options
                                    enableWorker: true,
                                    lowLatencyMode: false,
                                    backBufferLength: 90
                                });
                                
                                hls.loadSource(videoUrl);
                                hls.attachMedia(video);
                                
                                // Handle HLS events
                                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                                    console.log('HLS manifest parsed successfully');
                                });
                                
                                hls.on(Hls.Events.ERROR, function(event, data) {
                                    console.error('HLS error:', data);
                                    if (data.fatal) {
                                        switch(data.type) {
                                            case Hls.ErrorTypes.NETWORK_ERROR:
                                                console.log('Network error, trying to recover...');
                                                hls.startLoad();
                                                break;
                                            case Hls.ErrorTypes.MEDIA_ERROR:
                                                console.log('Media error, trying to recover...');
                                                hls.recoverMediaError();
                                                break;
                                            default:
                                                console.log('Fatal error, cannot recover');
                                                break;
                                        }
                                    }
                                });
                                
                                // Store hls instance for cleanup
                                player.hls = hls;
                            }
                        }
                    },
                    contextmenu: [
                        {
                            text: 'Nontonin',
                            link: '<?php echo url('home'); ?>'
                        }
                    ]
                });
            } else if (streamType === 'hls' && video.canPlayType('application/vnd.apple.mpegurl')) {
                // Native HLS support (Safari/iOS)
                dp = new DPlayer({
                    container: document.getElementById('dplayer'),
                    autoplay: false,
                    theme: '#e94560',
                    lang: 'en',
                    video: {
                        url: videoUrl,
                        type: 'auto'
                    },
                    contextmenu: [
                        {
                            text: 'Nontonin',
                            link: '<?php echo url('home'); ?>'
                        }
                    ]
                });
            } else {
                // MP4 or other formats
                dp = new DPlayer({
                    container: document.getElementById('dplayer'),
                    autoplay: false,
                    theme: '#e94560',
                    lang: 'en',
                    video: {
                        url: videoUrl,
                        type: 'auto'
                    },
                    contextmenu: [
                        {
                            text: 'Nontonin',
                            link: '<?php echo url('home'); ?>'
                        }
                    ]
                });
            }
            
            // Keyboard shortcuts
            if (dp) {
                document.addEventListener('keydown', function(e) {
                    // Ignore if typing in input field
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                        return;
                    }
                    
                    // F for fullscreen
                    if (e.key === 'f' || e.key === 'F') {
                        e.preventDefault();
                        dp.fullScreen.toggle();
                    }
                    // Space for play/pause
                    if (e.key === ' ') {
                        e.preventDefault();
                        dp.toggle();
                    }
                    // Arrow Left for seek backward
                    if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        dp.video.currentTime = Math.max(0, dp.video.currentTime - 5);
                    }
                    // Arrow Right for seek forward
                    if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        dp.video.currentTime = Math.min(dp.video.duration, dp.video.currentTime + 5);
                    }
                    // Arrow Up for volume up
                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        dp.volume(Math.min(1, dp.video.volume + 0.1));
                    }
                    // Arrow Down for volume down
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        dp.volume(Math.max(0, dp.video.volume - 0.1));
                    }
                });
            }
            
            // Log player info
            console.log('DPlayer initialized with video URL:', videoUrl);
            console.log('Stream type:', streamType);
        });
    </script>
    <?php endif; ?>
</body>
</html>

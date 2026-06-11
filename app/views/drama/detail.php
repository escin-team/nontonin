<?php
/**
 * Drama Detail View
 * Displays drama information and episode list
 * PHP 5.6 - 8.3 Compatible
 * 
 * Features:
 * - 2-column layout (poster + info)
 * - Dark mode UI
 * - Episode grid buttons
 * - Back button to home
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CRITICAL: Prevent hotlink protection by blocking referer header -->
    <meta name="referrer" content="no-referrer">
    <title><?php echo isset($page_title) ? e($page_title) : APP_NAME; ?></title>
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
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
        
        /* Drama Poster */
        .drama-poster {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.2);
        }
        
        .drama-poster img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        /* Provider Badge */
        .provider-badge-large {
            display: inline-block;
            background-color: rgba(233, 69, 96, 0.9);
            color: #fff;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        
        /* Drama Title */
        .drama-title {
            color: #e94560;
            font-weight: bold;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        /* Drama Info */
        .drama-info {
            background-color: #1a1a1a;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .drama-synopsis {
            line-height: 1.8;
            color: #ccc;
        }
        
        .drama-meta {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        /* Episodes Section */
        .episodes-section {
            background-color: #1a1a1a;
            border-radius: 15px;
            padding: 25px;
        }
        
        .episodes-title {
            color: #e94560;
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .episodes-title i {
            margin-right: 10px;
        }
        
        /* Episode Buttons Grid */
        .episode-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
        }
        
        .episode-btn {
            background-color: #2a2a2a;
            border: 2px solid #333;
            color: #e0e0e0;
            padding: 12px 8px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .episode-btn:hover {
            background-color: #e94560;
            border-color: #e94560;
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(233, 69, 96, 0.3);
        }
        
        .episode-btn small {
            display: block;
            font-size: 0.75rem;
            color: #888;
            margin-top: 3px;
        }
        
        .episode-btn:hover small {
            color: #eee;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #333;
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
            .drama-title {
                font-size: 1.5rem;
            }
            
            .episode-grid {
                grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
            }
            
            .episode-btn {
                padding: 10px 5px;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-container {
                padding: 20px 10px;
            }
            
            .drama-info,
            .episodes-section {
                padding: 20px 15px;
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
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo e($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Drama Detail Layout -->
            <?php if (!empty($drama)): ?>
                <div class="row">
                    <!-- Left Column: Poster -->
                    <div class="col-md-4 mb-4">
                        <div class="drama-poster">
                            <?php 
                            $poster = isset($drama['poster']) && !empty($drama['poster']) ? $drama['poster'] : 'https://via.placeholder.com/300x450?text=No+Poster';
                            ?>
                            <img src="<?php echo e($poster); ?>" 
                                 alt="<?php echo isset($drama['title']) ? e($drama['title']) : 'Drama Poster'; ?>"
                                 onerror="this.src='https://via.placeholder.com/300x450?text=No+Poster'">
                        </div>
                    </div>
                    
                    <!-- Right Column: Info & Episodes -->
                    <div class="col-md-8">
                        <!-- Provider Badge -->
                        <?php if (isset($provider) && !empty($provider)): ?>
                            <span class="provider-badge-large">
                                <i class="fas fa-tv"></i> <?php echo e($provider); ?>
                            </span>
                        <?php endif; ?>
                        
                        <!-- Drama Title -->
                        <h1 class="drama-title">
                            <?php echo isset($drama['title']) ? e($drama['title']) : 'Unknown Drama'; ?>
                        </h1>
                        
                        <!-- Drama Meta Info -->
                        <div class="drama-info">
                            <?php if (isset($drama['release_year']) && !empty($drama['release_year'])): ?>
                                <p class="drama-meta">
                                    <i class="fas fa-calendar"></i> 
                                    <strong>Year:</strong> <?php echo e($drama['release_year']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (isset($drama['status']) && !empty($drama['status'])): ?>
                                <p class="drama-meta">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Status:</strong> <?php echo e(ucfirst($drama['status'])); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (isset($drama['genres']) && !empty($drama['genres'])): ?>
                                <p class="drama-meta">
                                    <i class="fas fa-tags"></i> 
                                    <strong>Genres:</strong> 
                                    <?php 
                                    if (is_array($drama['genres'])) {
                                        echo e(implode(', ', $drama['genres']));
                                    } else {
                                        echo e($drama['genres']);
                                    }
                                    ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Synopsis -->
                            <hr style="border-color: #333; margin: 20px 0;">
                            <h5 style="color: #e94560; margin-bottom: 15px;">
                                <i class="fas fa-book-open"></i> Sinopsis
                            </h5>
                            <div class="drama-synopsis">
                                <?php 
                                $synopsis = isset($drama['synopsis']) && !empty($drama['synopsis']) ? $drama['synopsis'] : 'Sinopsis tidak tersedia.';
                                echo nl2br(e($synopsis));
                                ?>
                            </div>
                        </div>
                        
                        <!-- Episodes Section -->
                        <div class="episodes-section">
                            <h4 class="episodes-title">
                                <i class="fas fa-list"></i> Daftar Episode
                            </h4>
                            
                            <?php if (!empty($episodes) && is_array($episodes)): ?>
                                <div class="episode-grid">
                                    <?php foreach ($episodes as $episode): ?>
                                        <?php 
                                        // Safe access to episode data
                                        $episodeId = isset($episode['id']) ? $episode['id'] : (isset($episode['episode_id']) ? $episode['episode_id'] : '');
                                        $episodeNumber = isset($episode['number']) ? $episode['number'] : (isset($episode['episode_number']) ? $episode['episode_number'] : '');
                                        $episodeTitle = isset($episode['title']) ? $episode['title'] : '';
                                        
                                        // Skip if no ID
                                        if (empty($episodeId)) continue;
                                        ?>
                                        <a href="<?php echo url('watch/' . $provider . '/' . $episodeId); ?>" 
                                           class="episode-btn"
                                           title="<?php echo !empty($episodeTitle) ? e($episodeTitle) : 'Watch Episode'; ?>">
                                            <i class="fas fa-play-circle"></i><br>
                                            <?php echo !empty($episodeNumber) ? e($episodeNumber) : e($episodeId); ?>
                                            <?php if (!empty($episodeTitle)): ?>
                                                <small><?php echo e(substr($episodeTitle, 0, 15)); ?><?php echo strlen($episodeTitle) > 15 ? '...' : ''; ?></small>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-film"></i>
                                    <p>Episode belum tersedia. Silakan coba lagi nanti.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state" style="padding: 100px 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 5rem; color: #e94560;"></i>
                    <h3 style="color: #e94560;">Drama Tidak Ditemukan</h3>
                    <p>Drama yang Anda cari tidak ada atau tidak dapat dimuat.</p>
                    <a href="<?php echo url('home'); ?>" class="btn btn-back" style="display: inline-block; margin-top: 20px;">
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
</body>
</html>

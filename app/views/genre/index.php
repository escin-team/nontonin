<?php
/**
 * Genre Index View
 * Displays list of genre cards/buttons
 * PHP 5.6 - 8.3 Compatible
 * 
 * Features:
 * - Dark mode UI (background: #121212, text: #e0e0e0)
 * - Grid of genre cards
 * - Each card links to genre detail page
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
        
        .navbar-brand:hover {
            color: #ff6b81 !important;
        }
        
        .nav-link {
            color: #e0e0e0 !important;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: #e94560 !important;
        }
        
        /* Main Container */
        .main-container {
            padding: 30px 15px;
        }
        
        /* Section Title */
        .section-title {
            color: #e94560;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #ff6b81;
        }
        
        .section-desc {
            color: #888;
            margin-bottom: 30px;
        }
        
        /* Genre Card Styles */
        .genre-card {
            background-color: #1a1a1a;
            border: 2px solid #333;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            text-decoration: none;
            display: block;
            padding: 30px 20px;
            text-align: center;
        }
        
        .genre-card:hover {
            transform: translateY(-5px);
            border-color: #e94560;
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.3);
            background-color: #252525;
        }
        
        .genre-icon {
            font-size: 3rem;
            color: #e94560;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .genre-card:hover .genre-icon {
            color: #ff6b81;
            transform: scale(1.1);
        }
        
        .genre-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #e0e0e0;
            margin: 0;
            transition: color 0.3s ease;
        }
        
        .genre-card:hover .genre-name {
            color: #e94560;
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
            .section-title {
                font-size: 1.3rem;
            }
            
            .genre-icon {
                font-size: 2.5rem;
            }
            
            .genre-name {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            .main-container {
                padding: 20px 10px;
            }
            
            .genre-card {
                padding: 25px 15px;
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
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('search'); ?>">
                            <i class="fas fa-search"></i> Search
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo url('genre'); ?>">
                            <i class="fas fa-th-large"></i> Genres
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <span class="nav-link">
                                <i class="fas fa-user"></i> <?php echo isset($_SESSION['username']) ? e($_SESSION['username']) : 'User'; ?>
                            </span>
                        </li>
                        <li class="nav-item ml-2">
                            <a class="nav-link btn btn-logout" href="<?php echo url('auth/logout'); ?>" style="background-color: #6c757d; border-color: #6c757d; color: #fff !important; border-radius: 20px; padding: 8px 25px;">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('auth/login'); ?>" style="background-color: #e94560; border-color: #e94560; color: #fff !important; border-radius: 20px; padding: 8px 25px;">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-container">
        <div class="container">
            <!-- Page Title -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="section-title">
                        <i class="fas fa-th-large"></i> Browse by Genre
                    </h2>
                    <p class="section-desc">
                        Pilih genre favoritmu untuk menemukan drama yang sesuai
                    </p>
                </div>
            </div>
            
            <!-- Genre Grid -->
            <div class="row">
                <?php if (!empty($genres) && is_array($genres)): ?>
                    <?php foreach ($genres as $genre): ?>
                        <?php 
                        $genreId = isset($genre['id']) ? $genre['id'] : '';
                        $genreName = isset($genre['name']) ? $genre['name'] : 'Unknown';
                        $genreIcon = isset($genre['icon']) ? $genre['icon'] : 'fa-film';
                        
                        // Skip if no ID
                        if (empty($genreId)) continue;
                        ?>
                        <div class="col-6 col-md-4 col-lg-3 mb-4">
                            <a href="<?php echo url('genre/' . $genreId); ?>" class="genre-card">
                                <div class="genre-icon">
                                    <i class="fas <?php echo e($genreIcon); ?>"></i>
                                </div>
                                <h5 class="genre-name"><?php echo e($genreName); ?></h5>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center" style="padding: 60px 20px; color: #666;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 20px; color: #333;"></i>
                            <h3>Genre tidak tersedia</h3>
                            <p>Silakan coba lagi nanti</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Search Link -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <p class="text-muted">Tidak menemukan genre yang kamu cari?</p>
                    <a href="<?php echo url('search'); ?>" class="btn" style="background-color: #e94560; color: #fff; border-radius: 20px; padding: 10px 30px;">
                        <i class="fas fa-search"></i> Cari Drama
                    </a>
                </div>
            </div>
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


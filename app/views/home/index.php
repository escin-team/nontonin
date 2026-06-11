<?php
/**
 * Home Index View
 * Displays grid of trending dramas from multiple providers
 * PHP 5.6 - 8.3 Compatible
 * 
 * Features:
 * - Dark mode UI (background: #121212, text: #e0e0e0)
 * - Responsive grid (col-6 col-md-3 col-lg-2)
 * - Poster with height 250px, object-fit: cover
 * - Hover effect (scale 1.05)
 * - Provider badge
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
        
        .btn-login {
            background-color: #e94560;
            border-color: #e94560;
            color: #fff !important;
            border-radius: 20px;
            padding: 8px 25px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background-color: #c73e54;
            border-color: #c73e54;
            transform: translateY(-2px);
        }
        
        .btn-logout {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff !important;
            border-radius: 20px;
            padding: 8px 25px;
        }
        
        .btn-logout:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        
        /* Main Container */
        .main-container {
            padding: 30px 15px;
        }
        
        /* Section Title */
        .section-title {
            color: #e94560;
            font-weight: bold;
            margin-bottom: 25px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #ff6b81;
        }
        
        /* Drama Card Styles */
        .drama-card {
            background-color: #1a1a1a;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            cursor: pointer;
        }
        
        .drama-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.3);
        }
        
        .drama-card .card-img-wrapper {
            position: relative;
            overflow: hidden;
            height: 250px;
        }
        
        .drama-card .card-img-top {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }
        
        .drama-card:hover .card-img-top {
            opacity: 0.9;
        }
        
        /* Provider Badge */
        .provider-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(233, 69, 96, 0.9);
            color: #fff;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Card Body */
        .drama-card .card-body {
            padding: 12px;
            background-color: #1a1a1a;
        }
        
        .drama-card .card-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0;
            line-height: 1.4;
            max-height: 2.8em;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .drama-card .card-title a {
            color: #e0e0e0;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .drama-card:hover .card-title a {
            color: #e94560;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #333;
        }
        
        .empty-state h3 {
            color: #888;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
        }
        
        /* Loading Spinner */
        .loading-spinner {
            text-align: center;
            padding: 50px;
        }
        
        .spinner-border {
            color: #e94560;
            width: 3rem;
            height: 3rem;
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
            
            .drama-card .card-img-wrapper {
                height: 200px;
            }
            
            .drama-card .card-img-top {
                height: 200px;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            .main-container {
                padding: 20px 10px;
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
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo url('home'); ?>">
                            <i class="fas fa-home"></i> Home
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
                            <a class="nav-link btn btn-logout" href="<?php echo url('auth/logout'); ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item mr-2">
                            <a class="nav-link btn btn-login" href="<?php echo url('auth/login'); ?>">
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
                        <i class="fas fa-fire"></i> Trending Dramas
                    </h2>
                    <p class="text-muted">
                        Latest trending dramas from multiple providers
                    </p>
                </div>
            </div>
            
            <!-- Drama Grid -->
            <?php if (!empty($dramas) && is_array($dramas)): ?>
                <div class="row">
                    <?php foreach ($dramas as $drama): ?>
                        <?php 
                        // Safe access to drama data
                        $dramaId = isset($drama['id']) ? $drama['id'] : '';
                        $title = isset($drama['title']) ? $drama['title'] : 'Unknown Title';
                        $poster = isset($drama['poster']) ? $drama['poster'] : '';
                        $provider = isset($drama['provider']) ? $drama['provider'] : 'unknown';
                        
                        // Skip if no ID
                        if (empty($dramaId)) continue;
                        ?>
                        <div class="col-6 col-md-3 col-lg-2 mb-4">
                            <div class="drama-card" onclick="window.location.href='<?php echo url('drama/' . $provider . '/' . $dramaId); ?>'">
                                <div class="card-img-wrapper">
                                    <img src="<?php echo !empty($poster) ? e($poster) : 'https://via.placeholder.com/200x300?text=No+Poster'; ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo e($title); ?>"
                                         onerror="this.src='https://via.placeholder.com/200x300?text=No+Poster'">
                                    
                                    <!-- Provider Badge -->
                                    <span class="provider-badge"><?php echo e($provider); ?></span>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="<?php echo url('drama/' . $provider . '/' . $dramaId); ?>">
                                            <?php echo e($title); ?>
                                        </a>
                                    </h6>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-film"></i>
                    <h3>No Dramas Available</h3>
                    <p>Check back later for new trending dramas!</p>
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

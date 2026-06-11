<?php
/**
 * Search Index View
 * Displays search form (Google-style)
 * PHP 5.6 - 8.3 Compatible
 * 
 * Features:
 * - Dark mode UI (background: #121212, text: #e0e0e0)
 * - Large centered search form
 * - Bootstrap 4 styling
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
        
        /* Main Container - Centered Content */
        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 140px);
            padding: 30px 15px;
        }
        
        /* Search Box Container */
        .search-container {
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        
        /* Logo/Title */
        .search-logo {
            font-size: 3rem;
            font-weight: bold;
            color: #e94560;
            margin-bottom: 30px;
            text-shadow: 0 2px 10px rgba(233, 69, 96, 0.3);
        }
        
        .search-logo i {
            margin-right: 10px;
        }
        
        /* Search Form */
        .search-form {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .search-icon {
            position: absolute;
            left: 20px;
            color: #888;
            font-size: 1.2rem;
            z-index: 10;
        }
        
        .search-input {
            width: 100%;
            padding: 15px 20px 15px 55px;
            font-size: 1.1rem;
            border: 2px solid #333;
            border-radius: 50px;
            background-color: #1a1a1a;
            color: #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #e94560;
            box-shadow: 0 0 15px rgba(233, 69, 96, 0.3);
            background-color: #252525;
        }
        
        .search-input::placeholder {
            color: #666;
        }
        
        /* Search Button */
        .search-btn {
            background-color: #e94560;
            border-color: #e94560;
            color: #fff;
            border-radius: 50px;
            padding: 12px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .search-btn:hover {
            background-color: #c73e54;
            border-color: #c73e54;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 69, 96, 0.4);
        }
        
        /* Quick Links */
        .quick-links {
            margin-top: 40px;
            text-align: center;
        }
        
        .quick-links p {
            color: #888;
            margin-bottom: 15px;
        }
        
        .quick-links a {
            display: inline-block;
            margin: 5px 10px;
            padding: 8px 20px;
            background-color: #1a1a1a;
            border: 1px solid #333;
            border-radius: 20px;
            color: #e0e0e0;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .quick-links a:hover {
            background-color: #e94560;
            border-color: #e94560;
            color: #fff;
        }
        
        /* Footer */
        footer {
            background-color: #1a1a1a;
            padding: 20px 0;
            margin-top: auto;
            border-top: 1px solid #333;
        }
        
        footer p {
            margin: 0;
            color: #888;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .search-logo {
                font-size: 2.5rem;
            }
            
            .search-input {
                font-size: 1rem;
                padding: 12px 15px 12px 45px;
            }
            
            .search-btn {
                padding: 10px 25px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            .search-logo {
                font-size: 2rem;
            }
            
            .search-input-wrapper {
                flex-direction: column;
            }
            
            .search-input {
                margin-bottom: 15px;
            }
            
            .search-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo url('home'); ?>">
                <i class="fas fa-play-circle"></i> Nontonin
            </a>
            
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('home'); ?>">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('genre'); ?>">
                        <i class="fas fa-th-large"></i> Genres
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Main Content - Search Form -->
    <main class="main-container">
        <div class="search-container">
            <!-- Logo/Title -->
            <div class="search-logo">
                <i class="fas fa-search"></i> Cari Drama
            </div>
            
            <!-- Search Form -->
            <form action="<?php echo url('search/results'); ?>" method="GET" class="search-form">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="q" 
                           class="search-input" 
                           placeholder="Masukkan judul drama, aktor, atau genre..." 
                           required
                           autocomplete="off">
                </div>
                <div class="mt-3">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Cari Drama
                    </button>
                </div>
            </form>
            
            <!-- Quick Links -->
            <div class="quick-links">
                <p>Atau browse berdasarkan genre:</p>
                <a href="<?php echo url('genre/romance'); ?>"><i class="fas fa-heart"></i> Romance</a>
                <a href="<?php echo url('genre/action'); ?>"><i class="fas fa-fist-raised"></i> Action</a>
                <a href="<?php echo url('genre/comedy'); ?>"><i class="fas fa-laugh"></i> Comedy</a>
                <a href="<?php echo url('genre/thriller'); ?>"><i class="fas fa-mask"></i> Thriller</a>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Nontonin. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


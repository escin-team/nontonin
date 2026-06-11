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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    
    <style>
        body {
            background-color: #1a1a2e;
            color: #eee;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: #16213e !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #e94560 !important;
        }
        
        .card {
            background-color: #16213e;
            border: none;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-img-top {
            height: 300px;
            object-fit: cover;
        }
        
        .btn-primary {
            background-color: #e94560;
            border-color: #e94560;
        }
        
        .btn-primary:hover {
            background-color: #c73e54;
            border-color: #c73e54;
        }
        
        .section-title {
            color: #e94560;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        footer {
            background-color: #0f3460;
            padding: 30px 0;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo url(''); ?>">
                <i class="fas fa-play-circle"></i> <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('home'); ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('drama/china'); ?>">Drama China</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('anime'); ?>">Anime</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('movies'); ?>">Movies</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo e(isset($_SESSION['username']) ? $_SESSION['username'] : 'User'); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="<?php echo url('profile'); ?>">Profile</a>
                            <a class="dropdown-item" href="<?php echo url('watchlist'); ?>">Watchlist</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo url('auth/logout'); ?>">Logout</a>
                        </div>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('auth/login'); ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white ml-2" href="<?php echo url('auth/register'); ?>">Register</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mt-4">
        <?php echo $content; ?>
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
    <!-- Custom JS -->
    <script src="<?php echo url('assets/js/main.js'); ?>"></script>
</body>
</html>

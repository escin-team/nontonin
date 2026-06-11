<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : APP_NAME; ?> - <?php echo APP_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .navbar { background: #e74c3c; padding: 15px 20px; color: white; }
        .navbar h1 { font-size: 24px; }
        .navbar nav { margin-top: 10px; }
        .navbar a { color: white; text-decoration: none; margin-right: 15px; }
        .navbar a:hover { text-decoration: underline; }
        .content { background: white; padding: 30px; margin-top: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 10px 20px; background: #e74c3c; color: white; text-decoration: none; border: none; border-radius: 3px; cursor: pointer; }
        .btn:hover { background: #c0392b; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 3px; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .text-center { text-align: center; }
        footer { text-align: center; padding: 20px; margin-top: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h1><?php echo APP_NAME; ?></h1>
            <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/home">Home</a>
                    <a href="<?php echo BASE_URL; ?>/auth/logout">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/auth/login">Login</a>
                    <a href="<?php echo BASE_URL; ?>/auth/register">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    
    <div class="container">
        <?php include $view_file; ?>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>
</body>
</html>

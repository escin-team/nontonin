<div class="content text-center">
    <h1 style="color: #e74c3c; margin-bottom: 20px;">404</h1>
    <h2 style="margin-bottom: 15px;">Page Not Found</h2>
    <p style="margin-bottom: 30px; color: #666;"><?php echo isset($message) ? htmlspecialchars($message) : 'The page you are looking for does not exist.'; ?></p>
    <a href="<?php echo BASE_URL; ?>/home" class="btn">Go to Home</a>
</div>

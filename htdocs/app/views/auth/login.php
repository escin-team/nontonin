<div class="content" style="max-width: 400px; margin: 50px auto;">
    <h2 class="text-center" style="margin-bottom: 30px;">Login to <?php echo APP_NAME; ?></h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo BASE_URL; ?>/auth/login">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group text-center">
            <button type="submit" class="btn" style="width: 100%;">Login</button>
        </div>
    </form>
    
    <p class="text-center" style="margin-top: 20px;">
        Don't have an account? 
        <a href="<?php echo BASE_URL; ?>/auth/register" style="color: #e74c3c;">Register here</a>
    </p>
</div>

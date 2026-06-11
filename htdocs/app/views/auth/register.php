<div class="content" style="max-width: 400px; margin: 50px auto;">
    <h2 class="text-center" style="margin-bottom: 30px;">Register for <?php echo APP_NAME; ?></h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo BASE_URL; ?>/auth/register">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password (min. 6 characters)</label>
            <input type="password" id="password" name="password" minlength="6" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div class="form-group text-center">
            <button type="submit" class="btn" style="width: 100%;">Register</button>
        </div>
    </form>
    
    <p class="text-center" style="margin-top: 20px;">
        Already have an account? 
        <a href="<?php echo BASE_URL; ?>/auth/login" style="color: #e74c3c;">Login here</a>
    </p>
</div>

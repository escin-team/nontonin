<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-body p-5">
                <h2 class="text-center mb-4"><i class="fas fa-sign-in-alt"></i> Login</h2>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/auth/login">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Enter username" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block mt-4">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <hr class="my-4">
                
                <p class="text-center mb-0">
                    Don't have an account? 
                    <a href="<?php echo BASE_URL; ?>/auth/register" class="text-primary">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

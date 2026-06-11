<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-body p-5">
                <h2 class="text-center mb-4"><i class="fas fa-user-plus"></i> Register</h2>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/auth/register">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Choose username" required autofocus>
                        <small class="form-text text-muted">Minimum 3 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Enter email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Choose password" required>
                        <small class="form-text text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block mt-4">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
                
                <hr class="my-4">
                
                <p class="text-center mb-0">
                    Already have an account? 
                    <a href="<?php echo BASE_URL; ?>/auth/login" class="text-primary">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

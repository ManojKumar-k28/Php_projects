<!-- Login Modal -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Welcome Back! 👋</h2>
            <span class="close" onclick="hideLoginModal()">&times;</span>
        </div>
        
        <div class="modal-body">
            <?php if (isset($error) && isset($_POST['action']) && $_POST['action'] === 'login'): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="login_username">Username or Email</label>
                    <input type="text" id="login_username" name="username" required 
                           placeholder="Enter your username or email">
                    <div class="form-error" id="login-username-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="login_password">Password</label>
                    <input type="password" id="login_password" name="password" required 
                           placeholder="Enter your password">
                    <div class="form-error" id="login-password-error"></div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">🚀 Login</button>
            </form>
        </div>
        
        <div class="modal-footer">
            <p>Don't have an account? 
               <a href="#" onclick="hideLoginModal(); showRegisterModal();">Create one here</a>
            </p>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div id="registerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Join Our Community! ✨</h2>
            <span class="close" onclick="hideRegisterModal()">&times;</span>
        </div>
        
        <div class="modal-body">
            <?php if (isset($error) && isset($_POST['action']) && $_POST['action'] === 'register'): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="reg_username">Username</label>
                    <input type="text" id="reg_username" name="username" required 
                           placeholder="Choose a unique username"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    <div class="form-error" id="reg-username-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="reg_email">Email</label>
                    <input type="email" id="reg_email" name="email" required 
                           placeholder="Enter your email address"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <div class="form-error" id="reg-email-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="reg_password">Password</label>
                    <input type="password" id="reg_password" name="password" required 
                           minlength="6" placeholder="Create a strong password (min 6 chars)">
                    <div class="form-error" id="reg-password-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="reg_confirm_password">Confirm Password</label>
                    <input type="password" id="reg_confirm_password" name="confirm_password" required 
                           minlength="6" placeholder="Confirm your password">
                    <div class="form-error" id="reg-confirm-error"></div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">🎉 Create Account</button>
            </form>
        </div>
        
        <div class="modal-footer">
            <p>Already have an account? 
               <a href="#" onclick="hideRegisterModal(); showLoginModal();">Login here</a>
            </p>
        </div>
    </div>
</div>
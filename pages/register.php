<?php
/**
 * User Registration Page
 * Save as: pages/register.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$pageTitle = 'Register';
include __DIR__ . '/../includes/header.php';
?>

    <!-- Registration Form -->
    <main class="main-content">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1 class="auth-title">Create Account</h1>
                    <p>Join our blogging community</p>
                </div>

                <div id="message"></div>

                <form id="registerForm" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control" 
                            required 
                            minlength="3"
                            placeholder="Choose a username">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            required
                            placeholder="your@email.com">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            required 
                            minlength="6"
                            placeholder="At least 6 characters">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control" 
                            required
                            placeholder="Re-enter your password">
                    </div>

                    <button type="submit" class="btn btn-primary">Register</button>
                </form>

                <div class="auth-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const messageDiv = document.getElementById('message');
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                messageDiv.innerHTML = '<div class="alert alert-error">Passwords do not match!</div>';
                return;
            }
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('<?php echo SITE_URL; ?>/api/register-handler.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
            }
        });
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * User Login Page
 * Save as: pages/login.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$pageTitle = 'Login';
include __DIR__ . '/../includes/header.php';
?>

    <!-- Login Form -->
    <main class="main-content">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1 class="auth-title">Welcome Back</h1>
                    <p>Login to your account</p>
                </div>

                <div id="message"></div>

                <form id="loginForm" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Username or Email</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control" 
                            required
                            placeholder="Enter your username or email">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            required
                            placeholder="Enter your password">
                    </div>

                    <button type="submit" class="btn btn-primary">Login</button>
                </form>

                <div class="auth-link">
                    Don't have an account? <a href="register.php">Register here</a>
                </div>

                <div class="auth-link" style="margin-top: 0.5rem; font-size: 0.875rem;">
                    <strong>Demo Account:</strong> testuser / password123
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const messageDiv = document.getElementById('message');
            const formData = new FormData(this);
            
            try {
                const response = await fetch('<?php echo SITE_URL; ?>/api/login-handler.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                    setTimeout(() => {
                        window.location.href = '<?php echo SITE_URL; ?>/index.php';
                    }, 1000);
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
            }
        });
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
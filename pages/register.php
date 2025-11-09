<?php
/**
 * User Registration Page - Updated for New Database Schema
 * Save as: pages/register.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$pageTitle = 'Register';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* Auth Container */
.auth-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.auth-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 2.5rem;
    width: 100%;
    max-width: 450px;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #6b7280;
    font-size: 0.9375rem;
}

.auth-link {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    color: #6b7280;
    font-size: 0.9375rem;
}

.auth-link a {
    color: #3b82f6;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.auth-link a:hover {
    color: #2563eb;
    text-decoration: underline;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9375rem;
    color: #1f2937;
    background: white;
    transition: all 0.2s ease;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #111827;
    box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.1);
}

.form-control::placeholder {
    color: #9ca3af;
}

/* Button */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    font-weight: 600;
    font-size: 0.875rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 2px solid transparent;
    font-family: inherit;
    width: 100%;
}

.btn-primary {
    background: #111827;
    color: white;
    border-color: #111827;
    box-shadow: 0 1px 2px rgba(17, 24, 39, 0.1);
}

.btn-primary:hover {
    background: #1f2937;
    border-color: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.3);
}

.btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(17, 24, 39, 0.2);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Alert Styles */
.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 2px solid #10b981;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 2px solid #ef4444;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .auth-card {
        padding: 2rem 1.5rem;
    }
}
</style>

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
                        maxlength="50"
                        pattern="[a-zA-Z0-9_ ]+"
                        title="Username can only contain letters, numbers, underscores, and spaces"
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

                <button type="submit" class="btn btn-primary" id="registerBtn">
                    Register
                </button>
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
        const registerBtn = document.getElementById('registerBtn');
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Validate passwords match
        if (password !== confirmPassword) {
            messageDiv.innerHTML = '<div class="alert-error">Passwords do not match!</div>';
            return;
        }
        
        // Disable button and show loading
        registerBtn.disabled = true;
        registerBtn.textContent = 'Creating account...';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/register-handler.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                messageDiv.innerHTML = '<div class="alert-success">' + result.message + '</div>';
                this.reset();
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
            } else {
                messageDiv.innerHTML = '<div class="alert-error">' + result.message + '</div>';
                registerBtn.disabled = false;
                registerBtn.textContent = 'Register';
            }
        } catch (error) {
            console.error('Registration error:', error);
            messageDiv.innerHTML = '<div class="alert-error">An error occurred. Please try again.</div>';
            registerBtn.disabled = false;
            registerBtn.textContent = 'Register';
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * User Login Page - Professional Design
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

<style>
.auth-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    background: #f9fafb;
}

.auth-container {
    width: 100%;
    max-width: 440px;
}

.auth-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 3rem 2.5rem;
}

.auth-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.auth-title {
    font-size: 2rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 0.5rem;
    letter-spacing: -0.02em;
}

.auth-subtitle {
    color: #6b7280;
    font-size: 0.9375rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9375rem;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9375rem;
    transition: all 0.2s;
    font-family: inherit;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #111827;
    box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.08);
}

.form-control::placeholder {
    color: #9ca3af;
}

.btn-submit {
    width: 100%;
    padding: 1rem;
    background: #111827;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 0.5rem;
}

.btn-submit:hover {
    background: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.2);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.auth-divider {
    text-align: center;
    margin: 2rem 0;
    position: relative;
}

.auth-divider::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background: #e5e7eb;
}

.auth-divider span {
    background: white;
    padding: 0 1rem;
    color: #9ca3af;
    font-size: 0.875rem;
    position: relative;
    z-index: 1;
}

.auth-link {
    text-align: center;
    margin-top: 2rem;
    color: #6b7280;
    font-size: 0.9375rem;
}

.auth-link a {
    color: #111827;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.auth-link a:hover {
    color: #3b82f6;
}

.demo-badge {
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1.5rem;
    text-align: center;
}

.demo-badge-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.demo-credentials {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    color: #374151;
    font-weight: 600;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.9375rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.alert-info {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
}
</style>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your account</p>
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
                        placeholder="Enter your username or email"
                        autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        required
                        placeholder="Enter your password"
                        autocomplete="current-password">
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    Sign In
                </button>
            </form>

           

            <div class="auth-divider">
                <span>or</span>
            </div>

            <div class="auth-link">
                Don't have an account? <a href="register.php">Create one</a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const messageDiv = document.getElementById('message');
    const submitBtn = document.getElementById('submitBtn');
    const formData = new FormData(this);
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Signing in...';
    messageDiv.innerHTML = '<div class="alert alert-info">Please wait...</div>';
    
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
            submitBtn.disabled = false;
            submitBtn.textContent = 'Sign In';
        }
    } catch (error) {
        console.error('Error:', error);
        messageDiv.innerHTML = '<div class="alert alert-error">Login failed. Please try again.</div>';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Sign In';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
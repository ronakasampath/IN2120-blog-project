<?php
/**
 * User Profile Page - WITH BIO FIELD
 * Save as: pages/profile.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';

requireLogin();

$pageTitle = 'My Profile';
$currentUser = getCurrentUser();

// Get user's full data
$db = getDB();
$stmt = $db->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$currentUser['id']]);
$userFull = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<style>
/* Profile Container */
.profile-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 3rem 2rem;
}

/* Profile Header Card */
.profile-header-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 2.5rem;
    margin-bottom: 2rem;
    text-align: center;
}

.profile-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
    letter-spacing: -0.025em;
}

.profile-subtitle {
    color: #6b7280;
    font-size: 1rem;
}

/* Section Cards */
.profile-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 2.5rem;
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* Message/Alert Styles */
#message {
    margin-bottom: 2rem;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-weight: 500;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.alert-info {
    background: #dbeafe;
    color: #1e40af;
    border-left: 4px solid #3b82f6;
}

/* Profile Picture Section */
.profile-picture-section {
    display: flex;
    align-items: center;
    gap: 2rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.profile-picture-wrapper {
    position: relative;
}

.profile-picture-current {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.profile-picture-placeholder {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: white;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.profile-picture-upload {
    flex: 1;
    min-width: 280px;
}

/* Image Preview */
#imagePreview {
    margin: 1.5rem 0;
    text-align: center;
}

#previewImg {
    max-width: 200px;
    max-height: 200px;
    border-radius: 12px;
    border: 3px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.625rem;
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

.form-control:disabled {
    background: #f9fafb;
    color: #6b7280;
    cursor: not-allowed;
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
    line-height: 1.6;
}

.form-hint {
    color: #6b7280;
    font-size: 0.875rem;
    display: block;
    margin-top: 0.5rem;
}

.form-container {
    max-width: 500px;
}

.char-count {
    text-align: right;
    color: #9ca3af;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.char-count.warning {
    color: #f59e0b;
}

.char-count.error {
    color: #ef4444;
}

/* Admin Badge */
.admin-badge {
    display: inline-block;
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: white;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    margin-left: 0.75rem;
    text-transform: uppercase;
}

/* Buttons */
.btn {
    padding: 0.875rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
    border: 2px solid transparent;
    cursor: pointer;
    font-size: 0.9375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-family: inherit;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.btn-primary {
    background: #111827;
    color: white;
    border-color: #111827;
}

.btn-primary:hover:not(:disabled) {
    background: #1f2937;
    border-color: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.25);
}

.btn-success {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.btn-success:hover:not(:disabled) {
    background: #059669;
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-danger {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.btn-danger:hover:not(:disabled) {
    background: #dc2626;
    border-color: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-secondary {
    background: white;
    color: #6b7280;
    border-color: #d1d5db;
}

.btn-secondary:hover:not(:disabled) {
    background: #f9fafb;
    color: #111827;
    border-color: #9ca3af;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

/* Responsive */
@media (max-width: 768px) {
    .profile-container {
        padding: 2rem 1.25rem;
    }
    
    .profile-card,
    .profile-header-card {
        padding: 2rem 1.5rem;
    }
    
    .profile-picture-section {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-picture-upload {
        width: 100%;
    }
    
    .form-container {
        max-width: 100%;
    }
    
    .profile-title {
        font-size: 1.625rem;
    }
}
</style>

<main class="main-content">
    <div class="profile-container">
        <!-- Header -->
        <div class="profile-header-card">
            <h1 class="profile-title">👤 My Profile</h1>
            <p class="profile-subtitle">Manage your profile information and settings</p>
        </div>

        <!-- Message Area -->
        <div id="message"></div>

        <!-- Profile Picture Section -->
        <div class="profile-card">
            <h2 class="section-title">
                <span class="icon-camera"></span>
                Profile Picture
            </h2>
            
            <div class="profile-picture-section">
                <div class="profile-picture-wrapper">
                    <?php if ($userFull['profile_picture']): ?>
                        <img src="<?php echo SITE_URL . htmlspecialchars($userFull['profile_picture']); ?>" 
                             alt="Profile" 
                             class="profile-picture-current">
                    <?php else: ?>
                        <div class="profile-picture-placeholder">
                            <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="profile-picture-upload">
                    <form id="profilePicForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Choose New Profile Picture</label>
                            <input type="file" 
                                   name="profile_picture" 
                                   id="profilePicInput"
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                                   class="form-control"
                                   required>
                            <small class="form-hint">
                                Allowed: JPG, PNG, GIF, WEBP (Max 5MB)
                            </small>
                        </div>
                        
                        <div id="imagePreview" style="display: none;">
                            <img id="previewImg" alt="Preview">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            📤 Upload Profile Picture
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="profile-card">
            <h2 class="section-title">
                <span class="icon-info"></span>
                Account Information
            </h2>
            
            <form id="updateInfoForm" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" 
                           name="username" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($userFull['username']); ?>"
                           required
                           minlength="3"
                           placeholder="Enter your username">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" 
                           name="email" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($userFull['email']); ?>"
                           required
                           placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea 
                        name="bio" 
                        id="bioTextarea"
                        class="form-control" 
                        maxlength="500"
                        placeholder="Tell us about yourself... (Optional)"><?php echo htmlspecialchars($userFull['bio'] ?? ''); ?></textarea>
                    <small class="form-hint">
                        Write a short bio that will be displayed on your profile
                    </small>
                    <div class="char-count" id="bioCharCount">
                        <span id="bioCharCurrent">0</span> / 500 characters
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <input type="text" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($userFull['role']); ?>"
                               disabled
                               style="flex: 1;">
                        <?php if (isAdmin()): ?>
                            <span class="admin-badge">ADMIN</span>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    💾 Update Information
                </button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="profile-card">
            <h2 class="section-title">
                <span class="icon-lock"></span>
                Change Password
            </h2>
            
            <form id="changePasswordForm" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" 
                           name="current_password" 
                           class="form-control" 
                           required
                           placeholder="Enter current password">
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" 
                           name="new_password" 
                           class="form-control" 
                           required
                           minlength="6"
                           placeholder="Enter new password (min 6 characters)">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" 
                           name="confirm_password" 
                           class="form-control" 
                           required
                           placeholder="Confirm new password">
                </div>

                <button type="submit" class="btn btn-primary">
                    🔑 Change Password
                </button>
            </form>
        </div>

        <!-- Back Button -->
        <div class="profile-card" style="text-align: center;">
            <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">
                ← Back to Home
            </a>
        </div>
    </div>
</main>

<script>
// Character counter for bio
const bioTextarea = document.getElementById('bioTextarea');
const bioCharCurrent = document.getElementById('bioCharCurrent');
const bioCharCount = document.getElementById('bioCharCount');

function updateCharCount() {
    const length = bioTextarea.value.length;
    bioCharCurrent.textContent = length;
    
    if (length > 450) {
        bioCharCount.classList.add('warning');
        bioCharCount.classList.remove('error');
    }
    if (length >= 500) {
        bioCharCount.classList.add('error');
        bioCharCount.classList.remove('warning');
    }
    if (length < 450) {
        bioCharCount.classList.remove('warning', 'error');
    }
}

bioTextarea.addEventListener('input', updateCharCount);
updateCharCount(); // Initial count

// Preview image
document.getElementById('profilePicInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size
        if (file.size > 5 * 1024 * 1024) {
            showMessage('File is too large. Maximum size is 5MB.', 'error');
            this.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Upload profile picture
document.getElementById('profilePicForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Uploading...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/upload-profile-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Update info
document.getElementById('updateInfoForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Updating...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/update-user-info-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Change password
document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const newPass = this.new_password.value;
    const confirmPass = this.confirm_password.value;
    
    if (newPass !== confirmPass) {
        showMessage('Passwords do not match', 'error');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Changing...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/change-password-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(result.message, 'success');
            this.reset();
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Show message function
function showMessage(message, type) {
    const messageDiv = document.getElementById('message');
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-error' : 'alert-info';
    
    messageDiv.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
    
    // Scroll to message
    messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    // Auto hide after 5 seconds for success messages
    if (type === 'success') {
        setTimeout(() => {
            messageDiv.innerHTML = '';
        }, 5000);
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
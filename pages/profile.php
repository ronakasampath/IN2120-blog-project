<?php
/**
 * User Profile Page - WITH EDIT
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

<main class="main-content">
    <div class="container">
        <div class="card">
            <h1>My Profile</h1>
            <p>Manage your profile information</p>
        </div>

        <div id="message"></div>

        <!-- Profile Picture Section -->
        <div class="card">
            <h2>Profile Picture</h2>
            
            <div style="display: flex; align-items: center; gap: 2rem; margin: 1.5rem 0; flex-wrap: wrap;">
                <div>
                    <?php if ($userFull['profile_picture']): ?>
                        <img src="<?php echo SITE_URL . htmlspecialchars($userFull['profile_picture']); ?>" 
                             alt="Profile" 
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb;">
                    <?php else: ?>
                        <div style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 4rem; color: white; font-weight: 600;">
                            <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="flex: 1;">
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
                            <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                                Allowed: JPG, PNG, GIF, WEBP (Max 5MB)
                            </small>
                        </div>
                        
                        <div id="imagePreview" style="display: none; margin: 1rem 0;">
                            <img id="previewImg" style="max-width: 200px; max-height: 200px; border-radius: 10px; border: 2px solid #e5e7eb;">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Upload Profile Picture</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Account Info -->
        <div class="card">
            <h2>Account Information</h2>
            
            <form id="updateInfoForm" style="max-width: 500px;">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" 
                           name="username" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($userFull['username']); ?>"
                           required
                           minlength="3">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" 
                           name="email" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($userFull['email']); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($userFull['role']); ?>"
                           disabled>
                    <?php if (isAdmin()): ?>
                        <span class="admin-badge">ADMIN</span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-success">Update Information</button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="card">
            <h2>Change Password</h2>
            
            <form id="changePasswordForm" style="max-width: 500px;">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" 
                           name="current_password" 
                           class="form-control" 
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" 
                           name="new_password" 
                           class="form-control" 
                           required
                           minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" 
                           name="confirm_password" 
                           class="form-control" 
                           required>
                </div>

                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>

        <div class="card">
            <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">← Back to Home</a>
        </div>
    </div>
</main>

<script>
    // Preview image
    document.getElementById('profilePicInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
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
        
        const messageDiv = document.getElementById('message');
        const formData = new FormData(this);
        
        messageDiv.innerHTML = '<div class="alert alert-info">Uploading...</div>';
        
        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/upload-profile-handler.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setTimeout(() => location.reload(), 1000);
            } else {
                messageDiv.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
            }
        } catch (error) {
            messageDiv.innerHTML = '<div class="alert alert-error">Upload failed</div>';
        }
    });

    // Update info
    document.getElementById('updateInfoForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const messageDiv = document.getElementById('message');
        const formData = new FormData(this);
        
        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/update-user-info-handler.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setTimeout(() => location.reload(), 1500);
            } else {
                messageDiv.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
            }
        } catch (error) {
            messageDiv.innerHTML = '<div class="alert alert-error">Update failed</div>';
        }
    });

    // Change password
    document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const messageDiv = document.getElementById('message');
        const newPass = this.new_password.value;
        const confirmPass = this.confirm_password.value;
        
        if (newPass !== confirmPass) {
            messageDiv.innerHTML = '<div class="alert alert-error">Passwords do not match</div>';
            return;
        }
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/change-password-handler.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                this.reset();
            } else {
                messageDiv.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
            }
        } catch (error) {
            messageDiv.innerHTML = '<div class="alert alert-error">Update failed</div>';
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
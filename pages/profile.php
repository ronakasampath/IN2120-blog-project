<?php
/**
 * User Profile Page
 * Save as: pages/profile.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';

requireLogin();

$pageTitle = 'My Profile';
$currentUser = getCurrentUser();

// Get user's full data including profile picture
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
            
            <div style="display: flex; align-items: center; gap: 2rem; margin: 1.5rem 0;">
                <div>
                    <?php if ($userFull['profile_picture']): ?>
                        <img src="<?php echo SITE_URL . htmlspecialchars($userFull['profile_picture']); ?>" 
                             alt="Profile" 
                             class="profile-pic-large"
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb;">
                    <?php else: ?>
                        <div style="width: 150px; height: 150px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 4rem;">
                            👤
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
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
                        
                        <!-- Preview -->
                        <div id="imagePreview" style="display: none; margin: 1rem 0;">
                            <img id="previewImg" style="max-width: 200px; max-height: 200px; border-radius: 10px; border: 2px solid #e5e7eb;">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Upload Profile Picture</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="card">
            <h2>Account Information</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem; font-weight: 600;">Username:</td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($currentUser['username']); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem; font-weight: 600;">Email:</td>
                    <td style="padding: 1rem;"><?php echo htmlspecialchars($currentUser['email']); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 1rem; font-weight: 600;">Role:</td>
                    <td style="padding: 1rem;">
                        <?php echo htmlspecialchars($currentUser['role']); ?>
                        <?php if (isAdmin()): ?>
                            <span class="admin-badge">ADMIN</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="card">
            <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">← Back to Home</a>
        </div>
    </div>
</main>

<script>
    // Preview image before upload
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
        
        // Show loading
        messageDiv.innerHTML = '<div class="alert alert-info">Uploading...</div>';
        
        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/upload-profile-handler.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                messageDiv.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
            }
        } catch (error) {
            messageDiv.innerHTML = '<div class="alert alert-error">Upload failed. Please try again.</div>';
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
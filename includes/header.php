

<?php
/**
 * Modern Header with Profile Dropdown
 * Save as: includes/header.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/database.php';
}

require_once __DIR__ . '/auth.php';

$currentUser = getCurrentUser();

// Get user profile picture if logged in
$userProfilePic = null;
if ($currentUser) {
    $db = getDB();
    $stmt = $db->prepare("SELECT profile_picture FROM user WHERE id = ?");
    $stmt->execute([$currentUser['id']]);
    $userData = $stmt->fetch();
    $userProfilePic = $userData['profile_picture'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <!-- Modern Navigation -->
    <nav class="navbar-modern">
        <div class="container navbar-modern-content">
            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-logo">
                <span class="logo-icon">✍️</span>
                <span class="logo-text"><?php echo SITE_NAME; ?></span>
            </a>

            <!-- Center: Search (optional for now) -->
            <div class="navbar-center">
                <a href="<?php echo SITE_URL; ?>/index.php" class="nav-link-minimal">
                    <span class="nav-icon">🏠</span>
                    <span class="nav-text">Home</span>
                </a>
            </div>

            <!-- Right: Actions -->
            <div class="navbar-actions">
                <?php if ($currentUser): ?>
                    <!-- Create Post Button -->
                    <a href="<?php echo SITE_URL; ?>/pages/create-post.php" class="btn-create-post">
                        <span class="plus-icon">+</span>
                        <span>Create Post</span>
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="profile-dropdown">
                        <button class="profile-trigger" onclick="toggleProfileMenu()" id="profileTrigger">
                            <?php if ($userProfilePic): ?>
                                <img src="<?php echo SITE_URL . htmlspecialchars($userProfilePic); ?>" 
                                     alt="Profile" 
                                     class="profile-avatar">
                            <?php else: ?>
                                <div class="profile-avatar-default">
                                    <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <span class="dropdown-arrow">▼</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="profile-menu" id="profileMenu">
                            <div class="profile-menu-header">
                                <div class="profile-menu-info">
                                    <div class="profile-menu-name"><?php echo htmlspecialchars($currentUser['username']); ?></div>
                                    <div class="profile-menu-email"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                                </div>
                                <?php if (isAdmin()): ?>
                                    <span class="admin-badge-small">ADMIN</span>
                                <?php endif; ?>
                            </div>

                            <div class="profile-menu-divider"></div>

                            <a href="<?php echo SITE_URL; ?>/pages/profile.php" class="profile-menu-item">
                                <span class="menu-item-icon">👤</span>
                                <span>My Profile</span>
                            </a>

                            <a href="<?php echo SITE_URL; ?>/pages/my-posts.php" class="profile-menu-item">
                                <span class="menu-item-icon">📝</span>
                                <span>My Posts</span>
                            </a>

                            <div class="profile-menu-divider"></div>

                            <a href="<?php echo SITE_URL; ?>/api/logout-handler.php" class="profile-menu-item profile-menu-item-danger">
                                <span class="menu-item-icon">🚪</span>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Not Logged In -->
                    <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn-auth btn-login">Login</a>
                    <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn-auth btn-signup">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        // Profile dropdown toggle
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const menu = document.getElementById('profileMenu');
            
            if (profileDropdown && !profileDropdown.contains(event.target)) {
                menu?.classList.remove('show');
            }
        });
    </script>

    
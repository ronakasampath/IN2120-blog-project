<?php
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
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Idea Canvas</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar-modern">
        <div class="container navbar-modern-content">
            <!-- Logo - Clickable to home -->
            <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-logo">
                <i class="fas fa-lightbulb logo-icon"></i>
                <span class="logo-text">Idea Canvas</span>
            </a>

            <!-- Center: Search -->
            <div class="navbar-center">
                <form action="<?php echo SITE_URL; ?>/pages/search.php" method="GET" class="search-form">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               name="q" 
                               placeholder="Search posts, authors, categories..." 
                               class="search-input"
                               value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    </div>
                </form>
            </div>

            <!-- Right: Actions -->
            <div class="navbar-actions">
                <?php if ($currentUser): ?>
                    <!-- Create Post Button -->
                    <a href="<?php echo SITE_URL; ?>/pages/create-post.php" class="btn-create-post">
                        <i class="fas fa-plus plus-icon"></i>
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
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
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
                                <i class="fas fa-user menu-item-icon"></i>
                                <span>My Profile</span>
                            </a>

                            <a href="<?php echo SITE_URL; ?>/pages/my-posts.php" class="profile-menu-item">
                                <i class="fas fa-file-alt menu-item-icon"></i>
                                <span>My Posts</span>
                            </a>

                            <div class="profile-menu-divider"></div>

                            <a href="<?php echo SITE_URL; ?>/api/logout-handler.php" class="profile-menu-item profile-menu-item-danger">
                                <i class="fas fa-sign-out-alt menu-item-icon"></i>
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
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const menu = document.getElementById('profileMenu');
            
            if (profileDropdown && !profileDropdown.contains(event.target)) {
                menu?.classList.remove('show');
            }
        });
    </script>
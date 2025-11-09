<?php
/**
 * Professional Header - Publication Design
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
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Idea Canvas</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
    /* Professional Publication Header */
    .navbar-modern {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }

    .navbar-modern-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 2rem;
        max-width: 1600px;
        margin: 0 auto;
        gap: 2.5rem;
    }

    /* Logo Section - Refined */
    .navbar-logo {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        text-decoration: none;
        color: #111827;
        transition: opacity 0.2s;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .navbar-logo:hover {
        opacity: 0.75;
    }

    .logo-icon {
        font-size: 1.625rem;
        color: #111827;
    }

    .logo-text {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        letter-spacing: -0.02em;
    }

    /* Right Section - Search + Actions */
    .navbar-right-section {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
        justify-content: flex-end;
    }

    /* Search Form - Minimal Design */
    .search-form {
        flex: 1;
        max-width: 480px;
    }

    .search-container {
        position: relative;
        width: 100%;
        height: 44px;
    }

    .search-input {
        width: 100%;
        height: 100%;
        padding: 0 3.5rem 0 1.125rem;
        border: 1px solid #d1d5db;
        border-radius: 24px;
        font-size: 0.9375rem;
        transition: all 0.2s;
        background: #f9fafb;
        color: #111827;
        box-sizing: border-box;
    }

    .search-input:focus {
        outline: none;
        border-color: #111827;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.08);
    }

    .search-input::placeholder {
        color: #9ca3af;
    }

    .search-btn {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        background: #111827;
        color: white;
        border: none;
        padding: 0 1rem;
        height: 32px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
    }

    .search-btn:hover {
        background: #1f2937;
        transform: translateY(-50%) scale(1.05);
    }

    .search-btn:active {
        transform: translateY(-50%) scale(0.95);
    }

    /* Create Post Button - Professional */
    .btn-create-post {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0 1.5rem;
        height: 44px;
        background: #111827;
        color: white;
        border-radius: 24px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
        box-sizing: border-box;
    }

    .btn-create-post:hover {
        background: #1f2937;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(17, 24, 39, 0.2);
    }

    .plus-icon {
        font-size: 1rem;
        font-weight: 700;
    }

    /* Auth Buttons - Minimal */
    .btn-auth {
        padding: 0 1.5rem;
        height: 44px;
        display: flex;
        align-items: center;
        border-radius: 24px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
        border: 1px solid transparent;
        font-size: 0.9375rem;
        white-space: nowrap;
        flex-shrink: 0;
        box-sizing: border-box;
    }

    .btn-login {
        color: #374151;
        background: transparent;
        border-color: #d1d5db;
    }

    .btn-login:hover {
        border-color: #111827;
        background: #f9fafb;
        color: #111827;
    }

    .btn-signup {
        color: white;
        background: #111827;
        border-color: #111827;
    }

    .btn-signup:hover {
        background: #1f2937;
        transform: translateY(-1px);
    }

    /* Profile Dropdown - Refined */
    .profile-dropdown {
        position: relative;
        flex-shrink: 0;
        height: 44px;
        display: flex;
        align-items: center;
    }

    .profile-trigger {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0 0.875rem;
        height: 44px;
        background: transparent;
        border: 1px solid #d1d5db;
        border-radius: 24px;
        cursor: pointer;
        transition: all 0.2s;
        box-sizing: border-box;
    }

    .profile-trigger:hover {
        border-color: #111827;
        background: #f9fafb;
    }

    .profile-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile-avatar-default {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #111827 0%, #374151 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .dropdown-arrow {
        font-size: 0.625rem;
        color: #6b7280;
        transition: transform 0.2s;
    }

    .profile-trigger:hover .dropdown-arrow {
        transform: translateY(2px);
    }

    /* Profile Menu Dropdown - Clean */
    .profile-menu {
        position: absolute;
        top: calc(100% + 0.75rem);
        right: 0;
        width: 280px;
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s;
        z-index: 1001;
    }

    .profile-menu.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .profile-menu-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .profile-menu-info {
        margin-bottom: 0.75rem;
    }

    .profile-menu-name {
        font-weight: 700;
        font-size: 1.125rem;
        color: #111827;
        margin-bottom: 0.375rem;
    }

    .profile-menu-email {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .admin-badge-small {
        display: inline-block;
        background: #111827;
        color: white;
        padding: 0.25rem 0.625rem;
        border-radius: 6px;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .profile-menu-divider {
        height: 1px;
        background: #e5e7eb;
        margin: 0;
    }

    .profile-menu-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s;
        font-weight: 500;
        font-size: 0.9375rem;
    }

    .profile-menu-item:hover {
        background: #f9fafb;
        color: #111827;
    }

    .profile-menu-item-danger {
        color: #dc2626;
    }

    .profile-menu-item-danger:hover {
        background: #fef2f2;
        color: #991b1b;
    }

    .menu-item-icon {
        font-size: 1.125rem;
        width: 20px;
        text-align: center;
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .navbar-modern-content {
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .navbar-right-section {
            flex: 1 1 100%;
            order: 3;
            max-width: none;
        }
        
        .search-form {
            max-width: none;
            flex: 1;
        }
    }

    @media (max-width: 768px) {
        .navbar-modern-content {
            padding: 0.875rem 1.25rem;
            gap: 1rem;
        }
        
        .logo-text {
            display: none;
        }
        
        .btn-create-post span {
            display: none;
        }
        
        .btn-create-post {
            padding: 0;
            width: 44px;
            justify-content: center;
        }
        
        .search-input {
            font-size: 0.875rem;
            padding-left: 1rem;
        }
        
        .profile-menu {
            width: 260px;
        }
        
        .navbar-right-section {
            gap: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .btn-auth {
            padding: 0 1.25rem;
            font-size: 0.875rem;
            height: 40px;
        }
        
        .navbar-modern-content {
            padding: 0.75rem 1rem;
        }
    }
    </style>
</head>
<body>
    <nav class="navbar-modern">
        <div class="navbar-modern-content">
            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-logo">
                <i class="fas fa-lightbulb logo-icon"></i>
                <span class="logo-text">Idea Canvas</span>
            </a>

            <!-- Right Section: Search + Create Post + Profile -->
            <div class="navbar-right-section">
                <!-- Search Form -->
                <form action="<?php echo SITE_URL; ?>/pages/search.php" method="GET" class="search-form">
                    <div class="search-container">
                        <input type="text" 
                               name="q" 
                               placeholder="Search posts, authors, categories..." 
                               class="search-input"
                               value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button type="submit" class="search-btn" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

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

        // Close menu on ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const menu = document.getElementById('profileMenu');
                menu?.classList.remove('show');
            }
        });
    </script>
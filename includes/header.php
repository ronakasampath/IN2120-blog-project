<?php
/**
 * Common Header for All Pages
 * Save as: includes/header.php
 * 
 * Usage: include __DIR__ . '/../includes/header.php';
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration if not loaded
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/database.php';
}

// Load authentication functions
require_once __DIR__ . '/auth.php';

// Get current user
$currentUser = getCurrentUser();
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
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-brand"><?php echo SITE_NAME; ?></a>
            <ul class="navbar-menu">
                <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                <?php if ($currentUser): ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/create-post.php">Create Post</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/my-posts.php">My Posts</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/profile.php">Profile</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/api/logout-handler.php">Logout (<?php echo htmlspecialchars($currentUser['username']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/login.php">Login</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
<?php
/**
 * Logout Handler
 * Save as: api/logout-handler.php
 * Logs out the user and redirects to the homepage.
 */

// Include authentication and configuration
require_once __DIR__ . '/../includes/auth.php';

// Call the logout function
logoutUser();

// Redirect to the homepage
header("Location: " . SITE_URL . "/index.php");
exit;
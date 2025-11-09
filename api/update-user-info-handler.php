<?php
/**
 * Update User Information Handler (WITH BIO SUPPORT - FIXED)
 * Save as: api/update-user-info-handler.php
 */

ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

ob_end_clean();

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

try {
    $db = getDB();
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];
    
    // Get and sanitize input
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    
    // Validate username
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    }
    
    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters']);
        exit;
    }
    
    if (strlen($username) > 50) {
        echo json_encode(['success' => false, 'message' => 'Username must be less than 50 characters']);
        exit;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_ ]+$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, underscores, and spaces']);
        exit;
    }
    
    // Validate email
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Validate bio length
    if (strlen($bio) > 500) {
        echo json_encode(['success' => false, 'message' => 'Bio must be less than 500 characters']);
        exit;
    }
    
    // Check if username is taken by another user
    $stmt = $db->prepare("SELECT id FROM user WHERE username = ? AND id != ?");
    $stmt->execute([$username, $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already taken']);
        exit;
    }
    
    // Check if email is taken by another user
    $stmt = $db->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already in use']);
        exit;
    }
    
    // Check if bio column exists, if not create it
    try {
        $stmt = $db->query("SHOW COLUMNS FROM user LIKE 'bio'");
        if ($stmt->rowCount() === 0) {
            // Bio column doesn't exist, create it
            $db->exec("ALTER TABLE user ADD COLUMN bio TEXT NULL AFTER email");
        }
    } catch (PDOException $e) {
        error_log("Bio column check error: " . $e->getMessage());
    }
    
    // Update user information
    $stmt = $db->prepare("UPDATE user SET username = ?, email = ?, bio = ? WHERE id = ?");
    $success = $stmt->execute([$username, $email, $bio, $userId]);
    
    if ($success) {
        // Update session with new username and email
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile updated successfully!'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    
} catch (PDOException $e) {
    error_log("Database error in update-user-info-handler.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error in update-user-info-handler.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

exit;
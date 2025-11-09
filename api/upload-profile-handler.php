<?php
/**
 * Upload Profile Picture Handler - FIXED
 * Save as: api/upload-profile-handler.php
 */

// Start output buffering to catch any stray output
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Clear any output
ob_end_clean();

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Verify CSRF token - check both function names
$csrfToken = $_POST['csrf_token'] ?? '';
$csrfValid = false;

if (function_exists('verifyCSRFToken')) {
    $csrfValid = verifyCSRFToken($csrfToken);
} elseif (function_exists('verifyCSRF')) {
    $csrfValid = verifyCSRF($csrfToken);
}

if (!$csrfValid) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$currentUser = getCurrentUser();

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

// Check for upload errors
if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File is too large',
        UPLOAD_ERR_FORM_SIZE => 'File is too large',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'Upload blocked by extension'
    ];
    
    $message = $errorMessages[$_FILES['profile_picture']['error']] ?? 'Unknown upload error';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

try {
    $file = $_FILES['profile_picture'];
    $db = getDB();
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed']);
        exit;
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5MB']);
        exit;
    }
    
    // Create uploads directory if it doesn't exist
    $uploadsDir = __DIR__ . '/../uploads/profile-pictures';
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $currentUser['id'] . '_' . time() . '.' . $extension;
    $filepath = $uploadsDir . '/' . $filename;
    $dbPath = '/uploads/profile-pictures/' . $filename;
    
    // Delete old profile picture if exists
    $stmt = $db->prepare("SELECT profile_picture FROM user WHERE id = ?");
    $stmt->execute([$currentUser['id']]);
    $user = $stmt->fetch();
    
    if ($user && $user['profile_picture']) {
        $oldFile = __DIR__ . '/..' . $user['profile_picture'];
        if (file_exists($oldFile)) {
            @unlink($oldFile);
        }
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit;
    }
    
    // Update database
    $stmt = $db->prepare("UPDATE user SET profile_picture = ? WHERE id = ?");
    $success = $stmt->execute([$dbPath, $currentUser['id']]);
    
    if ($success) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Profile picture updated successfully!',
            'profile_picture' => $dbPath
        ]);
    } else {
        // Delete uploaded file if database update failed
        @unlink($filepath);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
    
} catch (Exception $e) {
    error_log("Profile picture upload error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

exit;
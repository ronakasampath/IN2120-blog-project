<?php
/**
 * Delete Post Image Handler - FIXED
 * Save as: api/delete-image-handler.php
 */

// Start output buffering
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/image-functions.php';

// Clear any output
ob_end_clean();

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

$currentUser = getCurrentUser();

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

// Get image ID
$imageId = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;

if (!$imageId || $imageId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
    exit;
}

// Delete the image
try {
    $result = deletePostImage($imageId, $currentUser['id']);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log("Delete image error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

exit;
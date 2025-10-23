<?php
/**
 * Delete Post Handler - FULLY FIXED
 * Save as: api/delete-post-handler.php
 */

require_once __DIR__ . '/../config/database.php'; 
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Get current user (needs session)
$currentUser = getCurrentUser();

// Verify CSRF token (needs session)
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// NOW close session - we have all the data we need
session_write_close();

// Get post ID
$postId = intval($_POST['post_id'] ?? 0);

if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid Post ID']);
    exit;
}

// Delete post
$result = deletePost($postId, $currentUser['id']);

if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);
exit;
?>
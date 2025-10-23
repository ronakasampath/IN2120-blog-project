<?php
/**
 * Add Comment Handler - FIXED
 * Save as: api/add-comment-handler.php
 */

header('Content-Type: application/json');

// Load dependencies
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog-enhanced.php';

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to comment']);
    exit;
}

$currentUser = getCurrentUser();

// Verify CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

// Close session
session_write_close();

// Get data
$postId = intval($_POST['post_id'] ?? 0);
$commentText = trim($_POST['comment_text'] ?? '');

// Validate
if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

if (empty($commentText)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

// Add comment
$result = addComment($postId, $currentUser['id'], $commentText);

if ($result['success']) {
    http_response_code(201);
} else {
    http_response_code(400);
}

echo json_encode($result);
exit;
?>
<?php
/**
 * API Handler for uploading a featured image to a post.
 * Save as: api/upload-post-image-handler.php
 */

header('Content-Type: application/json');

// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php'; // Provides isLoggedIn(), getCurrentUser(), verifyCSRF()
require_once __DIR__ . '/../includes/blog.php'; // Required for getPost()
require_once __DIR__ . '/../includes/blog-enhanced.php'; // Required for addPostImage()

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required to upload images.']);
    exit;
}

// Check CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$currentUser = getCurrentUser();
session_write_close();

// Get POST data and file data
$postId = intval($_POST['post_id'] ?? 0);
$file = $_FILES['featured_image'] ?? null;

// Validate input
if (!$postId || !$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing post ID or no file selected for upload.']);
    exit;
}

// Check authorization (Must be the post author or an admin)
$post = getPost($postId);
if (!$post || ($post['user_id'] != $currentUser['id'] && !isAdmin())) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Post not found or unauthorized to edit its image.']);
    exit;
}

// Call the image upload function defined in blog-enhanced.php
$result = addPostImage($postId, $file);

if ($result['success']) {
    http_response_code(200);
} else {
    // Determine appropriate error code
    $errorCode = (strpos($result['message'], 'size') !== false || strpos($result['message'], 'type') !== false) ? 400 : 500;
    http_response_code($errorCode);
}

echo json_encode($result);
exit;
?>

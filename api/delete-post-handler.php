<?php
/**
 * Delete Post Handler
 * Save as: api/delete-post-handler.php
 * Handles blog post deletion via POST request.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';

// Set content type to JSON
header('Content-Type: application/json');

// 1. Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// 2. Check if the user is logged in
$currentUser = getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required to delete a post.']);
    exit;
}

// 3. Verify CSRF token
$csrfToken = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
if (!verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token. Request rejected.']);
    exit;
}

// 4. Get input
$postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);

if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid Post ID.']);
    exit;
}

// 5. Call the deletePost function (handles ownership check internally)
$result = deletePost($postId, $currentUser['id']);

// 6. Return JSON response
if ($result['success']) {
    http_response_code(200);
} else {
    // 403 for unauthorized, 404 for not found, 400 for errors
    $statusCode = 400; 
    if (isset($result['message']) && $result['message'] === 'Unauthorized') {
        $statusCode = 403;
    } else if (isset($result['message']) && $result['message'] === 'Post not found') {
        $statusCode = 404;
    }
    http_response_code($statusCode);
}

echo json_encode($result);
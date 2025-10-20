<?php
/**
 * Update Post Handler
 * Save as: api/update-post-handler.php
 * Handles updates to an existing blog post via POST request.
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
    echo json_encode(['success' => false, 'message' => 'Authentication required to edit a post.']);
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
$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
$content = $_POST['content'] ?? '';

if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid Post ID.']);
    exit;
}

// 5. Call the updatePost function (handles ownership check internally)
$result = updatePost($postId, $currentUser['id'], $title, $content);

// 6. Return JSON response
if ($result['success']) {
    http_response_code(200);
} else {
    // 403 for unauthorized, 404 for not found, 400 for validation
    $statusCode = (isset($result['message']) && $result['message'] === 'Unauthorized') ? 403 : 400;
    http_response_code($statusCode);
}

echo json_encode($result);
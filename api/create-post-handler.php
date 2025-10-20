<?php
/**
 * Create Post Handler
 * Save as: api/create-post-handler.php
 * Handles new blog post creation via POST request.
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
    echo json_encode(['success' => false, 'message' => 'Authentication required to create a post.']);
    exit;
}

// 3. Verify CSRF token
$csrfToken = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
if (!verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token. Request rejected.']);
    exit;
}

// 4. Get and sanitize input
$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
$content = $_POST['content'] ?? ''; // Content can contain markdown/HTML, so don't sanitize aggressively

// 5. Call the createPost function
$result = createPost($currentUser['id'], $title, $content);

// 6. Return JSON response
if ($result['success']) {
    http_response_code(201); // Created
} else {
    http_response_code(400); // Bad Request for validation errors
}

echo json_encode($result);
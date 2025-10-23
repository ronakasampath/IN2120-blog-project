<?php
/**
 * Create Post Handler (Improved Debug Version)
 * Save as: api/create-post-handler.php
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';
header('Content-Type: application/json');

// 1️⃣ Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// 2️⃣ Authentication check
$currentUser = getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to create a post.']);
    exit;
}

// 3️⃣ CSRF validation
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

// 4️⃣ Read data
$title = trim($_POST['title'] ?? '');
$content = $_POST['content'] ?? '';

if (empty($title) || empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title and content are required.']);
    exit;
}

// 5️⃣ Create post
$result = createPost($currentUser['id'], $title, $content);

// 6️⃣ Response
if ($result['success']) {
    http_response_code(201);
    echo json_encode($result);
} else {
    error_log("POST CREATION FAILED: " . $result['message']);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $result['message']]);
}

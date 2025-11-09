<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to create a post.']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$content = $_POST['content'] ?? '';
$category = trim($_POST['category'] ?? 'general');

if (empty($title) || empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title and content are required.']);
    exit;
}

// Sanitize category
$category = strtolower(preg_replace('/[^a-z0-9\s-]/', '', $category));
$category = str_replace(' ', '-', $category);

if (empty($category)) {
    $category = 'general';
}

$result = createPost($currentUser['id'], $title, $content, $category);

if ($result['success']) {
    http_response_code(201);
    echo json_encode($result);
} else {
    error_log("POST CREATION FAILED: " . $result['message']);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
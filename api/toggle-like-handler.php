<?php
/**
 * Toggle Like Handler
 * Save as: api/toggle-like-handler.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog-enhanced.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to like']);
    exit;
}

$currentUser = getCurrentUser();
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

session_write_close();

$postId = intval($_POST['post_id'] ?? 0);

$result = toggleLike($postId, $currentUser['id']);

// Add current like count to response
if ($result['success']) {
    $result['like_count'] = getLikeCount($postId);
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);
exit;
?>
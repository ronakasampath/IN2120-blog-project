<?php
/**
 * Delete Post Image Handler
 * Save as: api/delete-image-handler.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/image-functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

$currentUser = getCurrentUser();
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

session_write_close();

$imageId = intval($_POST['image_id'] ?? 0);

if (!$imageId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
    exit;
}

$result = deletePostImage($imageId, $currentUser['id']);

if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);
exit;
?>
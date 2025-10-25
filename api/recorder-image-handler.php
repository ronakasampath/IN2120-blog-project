<?php
/**
 * Reorder Post Images Handler
 * Save as: api/reorder-images-handler.php
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

$postId = intval($_POST['post_id'] ?? 0);
$imageIdsJson = $_POST['image_ids'] ?? '';

if (!$postId || empty($imageIdsJson)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Verify ownership
$db = getDB();
$stmt = $db->prepare("SELECT user_id FROM blogPost WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post || ($post['user_id'] != $currentUser['id'] && !isAdmin())) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$imageIds = json_decode($imageIdsJson, true);
if (!is_array($imageIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid image IDs']);
    exit;
}

$result = reorderPostImages($postId, $imageIds);

if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);
exit;
?>
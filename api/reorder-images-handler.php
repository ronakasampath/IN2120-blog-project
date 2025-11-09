<?php
/**
 * Reorder Images Handler - FIXED
 * Save as: api/reorder-images-handler.php
 */

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

$currentUser = getCurrentUser();

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$postId = intval($_POST['post_id'] ?? 0);
$orderJson = $_POST['order'] ?? '';

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

if (!$orderJson) {
    echo json_encode(['success' => false, 'message' => 'No order data provided']);
    exit;
}

$order = json_decode($orderJson, true);
if (!is_array($order)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

try {
    $db = getDB();
    
    // Verify post ownership
    $stmt = $db->prepare("SELECT user_id FROM blogpost WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit;
    }
    
    if ($post['user_id'] != $currentUser['id'] && !isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        exit;
    }
    
    // Update image_order for each image
    $db->beginTransaction();
    
    $updateStmt = $db->prepare("
        UPDATE post_images 
        SET image_order = ? 
        WHERE id = ? AND post_id = ?
    ");
    
    foreach ($order as $item) {
        $imageId = intval($item['id']);
        $imageOrder = intval($item['order']);
        $updateStmt->execute([$imageOrder, $imageId, $postId]);
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Image order updated successfully'
    ]);
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
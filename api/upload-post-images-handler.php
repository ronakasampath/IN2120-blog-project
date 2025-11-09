<?php
/**
 * Upload Post Images Handler - WORKING VERSION
 * Save as: api/upload-post-images-handler.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/image-functions.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load dependencies: ' . $e->getMessage()]);
    exit;
}

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$currentUser = getCurrentUser();

// Check CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

session_write_close();

// Get post ID
$postId = intval($_POST['post_id'] ?? 0);

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

// Verify ownership
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT user_id FROM blogpost WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit;
    }
    
    if ($post['user_id'] != $currentUser['id'] && !isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

// Check files
if (!isset($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    echo json_encode(['success' => false, 'message' => 'No images uploaded']);
    exit;
}

// Upload images
try {
    $result = uploadPostImages($postId, $_FILES['images']);
    
    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $e->getMessage()]);
}

exit;
?>
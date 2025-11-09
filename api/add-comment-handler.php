<?php
/**
 * Add Comment Handler - FIXED WITH BETTER ERROR HANDLING
 * Save as: api/add-comment-handler.php
 */

// Start output buffering
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog-enhanced.php';

// Clear any output
ob_end_clean();

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to comment']);
    exit;
}

$currentUser = getCurrentUser();

// Verify CSRF - check both function names
$csrfToken = $_POST['csrf_token'] ?? '';
$csrfValid = false;

if (function_exists('verifyCSRFToken')) {
    $csrfValid = verifyCSRFToken($csrfToken);
} elseif (function_exists('verifyCSRF')) {
    $csrfValid = verifyCSRF($csrfToken);
}

if (!$csrfValid) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

// Get data
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$commentText = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';

// Validate post ID
if (!$postId || $postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

// Validate comment text
if (empty($commentText)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

if (strlen($commentText) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment is too long (max 1000 characters)']);
    exit;
}

// Check if post exists
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM blogpost WHERE id = ?");
    $stmt->execute([$postId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit;
    }
} catch (PDOException $e) {
    error_log("Error checking post: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

// Add comment
try {
    $result = addComment($postId, $currentUser['id'], $commentText);
    
    if ($result['success']) {
        http_response_code(201);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log("Error adding comment: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to add comment',
        'error' => $e->getMessage()
    ]);
}

exit;
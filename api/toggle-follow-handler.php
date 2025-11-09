<?php
/**
 * Toggle Follow Handler - DEBUGGED VERSION
 * Save as: api/toggle-follow-handler.php
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Log the start
error_log("=== TOGGLE FOLLOW HANDLER START ===");

try {
    require_once __DIR__ . '/../config/database.php';
    error_log("✓ Database config loaded");
} catch (Exception $e) {
    error_log("✗ Failed to load database.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Config error: ' . $e->getMessage()]);
    exit;
}

try {
    require_once __DIR__ . '/../includes/auth.php';
    error_log("✓ Auth loaded");
} catch (Exception $e) {
    error_log("✗ Failed to load auth.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Auth error: ' . $e->getMessage()]);
    exit;
}

try {
    require_once __DIR__ . '/../includes/follow-functions.php';
    error_log("✓ Follow functions loaded");
} catch (Exception $e) {
    error_log("✗ Failed to load follow-functions.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Follow functions error: ' . $e->getMessage()]);
    exit;
}

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("✗ Wrong method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

error_log("✓ Method is POST");

// Check login
if (!isLoggedIn()) {
    error_log("✗ User not logged in");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to follow users']);
    exit;
}

error_log("✓ User is logged in");

$currentUser = getCurrentUser();
error_log("Current user: " . json_encode($currentUser));

// Check CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
error_log("CSRF token received: " . substr($csrfToken, 0, 10) . "...");
error_log("Session CSRF: " . substr($_SESSION['csrf_token'] ?? '', 0, 10) . "...");

if (!verifyCSRF($csrfToken)) {
    error_log("✗ CSRF verification failed");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token - please refresh the page']);
    exit;
}

error_log("✓ CSRF verified");

// Close session
session_write_close();

// Get target user ID
$targetUserId = intval($_POST['user_id'] ?? 0);
error_log("Target user ID: " . $targetUserId);

if (!$targetUserId) {
    error_log("✗ Invalid user ID");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

error_log("✓ Target user ID valid");

// Check not following self
if ($targetUserId == $currentUser['id']) {
    error_log("✗ User trying to follow themselves");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot follow yourself']);
    exit;
}

error_log("✓ Not following self");

// Check if target user exists
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM user WHERE id = ?");
    $stmt->execute([$targetUserId]);
    if (!$stmt->fetch()) {
        error_log("✗ Target user not found");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    error_log("✓ Target user exists");
} catch (PDOException $e) {
    error_log("✗ Database error checking user: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

// Check if followers table exists
try {
    $stmt = $db->query("SHOW TABLES LIKE 'followers'");
    if ($stmt->rowCount() === 0) {
        error_log("✗ FOLLOWERS TABLE DOES NOT EXIST!");
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Follow system not set up. Please run database migration.',
            'error' => 'followers_table_missing'
        ]);
        exit;
    }
    error_log("✓ Followers table exists");
} catch (PDOException $e) {
    error_log("✗ Error checking followers table: " . $e->getMessage());
}

// Check if already following
try {
    $isCurrentlyFollowing = isFollowing($currentUser['id'], $targetUserId);
    error_log("Currently following: " . ($isCurrentlyFollowing ? 'YES' : 'NO'));
} catch (Exception $e) {
    error_log("✗ Error in isFollowing: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error checking follow status: ' . $e->getMessage()]);
    exit;
}

// Toggle follow
try {
    if ($isCurrentlyFollowing) {
        error_log("Attempting to UNFOLLOW...");
        $result = unfollowUser($currentUser['id'], $targetUserId);
        $result['following'] = false;
    } else {
        error_log("Attempting to FOLLOW...");
        $result = followUser($currentUser['id'], $targetUserId);
        $result['following'] = true;
    }
    
    error_log("Follow action result: " . json_encode($result));
} catch (Exception $e) {
    error_log("✗ Error in follow/unfollow: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Follow action failed: ' . $e->getMessage()]);
    exit;
}

// Get updated follower count
try {
    $result['follower_count'] = getFollowerCount($targetUserId);
    error_log("Follower count: " . $result['follower_count']);
} catch (Exception $e) {
    error_log("✗ Error getting follower count: " . $e->getMessage());
    $result['follower_count'] = 0;
}

// Send response
if ($result['success']) {
    error_log("✓ SUCCESS: " . json_encode($result));
    http_response_code(200);
} else {
    error_log("✗ FAILED: " . json_encode($result));
    http_response_code(400);
}

echo json_encode($result);
error_log("=== TOGGLE FOLLOW HANDLER END ===");
exit;
?>
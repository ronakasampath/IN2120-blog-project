<?php
/**
 * Login Handler - FULLY FIXED VERSION
 * Save as: api/login-handler.php
 */

// Load dependencies
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username)) {
    error_log("LOGIN ERROR: Empty username");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username or email is required']);
    exit;
}

if (empty($password)) {
    error_log("LOGIN ERROR: Empty password");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password is required']);
    exit;
}

// Log attempt
error_log("LOGIN ATTEMPT: username/email = '$username'");

try {
    $db = getDB();
    
    // Find user by username OR email
    $stmt = $db->prepare("SELECT id, username, email, password, role FROM user WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        error_log("LOGIN ERROR: User not found for '$username'");
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username/email or password']);
        exit;
    }
    
    error_log("LOGIN: User found - ID: {$user['id']}, Username: {$user['username']}");
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        error_log("LOGIN ERROR: Password verification failed for user '{$user['username']}'");
        error_log("LOGIN ERROR: Hash in DB: " . substr($user['password'], 0, 30) . "...");
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username/email or password']);
        exit;
    }
    
    error_log("LOGIN: Password verified successfully for '{$user['username']}'");
    
    // Set session data
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['last_activity'] = time();
    
    // Generate CSRF token
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    error_log("LOGIN SUCCESS: User '{$user['username']}' logged in successfully");
    error_log("LOGIN SUCCESS: Session ID = " . session_id());
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("LOGIN ERROR: Database exception - " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
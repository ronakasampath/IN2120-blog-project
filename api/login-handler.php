<?php
/**
 * Login Handler (Improved Debug Version)
 * Save as: api/login-handler.php
 */

require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

// 1️⃣ Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// 2️⃣ Input validation
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username/Email and Password are required.']);
    exit;
}

// 3️⃣ Try login
$result = loginUser($username, $password);

// 4️⃣ Return proper response
if ($result['success']) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Login successful!']);
} else {
    // Extra info logged for debugging
    error_log("LOGIN FAILED: " . $result['message']);
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $result['message']]);
}

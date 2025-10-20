<?php
/**
 * Login Handler
 * Save as: api/login-handler.php
 * Handles user login via POST request.
 */

// Include authentication functions
require_once __DIR__ . '/../includes/auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get input: username can be username or email (handled by loginUser function)
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password');

// Basic input validation
if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username/Email and Password are required.']);
    exit;
}

// Call the login function
$result = loginUser($username, $password);

// Return JSON response
if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(401); // Unauthorized for invalid credentials
}

echo json_encode($result);
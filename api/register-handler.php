<?php
/**
 * Registration Handler
 * Save as: api/register-handler.php
 */

header('Content-Type: application/json');

require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$result = registerUser($username, $email, $password);
echo json_encode($result);
?>
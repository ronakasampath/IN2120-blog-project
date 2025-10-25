<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

$currentUser = getCurrentUser();
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRF($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');

if (strlen($username) < 3) {
    echo json_encode(['success' => false, 'message' => 'Username too short']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

$db = getDB();

// Check if username/email taken by others
$stmt = $db->prepare("SELECT id FROM user WHERE (username = ? OR email = ?) AND id != ?");
$stmt->execute([$username, $email, $currentUser['id']]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Username or email already taken']);
    exit;
}

// Update
$stmt = $db->prepare("UPDATE user SET username = ?, email = ? WHERE id = ?");
$stmt->execute([$username, $email, $currentUser['id']]);

$_SESSION['username'] = $username;
$_SESSION['email'] = $email;

echo json_encode(['success' => true, 'message' => 'Profile updated!']);
?>
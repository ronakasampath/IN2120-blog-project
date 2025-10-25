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

$currentPass = $_POST['current_password'] ?? '';
$newPass = $_POST['new_password'] ?? '';

if (strlen($newPass) < 6) {
    echo json_encode(['success' => false, 'message' => 'New password too short']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT password FROM user WHERE id = ?");
$stmt->execute([$currentUser['id']]);
$user = $stmt->fetch();

if (!password_verify($currentPass, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password incorrect']);
    exit;
}

$hashedPassword = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
$stmt = $db->prepare("UPDATE user SET password = ? WHERE id = ?");
$stmt->execute([$hashedPassword, $currentUser['id']]);

echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
?>
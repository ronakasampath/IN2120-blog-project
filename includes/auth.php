<?php
/**
 * Authentication Functions - FINAL CORRECTED VERSION (Using getDB() function)
 * Save as: includes/auth.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FIX 1: Require database.php using the correct path (up from 'includes', into 'config')
require_once __DIR__ . '/../config/database.php';


/**
 * Register new user
 */
function registerUser($username, $email, $password) {
    $db = getDB(); // FIX 2: Use getDB()
    $errors = [];
    
    // Validate
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'message' => implode(', ', $errors)];
    }
    
    // Check if exists
    try {
        $stmt = $db->prepare("SELECT id FROM user WHERE username = ? OR email = ?"); // Use $db
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Insert user
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]); 
        $stmt = $db->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, 'user')"); // Use $db
        $stmt->execute([$username, $email, $hashedPassword]);
        
        return ['success' => true, 'message' => 'Registration successful!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Login user
 */
function loginUser($username, $password) {
    $db = getDB(); // FIX 2: Use getDB()
    
    try {
        $stmt = $db->prepare("SELECT * FROM user WHERE username = ? OR email = ?"); // Use $db
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Set session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        return ['success' => true, 'message' => 'Login successful!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Login failed'];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    // Note: This function correctly uses session/cookie management only.
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

/**
 * Check if logged in
 */
function isLoggedIn() {
    // Note: SESSION_LIFETIME is defined in database.php
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        logoutUser();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Require login
 */
function requireLogin() {
    // Note: SITE_URL is defined in database.php
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "/pages/login.php");
        exit;
    }
}

/**
 * Get CSRF token
 */
function getCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
<?php
/**
 * Reset Test User Password
 * Save as: reset-test-user.php (in root)
 * Visit: http://localhost/Idea-canvas/reset-test-user.php
 * 
 * This will fix login issues by resetting the password
 */

require_once 'config/database.php';

echo "<h1>Reset Test User Password</h1>";

$testUsername = 'testuser';
$testEmail = 'test@example.com';
$testPassword = 'password123';

try {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, username, email FROM user WHERE username = ? OR email = ?");
    $stmt->execute([$testUsername, $testEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        // User exists - UPDATE password
        echo "<p style='color: blue;'>User '<strong>" . htmlspecialchars($user['username']) . "</strong>' exists. Updating password...</p>";
        
        $hashedPassword = password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $db->prepare("UPDATE user SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);
        
    echo "<p style='color: green;'>Password reset successful!</p>";
        echo "<p style='color: green;'>Password reset successful!</p>";
        
    } else {
        // User doesn't exist - CREATE new user
        echo "<p style='color: blue;'>User doesn't exist. Creating new test user...</p>";
        
        $hashedPassword = password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $db->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$testUsername, $testEmail, $hashedPassword]);
        
    echo "<p style='color: green;'>Test user created successfully!</p>";
        echo "<p style='color: green;'>Test user created successfully!</p>";
    }
    
    // Verify the password works
    $stmt = $db->prepare("SELECT id, username, email, password FROM user WHERE username = ?");
    $stmt->execute([$testUsername]);
    $verifyUser = $stmt->fetch();
    
    if (password_verify($testPassword, $verifyUser['password'])) {
           echo "<p style='color: green;'>Password verification: <strong>WORKING</strong></p>";
    } else {
        echo "<p style='color: red;'>Password verification: <strong>FAILED</strong></p>";
    }
    
    echo "<hr>";
    echo "<h2>You can now login with:</h2>";
        echo "<h2>You can now login with:</h2>";
    echo "<ul style='font-size: 1.2rem;'>";
    echo "<li><strong>Username:</strong> testuser</li>";
    echo "<li><strong>Email:</strong> test@example.com</li>";
    echo "<li><strong>Password:</strong> password123</li>";
    echo "</ul>";
    
    echo "<p><a href='pages/login.php' style='display: inline-block; padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page →</a></p>";
    
    echo "<hr>";
    echo "<h3>All Users in Database:</h3>";
    $stmt = $db->query("SELECT id, username, email, role, created_at FROM user");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th></tr>";
    while ($u = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . $u['id'] . "</td>";
        echo "<td>" . htmlspecialchars($u['username']) . "</td>";
        echo "<td>" . htmlspecialchars($u['email']) . "</td>";
        echo "<td>" . htmlspecialchars($u['role']) . "</td>";
        echo "<td>" . $u['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color: red; margin-top: 20px;'><strong>IMPORTANT:</strong> Delete this file after testing!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure:</p>";
    echo "<ul>";
    echo "<li>XAMPP MySQL is running</li>";
    echo "<li>Database 'blog_application' exists</li>";
    echo "<li>User table exists</li>";
    echo "</ul>";
}
?>
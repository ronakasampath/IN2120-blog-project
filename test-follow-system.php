<?php
/**
 * Test Follow System Setup
 * Save as: test-follow-system.php (in root)
 * Visit: http://localhost/Idea-canvas/test-follow-system.php
 */

require_once 'config/database.php';

echo "<h1>Follow System Test</h1>";

echo "<style>
    body { font-family: Arial, sans-serif; padding: 2rem; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    table { border-collapse: collapse; margin: 1rem 0; }
    td, th { padding: 0.5rem; border: 1px solid #ddd; text-align: left; }
    th { background: #f3f4f6; }
</style>";

$db = getDB();
$errors = [];

// Test 1: Check if followers table exists
echo "<h2>Test 1: Followers Table</h2>";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'followers'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✓ Followers table exists</p>";
        
        // Show structure
        $stmt = $db->query("DESCRIBE followers");
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ Followers table does NOT exist</p>";
        echo "<p class='warning'>⚠ You need to run the database migration SQL!</p>";
        $errors[] = 'followers_table_missing';
    }
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    $errors[] = 'followers_check_failed';
}

// Test 2: Check if user table has bio column
echo "<h2>Test 2: User Table Columns</h2>";
try {
    $stmt = $db->query("DESCRIBE user");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['id', 'username', 'email', 'password', 'bio', 'profile_picture', 'role'];
    
    echo "<table>";
    echo "<tr><th>Column</th><th>Status</th></tr>";
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columns);
        $status = $exists ? "<span class='success'>✓ Exists</span>" : "<span class='error'>✗ Missing</span>";
        echo "<tr><td>$col</td><td>$status</td></tr>";
        if (!$exists) {
            $errors[] = "user_column_missing_$col";
        }
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 3: Check if follow-functions.php exists
echo "<h2>Test 3: Follow Functions File</h2>";
if (file_exists('includes/follow-functions.php')) {
    echo "<p class='success'>✓ includes/follow-functions.php exists</p>";
    
    require_once 'includes/follow-functions.php';
    
    // Test if functions are defined
    $functions = ['isFollowing', 'followUser', 'unfollowUser', 'getFollowerCount', 'getFollowingCount'];
    echo "<table>";
    echo "<tr><th>Function</th><th>Status</th></tr>";
    foreach ($functions as $func) {
        $exists = function_exists($func);
        $status = $exists ? "<span class='success'>✓ Defined</span>" : "<span class='error'>✗ Missing</span>";
        echo "<tr><td>$func()</td><td>$status</td></tr>";
        if (!$exists) {
            $errors[] = "function_missing_$func";
        }
    }
    echo "</table>";
} else {
    echo "<p class='error'>✗ includes/follow-functions.php does NOT exist</p>";
    $errors[] = 'follow_functions_missing';
}

// Test 4: Check API handler
echo "<h2>Test 4: API Handler</h2>";
if (file_exists('api/toggle-follow-handler.php')) {
    echo "<p class='success'>✓ api/toggle-follow-handler.php exists</p>";
} else {
    echo "<p class='error'>✗ api/toggle-follow-handler.php does NOT exist</p>";
    $errors[] = 'api_handler_missing';
}

// Test 5: Try test follow operation (if all OK)
if (empty($errors)) {
    echo "<h2>Test 5: Test Follow Operation</h2>";
    
    // Get two test users
    $stmt = $db->query("SELECT id, username FROM user LIMIT 2");
    $users = $stmt->fetchAll();
    
    if (count($users) >= 2) {
        $user1 = $users[0];
        $user2 = $users[1];
        
        echo "<p>Testing: User '{$user1['username']}' (ID: {$user1['id']}) following User '{$user2['username']}' (ID: {$user2['id']})</p>";
        
        try {
            // Test follow
            $result = followUser($user1['id'], $user2['id']);
            echo "<p class='success'>✓ Follow test: " . $result['message'] . "</p>";
            
            // Check if following
            $isFollowing = isFollowing($user1['id'], $user2['id']);
            echo "<p class='success'>✓ Is following check: " . ($isFollowing ? 'YES' : 'NO') . "</p>";
            
            // Get follower count
            $count = getFollowerCount($user2['id']);
            echo "<p class='success'>✓ Follower count: $count</p>";
            
            // Test unfollow
            $result = unfollowUser($user1['id'], $user2['id']);
            echo "<p class='success'>✓ Unfollow test: " . $result['message'] . "</p>";
            
        } catch (Exception $e) {
            echo "<p class='error'>✗ Test failed: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='warning'>⚠ Need at least 2 users to test follow system</p>";
    }
}

// Summary
echo "<hr>";
echo "<h2>Summary</h2>";
if (empty($errors)) {
    echo "<p class='success' style='font-size: 1.5rem;'>✓ All tests passed! Your follow system is ready to use.</p>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
} else {
    echo "<p class='error' style='font-size: 1.5rem;'>✗ " . count($errors) . " issue(s) found</p>";
    echo "<p class='warning'>You need to run the database migration SQL script.</p>";
    echo "<ol>";
    echo "<li>Open phpMyAdmin</li>";
    echo "<li>Select 'blog_application' database</li>";
    echo "<li>Go to SQL tab</li>";
    echo "<li>Run the complete-database-migration.sql</li>";
    echo "</ol>";
}

echo "<p style='color: red; margin-top: 2rem;'><strong>Delete this file after testing!</strong></p>";
?>
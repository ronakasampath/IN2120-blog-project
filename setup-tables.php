<?php
/**
 * Database Setup Script
 * Visit: http://localhost/blog-app/setup-tables.php
 * Delete this file after running!
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Database Setup</h1>";
echo "<p>Setting up tables...</p>";

$db = getDB();

try {
    // Create followers table
    $sql1 = "CREATE TABLE IF NOT EXISTS followers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        follower_id INT NOT NULL,
        followed_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_follow (follower_id, followed_id),
        FOREIGN KEY (follower_id) REFERENCES user(id) ON DELETE CASCADE,
        FOREIGN KEY (followed_id) REFERENCES user(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql1);
    echo "<p style='color: green;'>Followers table created successfully!</p>";
    
    // Create tutorial_progress table
    $sql2 = "CREATE TABLE IF NOT EXISTS tutorial_progress (
        user_id INT PRIMARY KEY,
        completed BOOLEAN DEFAULT FALSE,
        step INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql2);
    echo "<p style='color: green;'>Tutorial_progress table created successfully!</p>";
    
    // Show all tables
    $stmt = $db->query("SHOW TABLES");
    echo "<h2>All Tables in Database:</h2>";
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
    
    echo "<h2 style='color: green;'>Setup Complete!</h2>";
    echo "<p><strong>Important:</strong> Please delete this file (setup-tables.php) now for security!</p>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
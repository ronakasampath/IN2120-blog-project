<?php
require_once __DIR__ . '/config/database.php';

echo "<h1>Creating Tables</h1>";

$db = getDB();

try {
    // Create followers table
    $db->exec("CREATE TABLE IF NOT EXISTS followers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        follower_id INT NOT NULL,
        followed_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_follow (follower_id, followed_id),
        FOREIGN KEY (follower_id) REFERENCES user(id) ON DELETE CASCADE,
        FOREIGN KEY (followed_id) REFERENCES user(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "<p style='color: green;'>Followers table created</p>";
    
    // Create tutorial_progress table
    $db->exec("CREATE TABLE IF NOT EXISTS tutorial_progress (
        user_id INT PRIMARY KEY,
        completed BOOLEAN DEFAULT FALSE,
        step INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "<p style='color: green;'>Tutorial_progress table created</p>";
    
    // Show all tables
    $stmt = $db->query("SHOW TABLES");
    echo "<h2>All Tables:</h2><ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    // Check if user exists
    $stmt = $db->query("SELECT username, email FROM user LIMIT 5");
    echo "<h2>Sample Users:</h2><ul>";
    while ($user = $stmt->fetch()) {
        echo "<li>Username: " . $user['username'] . " | Email: " . $user['email'] . "</li>";
    }
    echo "</ul>";
    
    echo "<h2 style='color: green;'>Done! Delete this file now.</h2>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
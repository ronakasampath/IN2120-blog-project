<?php
require_once 'config/database.php';

$db = getDB();

try {
    // Check if category column exists
    $stmt = $db->query("SHOW COLUMNS FROM blogpost LIKE 'category'");
    if ($stmt->rowCount() == 0) {
        // Add category column
        $db->exec("ALTER TABLE blogpost ADD COLUMN category VARCHAR(50) DEFAULT 'general' AFTER content");
        echo "<p style='color: green;'>✓ Category column added successfully!</p>";
    } else {
        echo "<p style='color: blue;'>Category column already exists.</p>";
    }
    
    // Update existing posts to have 'general' category
    $db->exec("UPDATE blogpost SET category = 'general' WHERE category IS NULL OR category = ''");
    echo "<p style='color: green;'>✓ Updated existing posts with default category.</p>";
    
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    echo "<p style='color: red;'><strong>Delete this file after running!</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
<?php
/**
 * Fix Database Tables
 * Save as: fix-database.php (in root)
 * Visit: http://localhost/Idea-canvas/fix-database.php
 */

require_once 'config/database.php';

echo "<!DOCTYPE html><html><head><title>Fix Database</title></head><body>";
echo "<h1>Fixing Database Structure...</h1>";

try {
    $db = getDB();
    
    // 1. Add category column to blogPost if missing
    echo "<h2>1. Checking blogPost table...</h2>";
    try {
        $db->exec("ALTER TABLE blogPost ADD COLUMN category VARCHAR(50) DEFAULT NULL");
        echo "<p style='color: green;'>✅ Added 'category' column to blogPost</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color: blue;'>ℹ️ Category column already exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
    
    // 2. Check if post_images table exists
    echo "<h2>2. Checking post_images table...</h2>";
    $stmt = $db->query("SHOW TABLES LIKE 'post_images'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "<p style='color: orange;'>⚠️ post_images table doesn't exist. Creating it...</p>";
        
        $db->exec("CREATE TABLE post_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            image_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES blogPost(id) ON DELETE CASCADE,
            INDEX idx_post_id (post_id),
            INDEX idx_order (image_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        echo "<p style='color: green;'>✅ Created post_images table</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ post_images table already exists</p>";
        
        // Check column name
        $stmt = $db->query("SHOW COLUMNS FROM post_images LIKE 'image_order'");
        $hasImageOrder = $stmt->fetch();
        
        $stmt = $db->query("SHOW COLUMNS FROM post_images LIKE 'display_order'");
        $hasDisplayOrder = $stmt->fetch();
        
        if ($hasDisplayOrder && !$hasImageOrder) {
            echo "<p style='color: orange;'>⚠️ Found 'display_order' column, renaming to 'image_order'...</p>";
            $db->exec("ALTER TABLE post_images CHANGE display_order image_order INT DEFAULT 0");
            echo "<p style='color: green;'>✅ Renamed column</p>";
        } elseif (!$hasImageOrder && !$hasDisplayOrder) {
            echo "<p style='color: orange;'>⚠️ Adding 'image_order' column...</p>";
            $db->exec("ALTER TABLE post_images ADD COLUMN image_order INT DEFAULT 0");
            echo "<p style='color: green;'>✅ Added image_order column</p>";
        } else {
            echo "<p style='color: green;'>✅ image_order column exists</p>";
        }
    }
    
    // 3. Show final table structure
    echo "<h2>3. Final Table Structure:</h2>";
    
    echo "<h3>blogPost columns:</h3><ul>";
    $stmt = $db->query("SHOW COLUMNS FROM blogPost");
    while ($col = $stmt->fetch()) {
        echo "<li><strong>{$col['Field']}</strong> - {$col['Type']}</li>";
    }
    echo "</ul>";
    
    echo "<h3>post_images columns:</h3><ul>";
    $stmt = $db->query("SHOW COLUMNS FROM post_images");
    while ($col = $stmt->fetch()) {
        echo "<li><strong>{$col['Field']}</strong> - {$col['Type']}</li>";
    }
    echo "</ul>";
    
    // 4. Show sample data
    echo "<h2>4. Current Data:</h2>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM blogPost");
    $result = $stmt->fetch();
    echo "<p>Total posts: <strong>{$result['count']}</strong></p>";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM post_images");
    $result = $stmt->fetch();
    echo "<p>Total images: <strong>{$result['count']}</strong></p>";
    
    echo "<h2 style='color: green;'>✅ All Done!</h2>";
    echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;'>Go to Homepage</a></p>";
    echo "<p style='color: #ef4444; margin-top: 2rem;'><strong>⚠️ DELETE THIS FILE AFTER RUNNING IT!</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>
<?php
require_once 'config/database.php';

try {
    $db = getDB();
    echo "<h1 style='color: green;'>Database Connected Successfully!</h1>";
    
    // Test query
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tables in database:</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h1 style='color: red;'>Connection Failed</h1>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
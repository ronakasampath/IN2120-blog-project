<?php
require_once 'config/database.php';

$db = getDB();

try {
    // Add category column to blogpost table
    $db->exec("ALTER TABLE blogpost ADD COLUMN category VARCHAR(50) DEFAULT 'general' AFTER content");
    echo "Category column added successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
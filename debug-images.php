<?php
/**
 * DEBUG FILE - Check why images aren't showing
 * Save as: debug-images.php (in root)
 * Visit: http://localhost/Idea-canvas/debug-images.php?id=5
 */

require_once 'config/database.php';
require_once 'includes/image-functions.php';

$postId = intval($_GET['id'] ?? 0);

if (!$postId) {
    die("Please provide post ID: ?id=5");
}

echo "<h1>Debug Images for Post #$postId</h1>";

// Test 1: Get images from database
echo "<h2>Database Query Result:</h2>";
$images = getPostImages($postId);
echo "<pre>";
print_r($images);
echo "</pre>";

// Test 2: Check if files exist
echo "<h2>File Existence Check:</h2>";
foreach ($images as $img) {
    $fullPath = __DIR__ . $img['image_path'];
    $exists = file_exists($fullPath);
    $color = $exists ? 'green' : 'red';
    
    echo "<p style='color: $color;'>";
    echo $exists ? "EXISTS: " : "MISSING: ";
    echo htmlspecialchars($img['image_path']);
    echo "<br>Full path: " . htmlspecialchars($fullPath);
    echo "</p>";
    
    if ($exists) {
        echo "<img src='" . SITE_URL . htmlspecialchars($img['image_path']) . "' style='max-width: 200px; border: 2px solid green;'><br><br>";
    }
}

// Test 3: SITE_URL
echo "<h2>Configuration:</h2>";
echo "<p><strong>SITE_URL:</strong> " . SITE_URL . "</p>";

// Test 4: Show what the HTML looks like
echo "<h2>Generated HTML:</h2>";
echo "<code>";
foreach ($images as $img) {
    $url = SITE_URL . htmlspecialchars($img['image_path']);
    echo htmlspecialchars("<img src='$url' alt='Image'>") . "<br>";
}
echo "</code>";
?>
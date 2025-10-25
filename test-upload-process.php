<?php
echo "<h1>Upload Test Results</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    echo "<h2>Files Array:</h2><pre>";
    print_r($_FILES);
    echo "</pre>";
    
    $uploadDir = __DIR__ . '/uploads/posts/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        echo "<p>Created directory: $uploadDir</p>";
    }
    
    echo "<p>Upload dir exists: " . (is_dir($uploadDir) ? 'YES' : 'NO') . "</p>";
    echo "<p>Upload dir writable: " . (is_writable($uploadDir) ? 'YES' : 'NO') . "</p>";
    
    $tmpName = $_FILES['test_image']['tmp_name'];
    $originalName = $_FILES['test_image']['name'];
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $newName = 'test_' . time() . '.' . $ext;
    $destination = $uploadDir . $newName;
    
    echo "<p>Moving from: $tmpName</p>";
    echo "<p>Moving to: $destination</p>";
    
    if (move_uploaded_file($tmpName, $destination)) {
        echo "<h2 style='color: green;'>✅ SUCCESS!</h2>";
        echo "<p>File saved: $newName</p>";
        echo "<img src='uploads/posts/$newName' style='max-width: 400px;'>";
    } else {
        echo "<h2 style='color: red;'>❌ FAILED!</h2>";
        $error = error_get_last();
        echo "<pre>";
        print_r($error);
        echo "</pre>";
    }
} else {
    echo "<p>No file uploaded</p>";
}
?>
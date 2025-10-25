<?php
echo "<h1>File Existence Check</h1>";

$files = [
    'config/database.php',
    'includes/auth.php',
    'includes/blog.php',
    'includes/blog-enhanced.php',
    'includes/image-functions.php',
    'api/upload-post-images-handler.php',
    'api/delete-image-handler.php',
    'api/reorder-images-handler.php',
    'pages/manage-images.php',
];

$folders = [
    'uploads',
    'uploads/posts',
    'uploads/profiles',
];

echo "<h2>Files:</h2>";
foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $color = $exists ? 'green' : 'red';
    $status = $exists ? '✓ EXISTS' : '✗ MISSING';
    echo "<p style='color: $color;'>$status - $file</p>";
}

echo "<h2>Folders:</h2>";
foreach ($folders as $folder) {
    $path = __DIR__ . '/' . $folder;
    $exists = is_dir($path);
    $writable = is_writable($path);
    $color = ($exists && $writable) ? 'green' : 'red';
    $status = $exists ? ($writable ? '✓ EXISTS & WRITABLE' : '⚠ EXISTS BUT NOT WRITABLE') : '✗ MISSING';
    echo "<p style='color: $color;'>$status - $folder</p>";
}

echo "<h2>Database Tables:</h2>";
require_once 'config/database.php';
$db = getDB();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach (['user', 'blogPost', 'comments', 'likes', 'post_images'] as $table) {
    $exists = in_array($table, $tables);
    $color = $exists ? 'green' : 'red';
    $status = $exists ? '✓ EXISTS' : '✗ MISSING';
    echo "<p style='color: $color;'>$status - $table</p>";
}
?>
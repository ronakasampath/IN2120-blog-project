<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

session_start();

// Fake login for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';
$_SESSION['logged_in'] = true;
$_SESSION['csrf_token'] = 'test123';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Upload</title>
</head>
<body>
    <h1>Test Image Upload</h1>
    
    <form action="api/upload-post-images-handler.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="test123">
        <input type="hidden" name="post_id" value="1">
        
        <input type="file" name="images[]" multiple>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
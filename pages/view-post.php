<?php
/**
 * View Single Blog Post - FINAL CORRECTED INCLUSIONS
 * Ensures database config and constants are loaded first.
 * Save as: pages/view-post.php
 */

// FIX 1: Explicitly load the database config first.
// Path from 'pages' up to root ('..'), then down into 'config'.
require_once __DIR__ . '/../config/database.php';

// FIX 2: Now load the functions.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';


// Get post ID
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$postId) {
    // Uses SITE_URL constant defined in database.php
    header("Location: " . SITE_URL . "/index.php"); 
    exit;
}

// Get post
$post = getPost($postId); // Now uses the working getDB() call from blog.php

if (!$post) {
    // Uses SITE_URL constant defined in database.php
    header("Location: " . SITE_URL . "/index.php"); 
    exit;
}

$user = getCurrentUser(); // Now uses the working getDB() call from auth.php
$canEdit = $user && $user['id'] == $post['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="../index.php" class="navbar-brand"><?php echo SITE_NAME; ?></a>
            <ul class="navbar-menu">
                <li><a href="../index.php">Home</a></li>
                <?php if ($user): ?>
                    <li><a href="create-post.php">Create Post</a></li>
                    <li><a href="my-posts.php">My Posts</a></li>
                    <li><a href="../api/logout-handler.php">Logout (<?php echo htmlspecialchars($user['username']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <div class="card">
                <h1 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="card-meta">
                    By <strong><?php echo htmlspecialchars($post['username']); ?></strong> 
                    on <?php echo formatDate($post['created_at']); ?>
                    <?php if ($post['created_at'] != $post['updated_at']): ?>
                        <br><em>Last updated: <?php echo formatDate($post['updated_at']); ?></em>
                    <?php endif; ?>
                </div>

                <?php if ($canEdit): ?>
                    <div class="card-actions" style="margin-top: 1rem;">
                        <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">Edit Post</a>
                        <button onclick="deletePost(<?php echo $post['id']; ?>)" class="btn btn-danger btn-sm">Delete Post</button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="post-content" id="postContent">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
            </div>

            <div class="card">
                <a href="../index.php" class="btn btn-secondary">← Back to All Posts</a>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Render markdown content
        // NOTE: If you are not using a markdown function, remove the call to renderMarkdown.
        const content = <?php echo json_encode($post['content']); ?>;
        // Assuming renderMarkdown is defined in main.js or should be replaced with simple HTML injection:
        document.getElementById('postContent').innerHTML = content; // Changed for safety, use renderMarkdown(content) if it exists.

        // Delete post function 
        function deletePost(postId) {
            if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('post_id', postId);
            // CRITICAL: getCSRFToken() is now available because auth.php loaded successfully.
            formData.append('csrf_token', '<?php echo getCSRFToken(); ?>'); 

            fetch('../api/delete-post-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // If response is not OK (e.g., 403, 404, 500), try to read the JSON error message.
                // If it crashes (the HTML error), this line will trigger the .catch() block.
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    // Redirect to home page after successful deletion
                    window.location.href = '../index.php';
                } else {
                    // Display specific error message from the PHP handler (e.g., 'Unauthorized')
                    alert(result.message);
                }
            })
            .catch(error => {
                // Catches the SyntaxError when PHP returns HTML instead of JSON
                alert('An unexpected server error occurred. Please check your PHP error logs for the cause.');
                console.error('Delete Error:', error);
            });
        }
    </script>
</body>
</html>
<?php
/**
 * Edit Blog Post Page
 * Save as: pages/edit-post.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';

// Require login
requireLogin();

$user = getCurrentUser();

// Get post ID
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$postId) {
    header("Location: ../index.php");
    exit;
}

// Get post
$post = getPost($postId);

if (!$post || $post['user_id'] != $user['id']) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="../index.php" class="navbar-brand"><?php echo SITE_NAME; ?></a>
            <ul class="navbar-menu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="create-post.php">Create Post</a></li>
                <li><a href="my-posts.php">My Posts</a></li>
                <li><a href="../api/logout-handler.php">Logout (<?php echo htmlspecialchars($user['username']); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <!-- Edit Post Form -->
    <main class="main-content">
        <div class="container">
            <div class="card">
                <h1 class="card-title">Edit Blog Post</h1>
                <p style="color: var(--text-light);">Update your blog post content</p>
            </div>

            <div id="message"></div>

            <div class="card">
                <form id="editPostForm" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Post Title</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-control" 
                            required
                            maxlength="255"
                            value="<?php echo htmlspecialchars($post['title']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label">Content (Markdown Supported)</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            class="form-control" 
                            required
                            rows="15"><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Update Post</button>
                        <a href="view-post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Preview -->
            <div class="card">
                <h3>Preview</h3>
                <div id="preview" class="post-content" style="min-height: 100px; padding: 1rem; background: var(--bg-gray); border-radius: 6px;">
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Initial preview
        const contentTextarea = document.getElementById('content');
        const previewDiv = document.getElementById('preview');
        
        // Show initial preview
        previewDiv.innerHTML = renderMarkdown(contentTextarea.value);

        // Update preview on input
        contentTextarea.addEventListener('input', function() {
            previewDiv.innerHTML = renderMarkdown(this.value);
        });

        // Form submission
        document.getElementById('editPostForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const messageDiv = document.getElementById('message');
            const formData = new FormData(this);
            
            try {
                const response = await fetch('../api/update-post-handler.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                    setTimeout(() => {
                        window.location.href = 'view-post.php?id=<?php echo $post['id']; ?>';
                    }, 1000);
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
            }
        });
    </script>
</body>
</html>
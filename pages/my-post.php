<?php
/**
 * My Posts Page - View user's own posts
 * Save as: pages/my-posts.php
 */

require_once '../includes/auth.php';
require_once '../includes/blog.php';

// Require login
requireLogin();

$user = getCurrentUser();
$myPosts = getPostsByUser($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts - <?php echo SITE_NAME; ?></title>
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

    <!-- My Posts Content -->
    <main class="main-content">
        <div class="container">
            <div class="card">
                <h1 class="card-title">My Blog Posts</h1>
                <p style="color: var(--text-light);">
                    Manage all your blog posts. Total: <?php echo count($myPosts); ?>
                </p>
                <a href="create-post.php" class="btn btn-primary" style="margin-top: 1rem;">Create New Post</a>
            </div>

            <?php if (empty($myPosts)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <h2 class="empty-state-title">No posts yet</h2>
                    <p>You haven't created any blog posts yet. Start writing your first post!</p>
                    <a href="create-post.php" class="btn btn-primary" style="margin-top: 1rem;">Create Your First Post</a>
                </div>
            <?php else: ?>
                <div class="post-list">
                    <?php foreach ($myPosts as $post): ?>
                        <article class="post-item">
                            <h2 class="post-title">
                                <a href="view-post.php?id=<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <div class="post-meta">
                                Created: <?php echo formatDate($post['created_at']); ?>
                                <?php if ($post['created_at'] != $post['updated_at']): ?>
                                    <br>Updated: <?php echo formatDate($post['updated_at']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="post-excerpt">
                                <?php echo htmlspecialchars(getExcerpt($post['content'])); ?>
                            </div>
                            <div class="card-actions">
                                <a href="view-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-success btn-sm">Edit</a>
                                <button onclick="deletePost(<?php echo $post['id']; ?>)" class="btn btn-danger btn-sm">Delete</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function deletePost(postId) {
            if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');

            fetch('../api/delete-post-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert(result.message);
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>
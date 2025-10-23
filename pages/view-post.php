<?php
/**
 * View Single Blog Post - WITH DELETE
 * Save as: pages/view-post.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';

// Get post ID
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$postId) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

// Get post
$post = getPost($postId);

if (!$post) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$pageTitle = htmlspecialchars($post['title']);
$currentUser = getCurrentUser();

// Check if user can edit/delete (owner OR admin)
$canEdit = $currentUser && ($currentUser['id'] == $post['user_id'] || isAdmin());

include __DIR__ . '/../includes/header.php';
?>

    <!-- Post Content -->
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
                        <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">✏️ Edit Post</a>
                        <button onclick="deletePost(<?php echo $post['id']; ?>)" class="btn btn-danger btn-sm">🗑️ Delete Post</button>
                        <?php if (isAdmin() && $currentUser['id'] != $post['user_id']): ?>
                            <span style="background: #ef4444; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.75rem; margin-left: 0.5rem;">ADMIN ACTION</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="post-content" id="postContent">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
            </div>

            <div class="card">
                <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">← Back to All Posts</a>
            </div>
        </div>
    </main>

    <script>
        // Render markdown content
        const content = <?php echo json_encode($post['content']); ?>;
        document.getElementById('postContent').innerHTML = renderMarkdown(content);

        // Delete post function
        function deletePost(postId) {
            if (!confirm('⚠️ Are you sure you want to delete this post?\n\nThis action cannot be undone!')) {
                return;
            }

            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');

            fetch('<?php echo SITE_URL; ?>/api/delete-post-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ ' + result.message);
                    window.location.href = '<?php echo SITE_URL; ?>/index.php';
                } else {
                    alert('❌ ' + result.message);
                }
            })
            .catch(error => {
                alert('❌ An error occurred. Please try again.');
                console.error('Error:', error);
            });
        }
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
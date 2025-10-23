<?php
/**
 * Homepage - WITH ADMIN DELETE BUTTONS
 * Save as: index.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/blog.php';

$pageTitle = 'Home';
$currentUser = getCurrentUser();
$posts = getAllPosts();

include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="card">
                <h1 class="card-title">Welcome to <?php echo SITE_NAME; ?></h1>
                <p style="color: var(--text-light);">
                    <?php if ($currentUser): ?>
                        Hello, <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                        <?php if (isAdmin()): ?>
                            <span style="background: #ef4444; color: white; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; margin-left: 0.5rem;">ADMIN</span>
                        <?php endif; ?>
                        ! Browse posts below or create your own.
                    <?php else: ?>
                        A simple blogging platform. <a href="pages/register.php">Register</a> or <a href="pages/login.php">Login</a> to start blogging!
                    <?php endif; ?>
                </p>
            </div>

            <!-- Blog Posts List -->
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <h2 class="empty-state-title">No posts yet</h2>
                    <p>Be the first to create a blog post!</p>
                    <?php if ($currentUser): ?>
                        <a href="pages/create-post.php" class="btn btn-primary" style="margin-top: 1rem;">Create Your First Post</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="post-list">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-item">
                            <h2 class="post-title">
                                <a href="pages/view-post.php?id=<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <div class="post-meta">
                                By <strong><?php echo htmlspecialchars($post['username']); ?></strong> 
                                on <?php echo formatDate($post['created_at']); ?>
                                <?php if ($post['created_at'] != $post['updated_at']): ?>
                                    <em>(Updated: <?php echo formatDate($post['updated_at']); ?>)</em>
                                <?php endif; ?>
                            </div>
                            <div class="post-excerpt">
                                <?php echo htmlspecialchars(getExcerpt($post['content'])); ?>
                            </div>
                            <div class="card-actions">
                                <a href="pages/view-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">Read More</a>
                                
                                <?php if ($currentUser): ?>
                                    <?php if ($currentUser['id'] == $post['user_id'] || isAdmin()): ?>
                                        <a href="pages/edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-success btn-sm">Edit</a>
                                        <button onclick="deletePostFromHome(<?php echo $post['id']; ?>)" class="btn btn-danger btn-sm">Delete</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function deletePostFromHome(postId) {
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
                    location.reload();
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

<?php include __DIR__ . '/includes/footer.php'; ?>
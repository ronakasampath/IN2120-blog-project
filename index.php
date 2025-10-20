<?php
/**
 * Homepage - Display all blog posts
 * Save as: index.php (root folder)
 */

// Load configuration and functions
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/blog.php';

$pageTitle = 'Home';
$currentUser = getCurrentUser();
$posts = getAllPosts();

// Include header
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="card">
                <h1 class="card-title">Welcome to <?php echo SITE_NAME; ?></h1>
                <p style="color: var(--text-light);">
                    <?php if ($currentUser): ?>
                        Hello, <?php echo htmlspecialchars($currentUser['username']); ?>! Browse posts below or create your own.
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
                            <a href="pages/view-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">Read More</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
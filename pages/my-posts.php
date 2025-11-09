<?php
/**
 * My Posts Page - THEMED TO MATCH HOMEPAGE
 * Save as: pages/my-posts.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';
require_once __DIR__ . '/../includes/image-functions.php';

requireLogin();

$user = getCurrentUser();
$myPosts = getPostsByUser($user['id']);
$pageTitle = 'My Posts';

include __DIR__ . '/../includes/header.php';
?>

<style>
/* My Posts Container - Matching Homepage */
.my-posts-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 3rem 2rem;
}

/* Header Card */
.header-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 2.5rem;
    margin-bottom: 2rem;
}

.header-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.75rem;
    letter-spacing: -0.025em;
}

.header-subtitle {
    color: #6b7280;
    font-size: 1rem;
    margin-bottom: 1.5rem;
}

.post-count-badge {
    display: inline-block;
    background: linear-gradient(135deg, #111827 0%, #374151 100%);
    color: white;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
}

/* Post Card - Matching Homepage Blog Cards */
.post-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: all 0.2s;
}

.post-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.post-card-content {
    padding: 2rem;
}

.post-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.post-card-title {
    font-size: 1.625rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.3;
    flex: 1;
}

.post-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

.post-card-title a:hover {
    color: #3b82f6;
}

.post-category-badge {
    padding: 0.375rem 0.875rem;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    white-space: nowrap;
    opacity: 0.7;
    background: #f3f4f6;
    color: #6b7280;
}

.post-meta {
    color: #9ca3af;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.post-excerpt {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-card-stats {
    display: flex;
    gap: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    margin-bottom: 1rem;
}

.post-stat {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.9375rem;
}

.post-card-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

/* Buttons - THEMED */
.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
    border: 2px solid transparent;
    cursor: pointer;
    font-size: 0.9375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #111827;
    color: white;
    border-color: #111827;
    flex: 1;
}

.btn-primary:hover {
    background: #1f2937;
    border-color: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.25);
}

.btn-success {
    background: #10b981;
    color: white;
    border-color: #10b981;
    flex: 1;
}

.btn-success:hover {
    background: #059669;
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-danger {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
    flex: 1;
}

.btn-danger:hover {
    background: #dc2626;
    border-color: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-sm {
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.empty-state-icon {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    opacity: 0.5;
}

.empty-state-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #6b7280;
    font-size: 1.0625rem;
    margin-bottom: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .my-posts-container {
        padding: 2rem 1.25rem;
    }
    
    .header-card,
    .post-card-content {
        padding: 2rem 1.5rem;
    }
    
    .post-card-header {
        flex-direction: column;
    }
    
    .post-card-title {
        font-size: 1.375rem;
    }
    
    .post-card-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<main class="main-content">
    <div class="my-posts-container">
        <div class="header-card">
            <h1 class="header-title">📝 My Blog Posts</h1>
            <p class="header-subtitle">
                Manage all your blog posts • Total: <span class="post-count-badge"><?php echo count($myPosts); ?></span>
            </p>
            <a href="<?php echo SITE_URL; ?>/pages/create-post.php" class="btn btn-primary">
                ✏️ Create New Post
            </a>
        </div>

        <?php if (empty($myPosts)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h2 class="empty-state-title">No posts yet</h2>
                <p>You haven't created any blog posts yet. Start writing your first post!</p>
                <a href="<?php echo SITE_URL; ?>/pages/create-post.php" class="btn btn-primary">
                    ✏️ Create Your First Post
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($myPosts as $post): 
                $likeCount = getLikeCount($post['id']);
                $commentCount = getCommentCount($post['id']);
                $firstImage = getFirstPostImage($post['id']);
            ?>
                <article class="post-card">
                    <div class="post-card-content">
                        <div class="post-card-header">
                            <h2 class="post-card-title">
                                <a href="<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <?php if ($post['category']): ?>
                                <span class="post-category-badge">
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="post-meta">
                            Created: <?php echo formatDate($post['created_at']); ?>
                            <?php if ($post['created_at'] != $post['updated_at']): ?>
                                • Updated: <?php echo formatDate($post['updated_at']); ?>
                            <?php endif; ?>
                        </div>

                        <div class="post-excerpt">
                            <?php echo htmlspecialchars(getExcerpt($post['content'], 200)); ?>
                        </div>

                        <div class="post-card-stats">
                            <span class="post-stat">
                                ❤️ <?php echo $likeCount; ?> likes
                            </span>
                            <span class="post-stat">
                                💬 <?php echo $commentCount; ?> comments
                            </span>
                            <?php if ($firstImage): ?>
                                <span class="post-stat">
                                    🖼️ Has images
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="post-card-actions">
                            <a href="<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $post['id']; ?>" 
                               class="btn btn-primary btn-sm">
                                👁️ View
                            </a>
                            <a href="<?php echo SITE_URL; ?>/pages/edit-post.php?id=<?php echo $post['id']; ?>" 
                               class="btn btn-success btn-sm">
                                ✏️ Edit
                            </a>
                            <button onclick="deletePost(<?php echo $post['id']; ?>)" 
                                    class="btn btn-danger btn-sm">
                                🗑️ Delete
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
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

    fetch('<?php echo SITE_URL; ?>/api/delete-post-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('✓ ' + result.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('✕ ' + result.message, 'error');
        }
    })
    .catch(error => {
        showToast('✕ An error occurred. Please try again.', 'error');
    });
}

function showToast(message, type = 'success') {
    const bgColor = type === 'error' ? '#ef4444' : '#10b981';
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        font-weight: 600;
        font-size: 0.9375rem;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
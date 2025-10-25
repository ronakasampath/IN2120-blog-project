<?php
/**
 * Homepage - NEW CARD LAYOUT
 * Save as: index.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/blog.php';
require_once __DIR__ . '/includes/blog-enhanced.php';
require_once __DIR__ . '/includes/image-functions.php';
require_once __DIR__ . '/includes/image-functions.php';

$pageTitle = 'Home';
$currentUser = getCurrentUser();
$posts = getAllPosts();

include __DIR__ . '/includes/header.php';
?>

<style>
/* Blog Card Layout */
.blog-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.blog-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.blog-card-content {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    padding: 1.5rem;
}

.blog-card-left {
    display: flex;
    flex-direction: column;
}

.blog-card-author {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.blog-card-author img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

.blog-card-author .default-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

.blog-card-author-info {
    flex: 1;
}

.blog-card-author-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.95rem;
}

.blog-card-date {
    font-size: 0.8rem;
    color: #6b7280;
}

.blog-card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

.blog-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

.blog-card-title a:hover {
    color: #4f46e5;
}

.blog-card-excerpt {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 1rem;
    flex: 1;
}

.blog-card-right {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.blog-card-image {
    width: 100%;
    height: 200px;
    border-radius: 8px;
    object-fit: cover;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 3rem;
}

.blog-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.blog-card-actions {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

.blog-card-action {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: #6b7280;
    font-size: 0.9rem;
    cursor: pointer;
    transition: color 0.2s;
}

.blog-card-action:hover {
    color: #4f46e5;
}

.blog-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.read-more {
    color: #4f46e5;
    font-weight: 500;
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.2s;
}

.read-more:hover {
    color: #4338ca;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .blog-card-content {
        grid-template-columns: 1fr;
    }
    
    .blog-card-image {
        height: 180px;
    }
}
</style>

<main class="main-content">
    <div class="container">
        <div class="card">
            <h1 class="card-title">Welcome to <?php echo SITE_NAME; ?></h1>
            <p style="color: var(--text-light);">
                <?php if ($currentUser): ?>
                    Hello, <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                    <?php if (isAdmin()): ?>
                        <span class="admin-badge">ADMIN</span>
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
            <?php foreach ($posts as $post): 
                // Get author profile picture
                $db = getDB();
                $stmt = $db->prepare("SELECT profile_picture FROM user WHERE id = ?");
                $stmt->execute([$post['user_id']]);
                $author = $stmt->fetch();
                $authorPic = $author['profile_picture'] ?? null;
                
                // Get engagement stats
                $likeCount = getLikeCount($post['id']);
                $commentCount = getCommentCount($post['id']);
                
                // Get first letter for default avatar
                $firstLetter = strtoupper(substr($post['username'], 0, 1));
            ?>
                <article class="blog-card">
                    <div class="blog-card-content">
                        <!-- Left Side: Author, Title, Excerpt, Actions -->
                        <div class="blog-card-left">
                            <!-- Author Info -->
                            <div class="blog-card-author">
                                <?php if ($authorPic): ?>
                                    <img src="<?php echo SITE_URL . htmlspecialchars($authorPic); ?>" alt="<?php echo htmlspecialchars($post['username']); ?>">
                                <?php else: ?>
                                    <div class="default-avatar"><?php echo $firstLetter; ?></div>
                                <?php endif; ?>
                                
                                <div class="blog-card-author-info">
                                    <div class="blog-card-author-name"><?php echo htmlspecialchars($post['username']); ?></div>
                                    <div class="blog-card-date"><?php echo formatDate($post['created_at']); ?></div>
                                </div>
                            </div>

                            <!-- Title -->
                            <h2 class="blog-card-title">
                                <a href="pages/view-post.php?id=<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>

                            <!-- Excerpt -->
                            <div class="blog-card-excerpt">
                                <?php echo htmlspecialchars(getExcerpt($post['content'], 200)); ?>
                                <a href="pages/view-post.php?id=<?php echo $post['id']; ?>" class="read-more">read more</a>
                            </div>

                            <!-- Footer: Actions & Edit Buttons -->
                            <div class="blog-card-footer">
                                <div class="blog-card-actions">
                                    <span class="blog-card-action">
                                        ❤️ <span><?php echo $likeCount; ?></span>
                                    </span>
                                    <span class="blog-card-action">
                                        💬 <span><?php echo $commentCount; ?></span>
                                    </span>
                                    <span class="blog-card-action" onclick="copyPostUrl(<?php echo $post['id']; ?>)">
                                        🔗 Share
                                    </span>
                                </div>

                                <?php if ($currentUser && ($currentUser['id'] == $post['user_id'] || isAdmin())): ?>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="pages/edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-success btn-sm">Edit</a>
                                        <button onclick="deletePostFromHome(<?php echo $post['id']; ?>)" class="btn btn-danger btn-sm">Delete</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right Side: Featured Image -->
                        <!-- Right Side: Featured Image -->
                        <div class="blog-card-right">
                            <div class="blog-card-image">
                                <?php 
                                    // Get first image from post_images table
                                    $firstImage = getFirstPostImage($post['id']);
                                if ($firstImage): 
                                ?>
                                    <img src="<?php echo SITE_URL . htmlspecialchars($firstImage); ?>" alt="Post image">
                                <?php else: ?>
                                    📄
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
    function copyPostUrl(postId) {
        const url = '<?php echo SITE_URL; ?>/pages/view-post.php?id=' + postId;
        navigator.clipboard.writeText(url).then(() => {
            showToast('✅ Link copied to clipboard!');
        }).catch(() => {
            alert('Link: ' + url);
        });
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000; animation: slideIn 0.3s ease;';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }

    function deletePostFromHome(postId) {
        if (!confirm('⚠️ Delete this post permanently?')) return;

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
                showToast('✅ ' + result.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('❌ ' + result.message);
            }
        })
        .catch(error => alert('❌ Error occurred'));
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
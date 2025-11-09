<?php
/**
 * Homepage - ULTRA PROFESSIONAL PUBLICATION DESIGN
 * Save as: index.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/blog.php';
require_once __DIR__ . '/includes/blog-enhanced.php';
require_once __DIR__ . '/includes/image-functions.php';
require_once __DIR__ . '/includes/follow-functions.php';

$pageTitle = 'Home';
$currentUser = getCurrentUser();

// Get filter parameters
$category = $_GET['category'] ?? '';
$searchQuery = $_GET['q'] ?? '';
$showFollowing = isset($_GET['following']) && $_GET['following'] === '1';

// Build query based on filters
$db = getDB();

if ($showFollowing && $currentUser) {
    $posts = getFollowingPosts($currentUser['id'], $category, $searchQuery);
} else {
    $sql = "SELECT 
                bp.id, 
                bp.title, 
                bp.content,
                bp.category,
                bp.created_at, 
                bp.updated_at,
                u.username,
                u.id as user_id
            FROM blogpost bp
            JOIN user u ON bp.user_id = u.id
            WHERE 1=1";

    $params = [];

    if ($category) {
        $sql .= " AND bp.category = ?";
        $params[] = $category;
    }

    if ($searchQuery) {
        $sql .= " AND (bp.title LIKE ? OR u.username LIKE ? OR bp.category LIKE ?)";
        $searchTerm = "%{$searchQuery}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY bp.created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
}

// Get all categories for filter
$categoriesStmt = $db->query("SELECT DISTINCT category FROM blogpost WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/includes/header.php';
?>

<style>
/* Feed Toggle Tabs */
.feed-toggle {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 0.5rem;
    margin-bottom: 2rem;
    display: flex;
    gap: 0.5rem;
}

.feed-tab {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 600;
    text-align: center;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.feed-tab:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.feed-tab.active {
    background: #111827;
    color: white;
    border-color: #111827;
}

/* Category Filter - Horizontal Scroll */
.category-filter-wrapper {
    margin-bottom: 2rem;
    position: relative;
}

.category-scroll-container {
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
    -ms-overflow-style: none;
    scroll-behavior: smooth;
}

.category-scroll-container::-webkit-scrollbar {
    display: none;
}

.category-filter {
    display: inline-flex;
    gap: 0.75rem;
    padding: 0.5rem 0;
    white-space: nowrap;
}

.category-btn {
    padding: 0.5rem 1.125rem;
    border: none;
    background: transparent;
    border-radius: 20px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 500;
    transition: all 0.2s;
    font-size: 0.9375rem;
}

.category-btn:hover {
    color: #111827;
    background: #f3f4f6;
}

.category-btn.active {
    background: #111827;
    color: white;
}

/* Blog Card Layout */
.blog-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.blog-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.blog-card-content {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2rem;
    padding: 2rem;
}

.blog-card-left {
    display: flex;
    flex-direction: column;
}

.blog-card-author {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    margin-bottom: 1.25rem;
}

.blog-card-author img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
    cursor: pointer;
}

.blog-card-author .default-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.25rem;
    cursor: pointer;
}

.blog-card-author-info {
    flex: 1;
}

.blog-card-author-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: color 0.2s;
}

.blog-card-author-name:hover {
    color: #1a232fff;
}

.blog-card-date {
    font-size: 0.8125rem;
    color: #9ca3af;
}

/* Follow Button - Dot Style */
.follow-dot-btn-card {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0;
    background: none;
    border: none;
    color: #1a232fff;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: opacity 0.2s;
}

.follow-dot-btn-card:hover {
    opacity: 0.75;
}

.follow-dot-btn-card.following {
    color: #111827;
}

.follow-dot-card {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: currentColor;
}

.blog-card-title-row {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.blog-card-title {
    font-size: 1.625rem;
    font-weight: 700;
    color: #1a232fff;
    line-height: 1.3;
    flex: 1;
}

.blog-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

.blog-card-title a:hover {
    color: #1a232fff;
}

/* Category Badge - More Subtle */
.category-badge-subtle {
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

.blog-card-excerpt {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 1rem;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 6;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.blog-card-right {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.blog-card-image {
    width: 100%;
    height: 220px;
    border-radius: 8px;
    object-fit: cover;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d1d5db;
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
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.blog-card-action {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: color 0.2s;
}

.blog-card-action:hover {
    color: #111827;
}

.blog-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.read-more {
    color: #3b82f6;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9375rem;
    transition: color 0.2s;
    margin-top: 0.75rem;
    display: inline-block;
}

.read-more:hover {
    color: #2563eb;
    text-decoration: underline;
}

/* Empty state for no following */
.empty-following {
    background: white;
    border-radius: 12px;
    padding: 3.5rem;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.empty-following-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
}

/* Add this CSS to your <style> section in index.php, right after the existing styles */

/* ============================================
   BUTTON STYLES - Theme Matched
   ============================================ */

/* Base button styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    font-weight: 600;
    font-size: 0.875rem;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 2px solid transparent;
    line-height: 1.2;
    font-family: inherit;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Small button variant */
.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8125rem;
    border-radius: 6px;
}

/* Success button (Edit) - Emerald Green */
.btn-success {
    background: #10b981;
    color: white;
    border-color: #10b981;
    box-shadow: 0 1px 2px rgba(16, 185, 129, 0.1);
}

.btn-success:hover:not(:disabled) {
    background: #059669;
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-success:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

/* Danger button (Delete) - Red */
.btn-danger {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
    box-shadow: 0 1px 2px rgba(239, 68, 68, 0.1);
}

.btn-danger:hover:not(:disabled) {
    background: #dc2626;
    border-color: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-danger:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
}

/* Primary button (Dark) */
.btn-primary {
    background: #111827;
    color: white;
    border-color: #111827;
    box-shadow: 0 1px 2px rgba(17, 24, 39, 0.1);
}

.btn-primary:hover:not(:disabled) {
    background: #1f2937;
    border-color: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.3);
}

.btn-primary:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(17, 24, 39, 0.2);
}

/* Secondary button (Light Gray) */
.btn-secondary {
    background: #f3f4f6;
    color: #1f2937;
    border-color: #e5e7eb;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn-secondary:hover:not(:disabled) {
    background: #e5e7eb;
    border-color: #d1d5db;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-secondary:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Responsive design for buttons */
@media (max-width: 768px) {
    .btn-sm {
        padding: 0.4rem 0.875rem;
        font-size: 0.75rem;
    }
    
    .blog-card-footer > div:last-child {
        width: 100%;
        justify-content: stretch;
    }
    
    .blog-card-footer > div:last-child .btn {
        flex: 1;
    }
}



</style>

<main class="main-content">
    <div class="container">
        <div class="card">
            <h1 class="card-title">Welcome to Idea Canvas</h1>
            <p style="color: var(--text-light);">
                <?php if ($currentUser): ?>
                    Hello, <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                    <?php if (isAdmin()): ?>
                        <span class="admin-badge">ADMIN</span>
                    <?php endif; ?>
                    Browse posts below or create your own.
                <?php else: ?>
                    A platform for sharing ideas. <a href="pages/register.php">Register</a> or <a href="pages/login.php">Login</a> to start blogging!
                <?php endif; ?>
            </p>
        </div>

        <?php if ($currentUser): ?>
        <!-- Feed Toggle -->
        <div class="feed-toggle">
            <a href="<?php echo SITE_URL; ?>/index.php" 
               class="feed-tab <?php echo !$showFollowing ? 'active' : ''; ?>">
                All Posts
            </a>
            <a href="<?php echo SITE_URL; ?>/index.php?following=1" 
               class="feed-tab <?php echo $showFollowing ? 'active' : ''; ?>">
                Following
            </a>
        </div>
        <?php endif; ?>

        <!-- Category Filter - Horizontal Scroll -->
        <?php if (!empty($categories) && !$showFollowing): ?>
        <div class="category-filter-wrapper">
            <div class="category-scroll-container">
                <div class="category-filter">
                    <a href="<?php echo SITE_URL; ?>/index.php" 
                       class="category-btn <?php echo empty($category) ? 'active' : ''; ?>">
                        All
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?php echo SITE_URL; ?>/index.php?category=<?php echo urlencode($cat); ?>" 
                           class="category-btn <?php echo $category === $cat ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars(ucfirst($cat)); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($searchQuery): ?>
            <div class="card">
                <p>Search results for: <strong><?php echo htmlspecialchars($searchQuery); ?></strong></p>
                <a href="<?php echo SITE_URL; ?>/index.php" style="color: #3b82f6; font-weight: 600;">Clear search</a>
            </div>
        <?php endif; ?>

        <!-- Empty state when showing following but not following anyone -->
        <?php if ($showFollowing && empty($posts) && $currentUser): ?>
            <div class="empty-following">
                <div class="empty-following-icon">👥</div>
                <h2 style="color: #1f2937; margin-bottom: 1rem;">You're not following anyone yet</h2>
                <p style="color: #6b7280; margin-bottom: 2rem;">
                    Follow authors to see their posts here. Discover authors by browsing all posts or searching for topics you're interested in.
                </p>
                <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-primary">
                    Browse All Posts
                </a>
            </div>
        <?php elseif (empty($posts)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h2 class="empty-state-title">No posts found</h2>
                <p>
                    <?php if ($searchQuery || $category): ?>
                        Try adjusting your search or filter.
                    <?php else: ?>
                        Be the first to create a blog post!
                    <?php endif; ?>
                </p>
                <?php if ($currentUser && !$searchQuery && !$category): ?>
                    <a href="pages/create-post.php" class="btn btn-primary" style="margin-top: 1rem;">Create Your First Post</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): 
                $db = getDB();
                $stmt = $db->prepare("SELECT profile_picture FROM user WHERE id = ?");
                $stmt->execute([$post['user_id']]);
                $author = $stmt->fetch();
                $authorPic = $author['profile_picture'] ?? null;
                
                $likeCount = getLikeCount($post['id']);
                $commentCount = getCommentCount($post['id']);
                
                $firstLetter = strtoupper(substr($post['username'], 0, 1));
                
                // Check if current user is following this author
                $isFollowingAuthor = $currentUser && $currentUser['id'] != $post['user_id'] ? isFollowing($currentUser['id'], $post['user_id']) : false;
            ?>
                <article class="blog-card">
                    <div class="blog-card-content">
                        <div class="blog-card-left">
                            <div class="blog-card-author">
                                <a href="pages/author-profile.php?id=<?php echo $post['user_id']; ?>" style="text-decoration: none;">
                                    <?php if ($authorPic): ?>
                                        <img src="<?php echo SITE_URL . htmlspecialchars($authorPic); ?>" alt="<?php echo htmlspecialchars($post['username']); ?>">
                                    <?php else: ?>
                                        <div class="default-avatar"><?php echo $firstLetter; ?></div>
                                    <?php endif; ?>
                                </a>
                                
                                <div class="blog-card-author-info">
                                    <a href="pages/author-profile.php?id=<?php echo $post['user_id']; ?>" style="text-decoration: none;">
                                        <div class="blog-card-author-name"><?php echo htmlspecialchars($post['username']); ?></div>
                                    </a>
                                    <div class="blog-card-date"><?php echo formatDate($post['created_at']); ?></div>
                                </div>
                                
                                <?php if ($currentUser && $currentUser['id'] != $post['user_id']): ?>
                                    <button class="follow-dot-btn-card <?php echo $isFollowingAuthor ? 'following' : ''; ?>" 
                                            data-user-id="<?php echo $post['user_id']; ?>"
                                            onclick="toggleFollowFromCard(this, <?php echo $post['user_id']; ?>)">
                                        <span class="follow-dot-card"></span>
                                        <span><?php echo $isFollowingAuthor ? 'Following' : 'Follow'; ?></span>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="blog-card-title-row">
                                <h2 class="blog-card-title">
                                    <a href="pages/view-post.php?id=<?php echo $post['id']; ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h2>
                                <?php if ($post['category']): ?>
                                    <span class="category-badge-subtle">
                                        <?php echo htmlspecialchars($post['category']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="blog-card-excerpt blog-excerpt-rendered" data-full-content='<?php echo htmlspecialchars($post['content'], ENT_QUOTES); ?>'>
                                Loading...
                            </div>
                            <a href="pages/view-post.php?id=<?php echo $post['id']; ?>" class="read-more">
                                Read more →
                            </a>

                            <div class="blog-card-footer">
                                <div class="blog-card-actions">
                                    <span class="blog-card-action">
                                        <span class="like-count"><?php echo $likeCount; ?></span>
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
                                        <a href="pages/edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-success btn-sm">
                                            Edit
                                        </a>
                                        <button onclick="deletePostFromHome(<?php echo $post['id']; ?>)" class="btn btn-danger btn-sm">
                                            Delete
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="blog-card-right">
                            <div class="blog-card-image">
                                <?php 
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
function toggleFollowFromCard(button, userId) {
    <?php if (!$currentUser): ?>
        alert('Please login to follow users');
        return;
    <?php endif; ?>
    
    const originalText = button.querySelector('span:last-child').textContent;
    button.disabled = true;
    button.querySelector('span:last-child').textContent = '...';
    
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
    
    fetch('<?php echo SITE_URL; ?>/api/toggle-follow-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                if (data.following) {
                    button.classList.add('following');
                    button.querySelector('span:last-child').textContent = 'Following';
                    showToast('✓ Now following!');
                } else {
                    button.classList.remove('following');
                    button.querySelector('span:last-child').textContent = 'Follow';
                    showToast('Unfollowed');
                }
            } else {
                alert(data.message || 'Failed to update follow status');
                button.querySelector('span:last-child').textContent = originalText;
            }
            button.disabled = false;
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Server error. Please try again.');
            button.querySelector('span:last-child').textContent = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        alert('Network error. Please try again.');
        button.querySelector('span:last-child').textContent = originalText;
        button.disabled = false;
    });
}

function copyPostUrl(postId) {
    const url = '<?php echo SITE_URL; ?>/pages/view-post.php?id=' + postId;
    navigator.clipboard.writeText(url).then(() => {
        showToast('Link copied to clipboard!');
    }).catch(() => {
        alert('Link: ' + url);
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #111827; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; font-weight: 600;';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 3000);
}

function deletePostFromHome(postId) {
    if (!confirm('Delete this post permanently?')) return;

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
            showToast(result.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(result.message);
        }
    })
    .catch(error => alert('Error occurred'));
}

// Render excerpts
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.blog-excerpt-rendered').forEach(excerptDiv => {
        const fullContent = excerptDiv.dataset.fullContent;
        if (!fullContent) {
            excerptDiv.textContent = 'No content available';
            return;
        }
        
        try {
            const rendered = renderMarkdown(fullContent);
            const temp = document.createElement('div');
            temp.innerHTML = rendered;
            
            let plainText = temp.textContent || temp.innerText || '';
            plainText = plainText.trim();
            
            if (plainText.length > 350) {
                plainText = plainText.substring(0, 350) + '...';
            }
            
            excerptDiv.textContent = plainText || 'No content available';
        } catch (error) {
            console.error('Error rendering excerpt:', error);
            excerptDiv.textContent = fullContent.substring(0, 350) + '...';
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
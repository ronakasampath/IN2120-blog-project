<?php
/**
 * Homepage - WITH FOLLOW SYSTEM
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
    // Get posts from followed users only
    $posts = getFollowingPosts($currentUser['id'], $category, $searchQuery);
} else {
    // Get all posts
    $sql = "SELECT 
                bp.id, 
                bp.title, 
                bp.content,
                bp.category,
                bp.created_at, 
                bp.updated_at,
                u.username,
                u.id as user_id
            FROM blogPost bp
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
$categoriesStmt = $db->query("SELECT DISTINCT category FROM blogPost WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/includes/header.php';
?>

<style>
/* Feed Toggle Tabs */
.feed-toggle {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

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
    cursor: pointer;
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
    cursor: pointer;
}

.blog-card-author-info {
    flex: 1;
}

.blog-card-author-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.95rem;
    cursor: pointer;
    transition: color 0.2s;
}

.blog-card-author-name:hover {
    color: #4f46e5;
}

.blog-card-date {
    font-size: 0.8rem;
    color: #6b7280;
}

.follow-btn-small {
    padding: 0.25rem 0.75rem;
    border: 1px solid #4f46e5;
    background: #4f46e5;
    color: white;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.follow-btn-small:hover {
    background: #4338ca;
    border-color: #4338ca;
}

.follow-btn-small.following {
    background: white;
    color: #4f46e5;
}

.follow-btn-small.following:hover {
    background: #fee2e2;
    border-color: #ef4444;
    color: #ef4444;
}

.blog-card-title-row {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.blog-card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.3;
    flex: 1;
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
    display: -webkit-box;
    -webkit-line-clamp: 8;
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
    height: 200px;
    border-radius: 8px;
    object-fit: cover;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
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
    font-weight: 600;
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.2s;
    margin-top: 0.5rem;
    display: inline-block;
}

.read-more:hover {
    color: #4338ca;
    text-decoration: underline;
}

.filter-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.filter-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

.category-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.category-btn {
    padding: 0.5rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 600;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.category-btn:hover {
    border-color: #4f46e5;
    color: #4f46e5;
    background: #f0f4ff;
}

.category-btn.active {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

.category-badge {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
}

.category-badge.technology { background: #dbeafe; color: #1e40af; }
.category-badge.science { background: #d1fae5; color: #065f46; }
.category-badge.art { background: #fce7f3; color: #9f1239; }
.category-badge.ai { background: #e0e7ff; color: #3730a3; }
.category-badge.business { background: #fef3c7; color: #92400e; }
.category-badge.lifestyle { background: #fee2e2; color: #991b1b; }
.category-badge.education { background: #f3e8ff; color: #6b21a8; }
.category-badge.health { background: #d1fae5; color: #065f46; }
.category-badge.travel { background: #dbeafe; color: #1e40af; }
.category-badge.food { background: #fed7aa; color: #9a3412; }
.category-badge.other { background: #f3f4f6; color: #374151; }

/* Empty state for no following */
.empty-following {
    background: white;
    border-radius: 12px;
    padding: 3rem;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-following-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .blog-card-content {
        grid-template-columns: 1fr;
    }
    
    .blog-card-image {
        height: 180px;
    }
    
    .feed-toggle {
        flex-direction: column;
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

        <!-- Category Filter -->
        <?php if (!empty($categories) && !$showFollowing): ?>
        <div class="filter-section">
            <span class="filter-label">Filter by Category:</span>
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
        <?php endif; ?>

        <?php if ($searchQuery): ?>
            <div class="card">
                <p>Search results for: <strong><?php echo htmlspecialchars($searchQuery); ?></strong></p>
                <a href="<?php echo SITE_URL; ?>/index.php" style="color: #4f46e5; font-weight: 600;">Clear search</a>
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
                                    <button class="follow-btn-small <?php echo $isFollowingAuthor ? 'following' : ''; ?>" 
                                            data-user-id="<?php echo $post['user_id']; ?>"
                                            onclick="toggleFollowFromCard(this, <?php echo $post['user_id']; ?>)">
                                        <?php echo $isFollowingAuthor ? 'Following' : 'Follow'; ?>
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
                                    <span class="category-badge <?php echo htmlspecialchars($post['category']); ?>">
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
    
    console.log('=== FOLLOW BUTTON CLICKED ===');
    console.log('User ID:', userId);
    console.log('Current user:', <?php echo json_encode($currentUser ?? null); ?>);
    console.log('CSRF Token:', '<?php echo getCSRFToken(); ?>');
    
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Loading...';
    
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
    
    console.log('FormData contents:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ':', pair[1]);
    }
    
    fetch('<?php echo SITE_URL; ?>/api/toggle-follow-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Try to get the response text first
        return response.text().then(text => {
            console.log('Raw response text:', text);
            
            // Try to parse as JSON
            try {
                const json = JSON.parse(text);
                console.log('Parsed JSON:', json);
                return {
                    ok: response.ok,
                    status: response.status,
                    data: json
                };
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was not valid JSON:', text.substring(0, 500));
                return {
                    ok: false,
                    status: response.status,
                    data: { 
                        success: false, 
                        message: 'Server returned invalid response',
                        raw: text.substring(0, 200)
                    }
                };
            }
        });
    })
    .then(({ ok, status, data }) => {
        console.log('Final result:', data);
        
        if (data.success) {
            if (data.following) {
                button.classList.add('following');
                button.textContent = 'Following';
                showToast('✓ Now following!');
            } else {
                button.classList.remove('following');
                button.textContent = 'Follow';
                showToast('Unfollowed');
            }
        } else {
            console.error('Follow action failed:', data);
            
            // Show detailed error
            let errorMsg = data.message || 'Unknown error';
            
            if (data.error === 'followers_table_missing') {
                errorMsg = '❌ Database not set up!\n\nThe followers table is missing.\nPlease run the database migration SQL.';
            }
            
            alert(errorMsg);
            button.textContent = originalText;
        }
        button.disabled = false;
    })
    .catch(error => {
        console.error('Network error:', error);
        alert('❌ Network error: ' + error.message + '\n\nCheck browser console for details.');
        button.textContent = originalText;
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
    toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000; font-weight: 600;';
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
                
                if (plainText.length > 400) {
                    plainText = plainText.substring(0, 400) + '...';
                }
                
                excerptDiv.textContent = plainText || 'No content available';
            } catch (error) {
                console.error('Error rendering excerpt:', error);
                excerptDiv.textContent = fullContent.substring(0, 400) + '...';
            }
        });
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
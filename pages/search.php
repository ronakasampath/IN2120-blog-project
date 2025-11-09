<?php
/**
 * Enhanced Search Page - Posts, Authors, Categories
 * Save as: pages/search.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';
require_once __DIR__ . '/../includes/blog-enhanced.php';
require_once __DIR__ . '/../includes/image-functions.php';
require_once __DIR__ . '/../includes/follow-functions.php';

$searchQuery = trim($_GET['q'] ?? '');
$activeTab = $_GET['tab'] ?? 'posts'; // posts, authors, or categories
$pageTitle = $searchQuery ? "Search: {$searchQuery}" : 'Search';
$currentUser = getCurrentUser();

$posts = [];
$authors = [];
$categories = [];

if ($searchQuery) {
    $db = getDB();
    $searchTerm = "%{$searchQuery}%";
    
    // Search Posts
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
            WHERE bp.title LIKE ? 
               OR bp.content LIKE ?
            ORDER BY bp.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm]);
    $posts = $stmt->fetchAll();
    
    // Search Authors
    $sql = "SELECT 
                u.id,
                u.username,
                u.email,
                u.bio,
                u.profile_picture,
                u.role,
                u.created_at,
                COUNT(DISTINCT bp.id) as post_count
            FROM user u
            LEFT JOIN blogpost bp ON u.id = bp.user_id
            WHERE u.username LIKE ?
            GROUP BY u.id
            ORDER BY post_count DESC, u.username ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$searchTerm]);
    $authors = $stmt->fetchAll();
    
    // Search Categories
    $sql = "SELECT 
                bp.category,
                COUNT(*) as post_count
            FROM blogpost bp
            WHERE bp.category LIKE ?
            AND bp.category IS NOT NULL 
            AND bp.category != ''
            GROUP BY bp.category
            ORDER BY post_count DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$searchTerm]);
    $categories = $stmt->fetchAll();
}

$totalResults = count($posts) + count($authors) + count($categories);

include __DIR__ . '/../includes/header.php';
?>

<style>
/* Search Header */
/* Search Header */
/* ============================================
   SEARCH PAGE STYLES - FIXED VERSION
   Add this to your search.php <style> section
   ============================================ */

/* Search Header */
.search-header {
    background: #ffffff;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-title {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.search-query {
    color: #3b82f6;
    font-weight: 600;
}

.search-stats {
    display: flex;
    gap: 2rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid #e5e7eb;
}

.search-stat {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
}

.search-stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

/* Search Tabs - FIXED */
.search-tabs {
    background: #ffffff;
    border-radius: 12px;
    padding: 0.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 0.5rem;
}

.search-tab {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 600;
    text-align: center;
    transition: all 0.2s;
    border: 2px solid transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: transparent;
}

.search-tab:hover {
    background: #f3f4f6;
    color: #111827;
}

.search-tab.active {
    background: #111827;
    color: #ffffff !important;
    border-color: #111827;
}

.tab-count {
    background: rgba(107, 114, 128, 0.2);
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.875rem;
    color: inherit;
}

.search-tab.active .tab-count {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

/* Blog Card for Search Results */
.blog-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.blog-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.blog-card-content {
    display: grid;
    grid-template-columns: 1fr 200px;
    gap: 2rem;
    align-items: start;
}

.blog-card-author {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

/* Author Card */
.author-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.author-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.author-card-content {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 1.5rem;
    align-items: start;
}

.author-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e5e7eb;
}

.author-avatar-default {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    font-weight: 700;
}

.author-info {
    flex: 1;
}

.author-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.author-bio {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.author-stats {
    display: flex;
    gap: 1.5rem;
    margin-top: 0.75rem;
}

.author-stat {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.9375rem;
}

.author-stat strong {
    color: #111827;
}

.author-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: stretch;
}

.follow-btn-search {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    border: 2px solid #3b82f6;
    background: #3b82f6;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    text-align: center;
}

.follow-btn-search:hover {
    background: #2563eb;
    border-color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.follow-btn-search.following {
    background: white;
    color: #111827;
    border-color: #e5e7eb;
}

.follow-btn-search.following:hover {
    background: #fee2e2;
    border-color: #ef4444;
    color: #ef4444;
}

.view-profile-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    background: #f3f4f6;
    color: #111827;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
    border: 2px solid #e5e7eb;
}

.view-profile-btn:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Category Card */
.category-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.category-card-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.category-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.category-info {
    flex: 1;
    margin-left: 1.5rem;
}

.category-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    text-transform: capitalize;
}

.category-count {
    color: #6b7280;
    font-size: 0.9375rem;
    margin-top: 0.25rem;
}

.view-category-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    background: #111827;
    color: white;
    text-decoration: none;
    transition: all 0.2s;
    border: 2px solid #111827;
}

.view-category-btn:hover {
    background: #1f2937;
    border-color: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.3);
}

/* Category colors */
.category-icon.technology { background: #dbeafe; }
.category-icon.science { background: #d1fae5; }
.category-icon.art { background: #fce7f3; }
.category-icon.ai { background: #e0e7ff; }
.category-icon.business { background: #fef3c7; }
.category-icon.lifestyle { background: #fee2e2; }
.category-icon.education { background: #f3e8ff; }
.category-icon.health { background: #d1fae5; }
.category-icon.travel { background: #dbeafe; }
.category-icon.food { background: #fed7aa; }
.category-icon.other { background: #f3f4f6; }

/* Empty State */
.empty-state {
    background: #ffffff;
    border-radius: 12px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin: 2rem 0;
}

.empty-state h2,
.empty-state h3 {
    color: #111827;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.empty-state ul {
    list-style-position: inside;
}

/* Admin Badge */
.admin-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #111827;
    color: white;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-left: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-stats {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .search-tabs {
        flex-direction: column;
    }
    
    .search-tab {
        width: 100%;
    }
    
    .blog-card-content {
        grid-template-columns: 1fr;
    }
    
    .author-card-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .author-avatar-large,
    .author-avatar-default {
        margin: 0 auto;
    }
    
    .author-stats {
        justify-content: center;
    }
    
    .author-actions {
        margin: 0 auto;
        max-width: 300px;
    }
    
    .category-card-content {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }
    
    .category-info {
        margin-left: 0;
    }
}

</style>

<main class="main-content">
    <div class="container">
        <!-- Search Header -->
        <div class="search-header">
            <h1 class="search-title">
                <?php if ($searchQuery): ?>
                    Search Results for <span class="search-query">"<?php echo htmlspecialchars($searchQuery); ?>"</span>
                <?php else: ?>
                    Search
                <?php endif; ?>
            </h1>
            
            <?php if ($searchQuery): ?>
                <div class="search-stats">
                    <div class="search-stat">
                        <span class="search-stat-number"><?php echo $totalResults; ?></span>
                        <span>Total Results</span>
                    </div>
                    <div class="search-stat">
                        <span class="search-stat-number"><?php echo count($posts); ?></span>
                        <span>Posts</span>
                    </div>
                    <div class="search-stat">
                        <span class="search-stat-number"><?php echo count($authors); ?></span>
                        <span>Authors</span>
                    </div>
                    <div class="search-stat">
                        <span class="search-stat-number"><?php echo count($categories); ?></span>
                        <span>Categories</span>
                    </div>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/index.php" style="color: #4f46e5; font-weight: 600; margin-top: 1rem; display: inline-block;">
                    ← Back to all posts
                </a>
            <?php else: ?>
                <p style="color: #6b7280; margin-top: 0.5rem;">Enter a search term to find posts, authors, and categories</p>
            <?php endif; ?>
        </div>

        <?php if ($searchQuery && $totalResults > 0): ?>
            <!-- Tab Navigation -->
            <div class="search-tabs">
                <a href="?q=<?php echo urlencode($searchQuery); ?>&tab=posts" 
                   class="search-tab <?php echo $activeTab === 'posts' ? 'active' : ''; ?>">
                    Posts
                    <span class="tab-count"><?php echo count($posts); ?></span>
                </a>
                <a href="?q=<?php echo urlencode($searchQuery); ?>&tab=authors" 
                   class="search-tab <?php echo $activeTab === 'authors' ? 'active' : ''; ?>">
                    👥 Authors
                    <span class="tab-count"><?php echo count($authors); ?></span>
                </a>
                <a href="?q=<?php echo urlencode($searchQuery); ?>&tab=categories" 
                   class="search-tab <?php echo $activeTab === 'categories' ? 'active' : ''; ?>">
                    Categories
                    <span class="tab-count"><?php echo count($categories); ?></span>
                </a>
            </div>

            <!-- Posts Tab -->
            <?php if ($activeTab === 'posts'): ?>
                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                        <h3>No posts found</h3>
                        <p>Try searching for something else</p>
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
                        $firstImage = getFirstPostImage($post['id']);
                        $firstLetter = strtoupper(substr($post['username'], 0, 1));
                    ?>
                        <article class="blog-card">
                            <div class="blog-card-content">
                                <div class="blog-card-left">
                                    <div class="blog-card-author">
                                        <a href="author-profile.php?id=<?php echo $post['user_id']; ?>">
                                            <?php if ($authorPic): ?>
                                                <img src="<?php echo SITE_URL . htmlspecialchars($authorPic); ?>" alt="<?php echo htmlspecialchars($post['username']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;"><?php echo $firstLetter; ?></div>
                                            <?php endif; ?>
                                        </a>
                                        
                                        <div style="flex: 1;">
                                            <a href="author-profile.php?id=<?php echo $post['user_id']; ?>" style="font-weight: 600; color: #1f2937; text-decoration: none;">
                                                <?php echo htmlspecialchars($post['username']); ?>
                                            </a>
                                            <div style="font-size: 0.875rem; color: #6b7280;">
                                                <?php echo formatDate($post['created_at']); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <h2 class="blog-card-title" style="font-size: 1.5rem; font-weight: 700; margin: 1rem 0;">
                                        <a href="view-post.php?id=<?php echo $post['id']; ?>" style="color: #1f2937; text-decoration: none;">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h2>

                                    <div style="color: #6b7280; line-height: 1.6; margin-bottom: 1rem;">
                                        <?php echo htmlspecialchars(getExcerpt($post['content'], 300)); ?>
                                    </div>

                                    <a href="view-post.php?id=<?php echo $post['id']; ?>" style="color: #4f46e5; font-weight: 600; text-decoration: none;">
                                        Read more →
                                    </a>

                                    <div style="display: flex; gap: 1.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                        <span style="color: #6b7280; font-size: 0.9rem;">❤️ <?php echo $likeCount; ?></span>
                                        <span style="color: #6b7280; font-size: 0.9rem;">💬 <?php echo $commentCount; ?></span>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="width: 100%; height: 200px; border-radius: 8px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        <?php if ($firstImage): ?>
                                            <img src="<?php echo SITE_URL . htmlspecialchars($firstImage); ?>" alt="Post image" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <span style="font-size: 3rem; color: #9ca3af;">📄</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Authors Tab -->
            <?php if ($activeTab === 'authors'): ?>
                <?php if (empty($authors)): ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
                        <h3>No authors found</h3>
                        <p>Try searching for a different name</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($authors as $author): 
                        $followerCount = getFollowerCount($author['id']);
                        $isFollowingAuthor = $currentUser ? isFollowing($currentUser['id'], $author['id']) : false;
                        $firstLetter = strtoupper(substr($author['username'], 0, 1));
                    ?>
                        <div class="author-card">
                            <div class="author-card-content">
                                <div>
                                    <?php if ($author['profile_picture']): ?>
                                        <img src="<?php echo SITE_URL . htmlspecialchars($author['profile_picture']); ?>" 
                                             alt="<?php echo htmlspecialchars($author['username']); ?>" 
                                             class="author-avatar-large">
                                    <?php else: ?>
                                        <div class="author-avatar-default">
                                            <?php echo $firstLetter; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="author-info">
                                    <h3 class="author-name">
                                        <?php echo htmlspecialchars($author['username']); ?>
                                        <?php if ($author['role'] === 'admin'): ?>
                                            <span class="admin-badge">ADMIN</span>
                                        <?php endif; ?>
                                    </h3>

                                    <?php if ($author['bio']): ?>
                                        <p class="author-bio">
                                            <?php echo htmlspecialchars($author['bio']); ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="author-bio" style="font-style: italic; color: #9ca3af;">
                                            No bio yet
                                        </p>
                                    <?php endif; ?>

                                    <div class="author-stats">
                                        <div class="author-stat">
                                            <strong><?php echo $author['post_count']; ?></strong> Posts
                                        </div>
                                        <div class="author-stat">
                                            <strong><?php echo $followerCount; ?></strong> Followers
                                        </div>
                                    </div>
                                </div>

                                <div class="author-actions">
                                    <?php if ($currentUser && $currentUser['id'] != $author['id']): ?>
                                        <button class="follow-btn-search <?php echo $isFollowingAuthor ? 'following' : ''; ?>" 
                                                onclick="toggleFollowSearch(this, <?php echo $author['id']; ?>)">
                                            <?php echo $isFollowingAuthor ? 'Following' : 'Follow'; ?>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="author-profile.php?id=<?php echo $author['id']; ?>" class="view-profile-btn">
                                        View Profile →
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Categories Tab -->
            <?php if ($activeTab === 'categories'): ?>
                <?php if (empty($categories)): ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 1rem;" class="icon-tag"></div>
                        <h3>No categories found</h3>
                        <p>Try searching for a different category</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $categoryIcons = [
                        'technology' => '💻',
                        'science' => '🔬',
                        'art' => '🎨',
                        'ai' => '🤖',
                        'business' => '💼',
                        'lifestyle' => '🌟',
                        'education' => '📚',
                        'health' => '💪',
                        'travel' => '✈️',
                        'food' => '🍔',
                        'other' => '📌'
                    ];
                    ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-card-content">
                                <div class="category-icon <?php echo htmlspecialchars($category['category']); ?>">
                                    <?php echo $categoryIcons[$category['category']] ?? '📌'; ?>
                                </div>

                                <div class="category-info">
                                    <h3 class="category-name">
                                        <?php echo htmlspecialchars($category['category']); ?>
                                    </h3>
                                    <p class="category-count">
                                        <?php echo $category['post_count']; ?> post<?php echo $category['post_count'] != 1 ? 's' : ''; ?>
                                    </p>
                                </div>

                                <a href="<?php echo SITE_URL; ?>/index.php?category=<?php echo urlencode($category['category']); ?>" 
                                   class="view-category-btn">
                                    View Posts →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

        <?php elseif ($searchQuery && $totalResults === 0): ?>
            <div class="empty-state">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
                <h2 style="color: #1f2937; margin-bottom: 1rem;">No results found</h2>
                <p style="color: #6b7280; margin-bottom: 2rem;">
                    We couldn't find anything matching "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"
                </p>
                <p style="color: #6b7280;">Try:</p>
                <ul style="color: #6b7280; text-align: left; max-width: 400px; margin: 1rem auto;">
                    <li>Using different keywords</li>
                    <li>Checking your spelling</li>
                    <li>Using more general terms</li>
                </ul>
                <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-primary" style="margin-top: 2rem;">
                    Browse All Posts
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleFollowSearch(button, userId) {
    <?php if (!$currentUser): ?>
        alert('Please login to follow users');
        return;
    <?php endif; ?>
    
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Loading...';
    
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
    
    fetch('<?php echo SITE_URL; ?>/api/toggle-follow-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            if (result.following) {
                button.classList.add('following');
                button.textContent = 'Following';
            } else {
                button.classList.remove('following');
                button.textContent = 'Follow';
            }
            showToast(result.following ? '✓ Now following!' : 'Unfollowed');
        } else {
            alert(result.message);
            button.textContent = originalText;
        }
        button.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
        button.textContent = originalText;
        button.disabled = false;
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; font-weight: 600;';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
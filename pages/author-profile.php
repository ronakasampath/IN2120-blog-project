<?php
/**
 * Author Profile Page - Professional Publication Design
 * Save as: pages/author-profile.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';
require_once __DIR__ . '/../includes/blog-enhanced.php';
require_once __DIR__ . '/../includes/follow-functions.php';
require_once __DIR__ . '/../includes/image-functions.php';

$authorId = intval($_GET['id'] ?? 0);

if (!$authorId) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$author = getUserProfile($authorId);

if (!$author) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$currentUser = getCurrentUser();
$isOwnProfile = $currentUser && $currentUser['id'] == $authorId;
$isFollowing = $currentUser && !$isOwnProfile ? isFollowing($currentUser['id'], $authorId) : false;

$followerCount = getFollowerCount($authorId);
$followingCount = getFollowingCount($authorId);
$authorPosts = getPostsByUser($authorId);

$pageTitle = htmlspecialchars($author['username']) . "'s Profile";

include __DIR__ . '/../includes/header.php';
?>

<style>
.profile-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 3rem 2rem;
}

/* Profile Header - Clean & Centered */
.profile-header-card {
    background: white;
    border-radius: 12px;
    padding: 3rem 2.5rem;
    margin-bottom: 3rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    text-align: center;
}

.profile-avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.profile-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e5e7eb;
}

.profile-avatar-default-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #111827 0%, #374151 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
    font-weight: 700;
}

.profile-name {
    font-size: 2.25rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 0.75rem;
    letter-spacing: -0.02em;
}

.profile-member-since {
    color: #9ca3af;
    font-size: 0.9375rem;
    margin-bottom: 1.5rem;
}

/* Stats - Minimal Design */
.profile-stats {
    display: flex;
    gap: 3rem;
    justify-content: center;
    padding: 2rem 0;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    margin: 2rem 0;
}

.profile-stat {
    text-align: center;
}

.profile-stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    display: block;
    margin-bottom: 0.25rem;
}

.profile-stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

/* Bio - Subtle */
.profile-bio {
    color: #4b5563;
    line-height: 1.7;
    margin: 2rem 0;
    font-size: 1.0625rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* Follow Button - Dot Style */
.follow-dot-btn-profile {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.875rem 2rem;
    border-radius: 24px;
    font-weight: 600;
    border: 1px solid #111827;
    background: #111827;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.9375rem;
}

.follow-dot-btn-profile:hover {
    background: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.2);
}

.follow-dot-btn-profile.following {
    background: white;
    color: #111827;
}

.follow-dot-btn-profile.following:hover {
    background: #f9fafb;
}

.follow-dot-profile {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

/* Posts Section */
.posts-section {
    margin-top: 3rem;
}

.section-header {
    margin-bottom: 2.5rem;
    text-align: center;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.section-subtitle {
    color: #6b7280;
    font-size: 0.9375rem;
}

/* Blog Card - Simplified */
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
    padding: 2rem;
}

.blog-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.blog-card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    flex: 1;
}

.blog-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

.blog-card-title a:hover {
    color: #3b82f6;
}

.blog-card-category {
    padding: 0.375rem 0.875rem;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    white-space: nowrap;
    background: #f3f4f6;
    color: #6b7280;
    opacity: 0.7;
}

.blog-card-date {
    font-size: 0.875rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.blog-card-excerpt {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 1.25rem;
}

.blog-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.blog-card-stats {
    display: flex;
    gap: 1.5rem;
    color: #6b7280;
    font-size: 0.9375rem;
}

.read-more {
    color: #3b82f6;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
    font-size: 0.9375rem;
}

.read-more:hover {
    color: #2563eb;
}

/* Empty State */
.empty-state {
    background: white;
    border-radius: 12px;
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #9ca3af;
}

/* Back Link */
.back-link {
    margin-top: 3rem;
    text-align: center;
}

.back-link a {
    color: #6b7280;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: color 0.2s;
}

.back-link a:hover {
    color: #111827;
}

@media (max-width: 768px) {
    .profile-container {
        padding: 2rem 1.25rem;
    }
    
    .profile-header-card {
        padding: 2rem 1.5rem;
    }
    
    .profile-name {
        font-size: 1.875rem;
    }
    
    .profile-stats {
        gap: 2rem;
    }
    
    .blog-card-content {
        padding: 1.5rem;
    }
}
</style>

<div class="profile-container">
    <div class="profile-header-card">
        <div class="profile-avatar-section">
            <?php if ($author['profile_picture']): ?>
                <img src="<?php echo SITE_URL . htmlspecialchars($author['profile_picture']); ?>" 
                     alt="<?php echo htmlspecialchars($author['username']); ?>" 
                     class="profile-avatar-large">
            <?php else: ?>
                <div class="profile-avatar-default-large">
                    <?php echo strtoupper(substr($author['username'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$isOwnProfile && $currentUser): ?>
                <button id="followBtn" 
                        class="follow-dot-btn-profile <?php echo $isFollowing ? 'following' : ''; ?>" 
                        onclick="toggleFollow(<?php echo $authorId; ?>)">
                    <span class="follow-dot-profile"></span>
                    <span id="followBtnText">
                        <?php echo $isFollowing ? 'Following' : 'Follow'; ?>
                    </span>
                </button>
            <?php elseif ($isOwnProfile): ?>
                <a href="<?php echo SITE_URL; ?>/pages/profile.php" class="btn btn-primary">
                    Edit Profile
                </a>
            <?php endif; ?>
        </div>
        
        <h1 class="profile-name">
            <?php echo htmlspecialchars($author['username']); ?>
            <?php if ($author['role'] === 'admin'): ?>
                <span class="admin-badge">ADMIN</span>
            <?php endif; ?>
        </h1>
        
        <p class="profile-member-since">
            Member since <?php echo date('F Y', strtotime($author['created_at'])); ?>
        </p>
        
        <?php if ($author['bio']): ?>
            <div class="profile-bio">
                <?php echo nl2br(htmlspecialchars($author['bio'])); ?>
            </div>
        <?php else: ?>
            <div class="profile-bio" style="color: #9ca3af; font-style: italic;">
                No bio yet
            </div>
        <?php endif; ?>
        
        <div class="profile-stats">
            <div class="profile-stat">
                <span class="profile-stat-number"><?php echo count($authorPosts); ?></span>
                <span class="profile-stat-label">Posts</span>
            </div>
            <div class="profile-stat">
                <span class="profile-stat-number" id="followerCount"><?php echo $followerCount; ?></span>
                <span class="profile-stat-label">Followers</span>
            </div>
            <div class="profile-stat">
                <span class="profile-stat-number"><?php echo $followingCount; ?></span>
                <span class="profile-stat-label">Following</span>
            </div>
        </div>
    </div>

    <div class="posts-section">
        <div class="section-header">
            <h2 class="section-title">
                Posts by <?php echo htmlspecialchars($author['username']); ?>
            </h2>
            <p class="section-subtitle"><?php echo count($authorPosts); ?> article<?php echo count($authorPosts) !== 1 ? 's' : ''; ?> published</p>
        </div>

        <?php if (empty($authorPosts)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h3>No posts yet</h3>
                <p>This user hasn't published any posts.</p>
            </div>
        <?php else: ?>
            <?php foreach ($authorPosts as $post): 
                $likeCount = getLikeCount($post['id']);
                $commentCount = getCommentCount($post['id']);
            ?>
                <article class="blog-card">
                    <div class="blog-card-content">
                        <div class="blog-card-header">
                            <h2 class="blog-card-title">
                                <a href="<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <?php if (!empty($post['category'])): ?>
                                <span class="blog-card-category">
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="blog-card-date">
                            <?php echo formatDate($post['created_at']); ?>
                        </div>
                        
                        <div class="blog-card-excerpt">
                            <?php echo htmlspecialchars(getExcerpt($post['content'], 200)); ?>
                        </div>
                        
                        <div class="blog-card-footer">
                            <div class="blog-card-stats">
                                <span>❤️ <?php echo $likeCount; ?></span>
                                <span>💬 <?php echo $commentCount; ?></span>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $post['id']; ?>" 
                               class="read-more">
                                Read more →
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="back-link">
        <a href="<?php echo SITE_URL; ?>/index.php">
            ← Back to Home
        </a>
    </div>
</div>

<script>
function toggleFollow(userId) {
    const followBtn = document.getElementById('followBtn');
    const followBtnText = document.getElementById('followBtnText');
    const followerCount = document.getElementById('followerCount');
    
    const originalText = followBtnText.textContent;
    followBtnText.textContent = '...';
    followBtn.disabled = true;
    
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
                followBtn.classList.add('following');
                followBtnText.textContent = 'Following';
            } else {
                followBtn.classList.remove('following');
                followBtnText.textContent = 'Follow';
            }
            followerCount.textContent = result.follower_count;
        } else {
            alert(result.message);
            followBtnText.textContent = originalText;
        }
        followBtn.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
        followBtnText.textContent = originalText;
        followBtn.disabled = false;
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
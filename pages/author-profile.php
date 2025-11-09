<?php
/**
 * Author Profile Page - View Other Users' Profiles
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
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.profile-header-card {
    background: white;
    border-radius: 12px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.profile-header-content {
    display: flex;
    gap: 2rem;
    align-items: start;
    flex-wrap: wrap;
}

.profile-avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.profile-avatar-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #e5e7eb;
}

.profile-avatar-default-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 4rem;
    font-weight: 700;
}

.profile-info-section {
    flex: 1;
    min-width: 300px;
}

.profile-name {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.profile-stats {
    display: flex;
    gap: 2rem;
    margin: 1.5rem 0;
    padding: 1rem 0;
    border-top: 2px solid #e5e7eb;
    border-bottom: 2px solid #e5e7eb;
}

.profile-stat {
    text-align: center;
}

.profile-stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #4f46e5;
    display: block;
}

.profile-stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    text-transform: uppercase;
    font-weight: 600;
}

.profile-bio {
    color: #4b5563;
    line-height: 1.6;
    margin-top: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    font-size: 1.05rem;
}

.follow-btn {
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    border: 2px solid #4f46e5;
    background: #4f46e5;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1rem;
}

.follow-btn:hover {
    background: #4338ca;
    border-color: #4338ca;
    transform: translateY(-2px);
}

.follow-btn.following {
    background: white;
    color: #4f46e5;
}

.follow-btn.following:hover {
    background: #fee2e2;
    border-color: #ef4444;
    color: #ef4444;
}

.posts-section {
    margin-top: 2rem;
}

.section-header {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

/* Reuse blog card styles from index */
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

.blog-card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.75rem;
}

.blog-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

.blog-card-title a:hover {
    color: #4f46e5;
}

.blog-card-date {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1rem;
}

.blog-card-excerpt {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 1rem;
    flex: 1;
}

.blog-card-right {
    display: flex;
    align-items: center;
    justify-content: center;
}

.blog-card-image {
    width: 100%;
    height: 200px;
    border-radius: 8px;
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

.read-more {
    color: #4f46e5;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.read-more:hover {
    color: #4338ca;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .profile-header-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .profile-stats {
        justify-content: center;
    }
    
    .blog-card-content {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="profile-container">
    <div class="profile-header-card">
        <div class="profile-header-content">
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
                            class="follow-btn <?php echo $isFollowing ? 'following' : ''; ?>" 
                            onclick="toggleFollow(<?php echo $authorId; ?>)">
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
            
            <div class="profile-info-section">
                <h1 class="profile-name">
                    <?php echo htmlspecialchars($author['username']); ?>
                    <?php if ($author['role'] === 'admin'): ?>
                        <span class="admin-badge">ADMIN</span>
                    <?php endif; ?>
                </h1>
                
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
                
                <?php if ($author['bio']): ?>
                    <div class="profile-bio">
                        <?php echo nl2br(htmlspecialchars($author['bio'])); ?>
                    </div>
                <?php else: ?>
                    <div class="profile-bio" style="color: #9ca3af; font-style: italic;">
                        No bio yet
                    </div>
                <?php endif; ?>
                
                <p style="color: #9ca3af; font-size: 0.875rem; margin-top: 1rem;">
                    Member since <?php echo date('F Y', strtotime($author['created_at'])); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="posts-section">
        <div class="section-header">
            <h2 class="section-title">
                Posts by <?php echo htmlspecialchars($author['username']); ?> (<?php echo count($authorPosts); ?>)
            </h2>
        </div>

        <?php if (empty($authorPosts)): ?>
            <div class="empty-state" style="background: white; border-radius: 12px; padding: 3rem; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                <h3 style="color: #6b7280;">No posts yet</h3>
                <p style="color: #9ca3af;">This user hasn't published any posts.</p>
            </div>
        <?php else: ?>
            <?php foreach ($authorPosts as $post): 
                $likeCount = getLikeCount($post['id']);
                $commentCount = getCommentCount($post['id']);
                $firstImage = getFirstPostImage($post['id']);
            ?>
                <article class="blog-card">
                    <div class="blog-card-content">
                        <div class="blog-card-left">
                            <h2 class="blog-card-title">
                                <a href="<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            
                            <div class="blog-card-date">
                                <?php echo formatDate($post['created_at']); ?>
                            </div>
                            
                            <div class="blog-card-excerpt">
                                <?php echo htmlspecialchars(getExcerpt($post['content'], 300)); ?>
                            </div>
                            
                            <a href="<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $post['id']; ?>" 
                               class="read-more">
                                Read more →
                            </a>
                        </div>

                        <div class="blog-card-right">
                            <div class="blog-card-image">
                                <?php if ($firstImage): ?>
                                    <img src="<?php echo SITE_URL . htmlspecialchars($firstImage); ?>" alt="Post image">
                                <?php else: ?>
                                    <span>📄</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">
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
    followBtnText.textContent = 'Loading...';
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

<?php include __DIR__ . '/../includes/footer.php'; ?></document_content></document>
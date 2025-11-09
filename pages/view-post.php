<?php
/**
 * View Single Blog Post - COMPLETE FIXED VERSION
 * Save as: pages/view-post.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';
require_once __DIR__ . '/../includes/blog-enhanced.php';
require_once __DIR__ . '/../includes/image-functions.php';

$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$postId) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$post = getPost($postId);

if (!$post) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$pageTitle = htmlspecialchars($post['title']);
$currentUser = getCurrentUser();
$canEdit = $currentUser && ($currentUser['id'] == $post['user_id'] || isAdmin());

$likeCount = getLikeCount($postId);
$commentCount = getCommentCount($postId);
$userLiked = $currentUser ? hasUserLiked($postId, $currentUser['id']) : false;
$comments = getComments($postId);
$postImages = getPostImages($postId);

include __DIR__ . '/../includes/header.php';
?>

<style>
.post-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.post-header-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.post-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.post-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #6b7280;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
    flex-wrap: wrap;
}

.post-author {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
}

.edit-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.post-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.post-images-grid img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.post-images-grid img:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.post-content-card {
    background: white;
    border-radius: 12px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* CRITICAL: Markdown Content Styling */
.post-content {
    line-height: 1.8;
    font-size: 1.05rem;
    color: #374151;
}

.post-content h1 {
    font-size: 2.25rem;
    margin: 2rem 0 1.5rem;
    color: #1f2937;
    font-weight: 700;
    line-height: 1.3;
    border-bottom: 3px solid #4f46e5;
    padding-bottom: 0.5rem;
}

.post-content h2 {
    font-size: 1.875rem;
    margin: 1.75rem 0 1.25rem;
    color: #1f2937;
    font-weight: 700;
    line-height: 1.3;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0.5rem;
}

.post-content h3 {
    font-size: 1.5rem;
    margin: 1.5rem 0 1rem;
    color: #1f2937;
    font-weight: 600;
    line-height: 1.3;
}

.post-content p {
    margin-bottom: 1.25rem;
    line-height: 1.8;
}

.post-content ul, .post-content ol {
    margin: 1.5rem 0;
    padding-left: 2.5rem;
}

.post-content ul {
    list-style-type: disc;
}

.post-content ol {
    list-style-type: decimal;
}

.post-content li {
    margin-bottom: 0.75rem;
    line-height: 1.7;
    padding-left: 0.5rem;
}

.post-content li::marker {
    color: #4f46e5;
    font-weight: 600;
}

.post-content code {
    background: #fee2e2;
    color: #991b1b;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', Consolas, Monaco, monospace;
    font-size: 0.9em;
    font-weight: 600;
}

.post-content pre {
    background: #1f2937;
    color: #f3f4f6;
    padding: 1.5rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1.5rem 0;
    border-left: 4px solid #4f46e5;
}

.post-content pre code {
    background: none;
    padding: 0;
    color: inherit;
    font-weight: normal;
}

.post-content blockquote {
    border-left: 4px solid #4f46e5;
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #6b7280;
    background: #f9fafb;
    padding: 1.25rem 1.5rem;
    border-radius: 0 8px 8px 0;
    font-size: 1.1rem;
}

.post-content strong {
    font-weight: 700;
    color: #1f2937;
}

.post-content em {
    font-style: italic;
    color: #4b5563;
}

.post-content a {
    color: #4f46e5;
    text-decoration: underline;
    font-weight: 500;
    transition: color 0.2s;
}

.post-content a:hover {
    color: #4338ca;
}

.post-content hr {
    border: none;
    border-top: 2px solid #e5e7eb;
    margin: 2.5rem 0;
}

.post-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1.5rem 0;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.post-content br {
    content: "";
    display: block;
    margin: 0.75rem 0;
}

.actions-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.post-actions-bar {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.like-button, .share-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
    color: #374151;
}

.like-button:hover, .share-button:hover {
    border-color: #4f46e5;
    background: #f0f4ff;
    color: #4f46e5;
    transform: translateY(-2px);
}

.like-button.liked {
    background: #fee2e2;
    border-color: #ef4444;
    color: #ef4444;
}

.comments-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.comment-item {
    display: flex;
    gap: 1rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.comment-content {
    flex: 1;
}

.comment-author {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.comment-text {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.comment-meta {
    color: #9ca3af;
    font-size: 0.875rem;
}

.comment-form-card {
    background: #f9fafb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

#imageModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 9999;
    cursor: pointer;
}

#modalImage {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 8px;
}

.modal-close {
    position: absolute;
    top: 20px;
    right: 40px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    user-select: none;
}

@media (max-width: 768px) {
    .post-title {
        font-size: 1.75rem;
    }
    
    .post-images-grid {
        grid-template-columns: 1fr;
    }
    
    .post-actions-bar {
        flex-direction: column;
    }
    
    .post-content h1 {
        font-size: 1.75rem;
    }
    
    .post-content h2 {
        font-size: 1.5rem;
    }
}

.follow-btn-post {
    padding: 0.5rem 1rem;
    border: 2px solid #4f46e5;
    background: #4f46e5;
    color: white;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.follow-btn-post:hover {
    background: #4338ca;
    border-color: #4338ca;
    transform: translateY(-2px);
}

.follow-btn-post.following {
    background: white;
    color: #4f46e5;
}

.follow-btn-post.following:hover {
    background: #fee2e2;
    border-color: #ef4444;
    color: #ef4444;
}
</style>

<div class="post-container">
    <div class="post-header-card">
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        
        <div class="post-meta">
    <div class="post-author">
        <!-- Make author name clickable -->
        <a href="<?php echo SITE_URL; ?>/pages/author-profile.php?id=<?php echo $post['user_id']; ?>" 
           style="color: inherit; text-decoration: none; font-weight: 600; transition: color 0.2s;">
            <span style="cursor: pointer;">
                <?php echo htmlspecialchars($post['username']); ?>
            </span>
        </a>
    </div>
    <?php 
    if ($currentUser && $currentUser['id'] != $post['user_id']): 
        require_once __DIR__ . '/../includes/follow-functions.php';
        $isFollowingAuthor = isFollowing($currentUser['id'], $post['user_id']);
    ?>
        <span>|</span>
        <button id="followBtnPost" 
                class="follow-btn-post <?php echo $isFollowingAuthor ? 'following' : ''; ?>" 
                onclick="toggleFollowOnPost(<?php echo $post['user_id']; ?>)">
            <span id="followBtnTextPost">
                <?php echo $isFollowingAuthor ? '✓ Following' : '+ Follow'; ?>
            </span>
        </button>
    <?php endif; ?>
</div>

    
    <span>|</span>
    
    <div>
        <?php echo formatDate($post['created_at']); ?>
    </div>

        <?php if ($canEdit): ?>
            <div class="edit-actions">
                <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">
                    Edit Post
                </a>
                <a href="manage-images.php?id=<?php echo $post['id']; ?>" class="btn btn-success btn-sm">
                    Manage Images
                </a>
                <button onclick="deletePost(<?php echo $post['id']; ?>)" class="btn btn-danger btn-sm">
                    Delete Post
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($postImages)): ?>
        <div class="post-images-grid">
            <?php foreach ($postImages as $image): ?>
                <img src="<?php echo SITE_URL . htmlspecialchars($image['image_path']); ?>" 
                     alt="Post image" 
                     onclick="openImageModal(this.src)">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="post-content-card">
        <div class="post-content" id="postContent">
            <p style="color: #9ca3af; text-align: center;">Loading content...</p>
        </div>
    </div>

    <div class="actions-card">
        <div class="post-actions-bar">
            <button onclick="toggleLike(<?php echo $post['id']; ?>)" 
                    class="like-button <?php echo $userLiked ? 'liked' : ''; ?>" 
                    id="likeBtn">
                <span id="likeCount"><?php echo $likeCount; ?></span>
                <span>Like<?php echo $likeCount != 1 ? 's' : ''; ?></span>
            </button>

            <button onclick="document.getElementById('commentTextarea')?.scrollIntoView({behavior: 'smooth'})" 
                    class="share-button">
                <span id="commentCount"><?php echo $commentCount; ?></span>
                <span>Comment<?php echo $commentCount != 1 ? 's' : ''; ?></span>
            </button>

            <button onclick="copyPostUrl()" class="share-button">
                <span>Share</span>
            </button>
        </div>
    </div>

    <div class="comments-section">
        <h3>Comments (<?php echo $commentCount; ?>)</h3>

        <?php if ($currentUser): ?>
            <div class="comment-form-card">
                <textarea id="commentTextarea" 
                          class="form-control" 
                          rows="3" 
                          placeholder="Write a comment..."></textarea>
                <button type="button" onclick="postComment()" class="btn btn-primary" style="margin-top: 0.75rem;">
                    Post Comment
                </button>
            </div>
        <?php else: ?>
            <p style="margin: 1.5rem 0; padding: 1rem; background: #f9fafb; border-radius: 8px; text-align: center;">
                <a href="<?php echo SITE_URL; ?>/pages/login.php" style="color: #4f46e5; font-weight: 600;">
                    🔐 Login
                </a> to leave a comment
            </p>
        <?php endif; ?>

        <div id="commentsList" style="margin-top: 2rem;">
            <?php if (empty($comments)): ?>
                <p style="color: #9ca3af; text-align: center; padding: 2rem;">
                    📭 No comments yet. Be the first to comment!
                </p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item" id="comment-<?php echo $comment['id']; ?>">
                        <div class="comment-avatar">
                            <?php if (isset($comment['profile_picture']) && $comment['profile_picture']): ?>
                                <img src="<?php echo SITE_URL . htmlspecialchars($comment['profile_picture']); ?>" 
                                     alt="<?php echo htmlspecialchars($comment['username']); ?>">
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                    <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="comment-content">
                            <div class="comment-author">
                                <?php echo htmlspecialchars($comment['username']); ?>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                            </div>
                            <div class="comment-meta">
                                🕒 <?php echo formatDate($comment['created_at']); ?>
                                <?php if ($currentUser && ($currentUser['id'] == $comment['user_id'] || isAdmin())): ?>
                                    <button onclick="deleteComment(<?php echo $comment['id']; ?>)" 
                                            style="margin-left: 1rem; color: #ef4444; background: none; border: none; cursor: pointer; font-weight: 600;">
                                        🗑️ Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">
            ← Back to All Posts
        </a>
    </div>
</div>

<div id="imageModal" onclick="closeImageModal()">
    <img id="modalImage">
    <span class="modal-close" onclick="closeImageModal()">×</span>
</div>

<script>
console.log('=== VIEW POST PAGE ===');
console.log('Post ID:', <?php echo $postId; ?>);

// Get post content
const postContent = <?php echo json_encode($post['content']); ?>;
console.log('Raw content:', postContent);

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderContent);
} else {
    renderContent();
}

function renderContent() {
    console.log('Rendering content...');
    
    // Check if renderMarkdown exists
    if (typeof renderMarkdown !== 'function') {
        console.error('❌ renderMarkdown function not found!');
        console.error('Make sure main.js is loaded');
        document.getElementById('postContent').innerHTML = '<div style="padding: 2rem; background: #fee2e2; color: #991b1b; border-radius: 8px;"><strong>Error:</strong> Markdown renderer not loaded. Content displayed as plain text:</div><pre style="margin-top: 1rem; background: #f3f4f6; padding: 1rem; border-radius: 8px;">' + postContent + '</pre>';
        return;
    }
    
    console.log('✅ renderMarkdown function found');
    
    try {
        const rendered = renderMarkdown(postContent);
        console.log('Rendered HTML:', rendered);
        document.getElementById('postContent').innerHTML = rendered;
        console.log('✅ Content rendered successfully');
    } catch (error) {
        console.error('❌ Error rendering markdown:', error);
        document.getElementById('postContent').innerHTML = '<div style="padding: 2rem; background: #fee2e2; color: #991b1b; border-radius: 8px;"><strong>Error:</strong> ' + error.message + '</div>';
    }
}

function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').style.display = 'block';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

document.getElementById('modalImage')?.addEventListener('click', (e) => e.stopPropagation());

function deletePost(postId) {
    if (!confirm('⚠️ Delete this post permanently? This action cannot be undone.')) return;

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
            showToast('Post deleted successfully');
            setTimeout(() => window.location.href = '<?php echo SITE_URL; ?>/index.php', 1000);
        } else {
            alert('❌ Error: ' + result.message);
        }
    })
    .catch(error => alert('❌ Error occurred'));
}

function toggleLike(postId) {
    <?php if (!$currentUser): ?>
        alert('Please login to like posts');
        return;
    <?php endif; ?>

    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');

    fetch('<?php echo SITE_URL; ?>/api/toggle-like-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const likeBtn = document.getElementById('likeBtn');
            const likeCount = document.getElementById('likeCount');
            
            if (result.liked) {
                likeBtn.classList.add('liked');
            } else {
                likeBtn.classList.remove('liked');
            }
            
            likeCount.textContent = result.like_count;
        }
    })
    .catch(error => console.error('Error:', error));
}

function copyPostUrl() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        showToast('✅ Link copied to clipboard!');
    }).catch(() => {
        prompt('Copy this link:', url);
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; font-weight: 600;';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function postComment() {
    const commentText = document.getElementById('commentTextarea').value.trim();
    
    if (!commentText) {
        alert('Please write a comment');
        return;
    }
    
    const formData = new FormData();
    formData.append('post_id', <?php echo $postId; ?>);
    formData.append('comment_text', commentText);
    formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
    
    fetch('<?php echo SITE_URL; ?>/api/add-comment-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('✅ Comment posted successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error posting comment');
    });
}

function deleteComment(commentId) {
    if (!confirm('Delete this comment?')) return;

    const formData = new FormData();
    formData.append('comment_id', commentId);
    formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');

    fetch('<?php echo SITE_URL; ?>/api/delete-comment-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('comment-' + commentId).remove();
            showToast('✅ Comment deleted');
            
            const countElem = document.getElementById('commentCount');
            countElem.textContent = parseInt(countElem.textContent) - 1;
        } else {
            alert('❌ Error: ' + result.message);
        }
    });
}


function toggleFollowOnPost(userId) {
    const followBtn = document.getElementById('followBtnPost');
    const followBtnText = document.getElementById('followBtnTextPost');
    
    const originalText = followBtnText.textContent;
    followBtn.disabled = true;
    followBtnText.textContent = 'Loading...';
    
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
                followBtnText.textContent = '✓ Following';
            } else {
                followBtn.classList.remove('following');
                followBtnText.textContent = '+ Follow';
            }
            showToast(result.following ? 'Now following!' : 'Unfollowed');
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
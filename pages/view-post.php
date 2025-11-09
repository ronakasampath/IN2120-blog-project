<?php
/**
 * View Single Blog Post - ULTRA PROFESSIONAL PUBLICATION DESIGN
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
/* Professional Article Container - Wider for better readability */
.article-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 3rem 2rem;
}

/* Category Tag - Subtle and Understated */
.category-tag-subtle {
    display: inline-block;
    margin-bottom: 1.25rem;
    color: #9ca3af;
    font-size: 0.8125rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    opacity: 0.7;
}

/* Article Header - Clean Typography */
.article-header {
    margin-bottom: 3rem;
}

.article-title {
    font-size: 3rem;
    font-weight: 800;
    color: #111827;
    line-height: 1.15;
    margin: 0 0 2rem;
    letter-spacing: -0.03em;
}

/* Author Byline - Elegant & Professional */
.article-byline {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
    font-size: 0.9375rem;
    flex-wrap: wrap;
}

.byline-author {
    display: flex;
    align-items: center;
}

.byline-author a {
    color: #111827;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.byline-author a:hover {
    color: #3b82f6;
}

.byline-separator {
    color: #d1d5db;
    font-weight: 300;
    margin: 0 0.25rem;
}

.byline-date {
    color: #9ca3af;
    font-weight: 400;
}

/* Follow Button - Minimal Dot Design */
.follow-dot-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0;
    background: none;
    border: none;
    color: #3b82f6;
    font-weight: 500;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: opacity 0.2s;
    margin: 0;
}

.follow-dot-btn:hover {
    opacity: 0.75;
}

.follow-dot-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.follow-dot-btn.following {
    color: #111827;
}

.follow-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

/* Edit Actions - Minimal */
.edit-actions-minimal {
    display: flex;
    gap: 0.75rem;
    margin-left: auto;
}

.edit-btn {
    padding: 0.5rem 1rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    white-space: nowrap;
}

.edit-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #374151;
}

.edit-btn-danger {
    border-color: #fecaca;
    color: #dc2626;
}

.edit-btn-danger:hover {
    background: #fef2f2;
    border-color: #fca5a5;
}

/* Images - Professional Layout */
.article-images {
    margin: 3rem 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.25rem;
}

.article-images.single-image {
    grid-template-columns: 1fr;
}

.article-images img {
    width: 100%;
    height: 450px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: opacity 0.2s;
}

.article-images.single-image img {
    height: 550px;
}

.article-images img:hover {
    opacity: 0.95;
}

/* Article Content - Premium Typography */
.article-content {
    font-size: 1.1875rem;
    line-height: 1.75;
    color: #374151;
    margin: 3rem 0;
}

.article-content h1 {
    font-size: 2.25rem;
    font-weight: 700;
    color: #111827;
    margin: 3rem 0 1.5rem;
    line-height: 1.25;
    letter-spacing: -0.025em;
}

.article-content h2 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin: 2.5rem 0 1.25rem;
    line-height: 1.3;
    letter-spacing: -0.02em;
}

.article-content h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin: 2rem 0 1rem;
    line-height: 1.35;
}

.article-content p {
    margin-bottom: 1.75rem;
}

.article-content ul, .article-content ol {
    margin: 1.75rem 0;
    padding-left: 2rem;
}

.article-content li {
    margin-bottom: 0.875rem;
    line-height: 1.7;
}

.article-content code {
    background: #f3f4f6;
    color: #dc2626;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.article-content pre {
    background: #1f2937;
    color: #f3f4f6;
    padding: 1.75rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 2.5rem 0;
    line-height: 1.6;
}

.article-content pre code {
    background: none;
    padding: 0;
    color: inherit;
}

.article-content blockquote {
    border-left: 3px solid #d1d5db;
    padding-left: 1.75rem;
    margin: 2.5rem 0;
    font-style: italic;
    color: #6b7280;
}

.article-content strong {
    font-weight: 700;
    color: #111827;
}

.article-content a {
    color: #3b82f6;
    text-decoration: underline;
    text-decoration-color: #bfdbfe;
    text-underline-offset: 2px;
    transition: text-decoration-color 0.2s;
}

.article-content a:hover {
    text-decoration-color: #3b82f6;
}

/* Actions Bar - Clean */
.article-actions {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 2rem 0;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    margin: 3rem 0;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.75rem 1.5rem;
    background: transparent;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #374151;
}

.action-btn.liked {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

/* Comments Section */
.comments-section {
    margin-top: 4rem;
}

.comments-header {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2.5rem;
}

.comment-form {
    margin-bottom: 3rem;
}

.comment-form textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
    resize: vertical;
    min-height: 120px;
    margin-bottom: 1rem;
    transition: border-color 0.2s;
}

.comment-form textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.comment-item {
    display: flex;
    gap: 1.25rem;
    padding: 2rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.comment-avatar {
    flex-shrink: 0;
}

.comment-avatar img,
.comment-avatar div {
    width: 44px;
    height: 44px;
    border-radius: 50%;
}

.comment-avatar div {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.25rem;
}

.comment-body {
    flex: 1;
    min-width: 0;
}

.comment-author {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.comment-text {
    color: #374151;
    line-height: 1.6;
    margin-bottom: 0.75rem;
    word-wrap: break-word;
}

.comment-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
    color: #9ca3af;
}

.comment-delete-btn {
    background: none;
    border: none;
    color: #dc2626;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    font-size: 0.875rem;
}

.comment-delete-btn:hover {
    text-decoration: underline;
}

/* Modal */
#imageModal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.95);
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
}

.modal-close {
    position: absolute;
    top: 2rem;
    right: 2.5rem;
    color: white;
    font-size: 2.5rem;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
    font-weight: 300;
}

.modal-close:hover {
    opacity: 1;
}

/* Back Link */
.back-link {
    margin-top: 4rem;
    padding-top: 2.5rem;
    border-top: 1px solid #e5e7eb;
}

.back-link a {
    color: #6b7280;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    transition: color 0.2s;
}

.back-link a:hover {
    color: #111827;
}

/* Sign-in prompt */
.signin-prompt {
    padding: 2rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 2.5rem;
}

.signin-prompt a {
    color: #3b82f6;
    font-weight: 600;
    text-decoration: none;
}

.signin-prompt a:hover {
    text-decoration: underline;
}

/* Empty state */
.empty-comments {
    color: #9ca3af;
    text-align: center;
    padding: 3rem;
    font-style: italic;
}

/* Responsive */
@media (max-width: 768px) {
    .article-container {
        padding: 2rem 1.25rem;
    }
    
    .article-title {
        font-size: 2.25rem;
        line-height: 1.2;
    }
    
    .category-tag-subtle {
        margin-bottom: 1rem;
    }
    
    .article-images {
        grid-template-columns: 1fr;
        margin: 2.5rem 0;
    }
    
    .article-images img {
        height: 280px;
    }
    
    .article-images.single-image img {
        height: 350px;
    }
    
    .article-byline {
        font-size: 0.875rem;
    }
    
    .edit-actions-minimal {
        width: 100%;
        margin-left: 0;
        margin-top: 1rem;
    }
    
    .article-content {
        font-size: 1.0625rem;
    }
    
    .article-actions {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .action-btn {
        flex: 1;
        min-width: calc(50% - 0.5rem);
        justify-content: center;
    }
}

/* Print styles */
@media print {
    .edit-actions-minimal,
    .article-actions,
    .comment-form,
    .comment-delete-btn,
    .back-link {
        display: none;
    }
}
</style>

<article class="article-container">
    <!-- Category Tag - Subtle -->
    <?php if (!empty($post['category'])): ?>
        <div class="category-tag-subtle">
            <?php echo htmlspecialchars($post['category']); ?>
        </div>
    <?php endif; ?>

    <!-- Article Header -->
    <header class="article-header">
        <h1 class="article-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        
        <div class="article-byline">
            <div class="byline-author">
                <a href="<?php echo SITE_URL; ?>/pages/author-profile.php?id=<?php echo $post['user_id']; ?>">
                    <?php echo htmlspecialchars($post['username']); ?>
                </a>
            </div>
            
            <?php if ($currentUser && $currentUser['id'] != $post['user_id']): 
                require_once __DIR__ . '/../includes/follow-functions.php';
                $isFollowingAuthor = isFollowing($currentUser['id'], $post['user_id']);
            ?>
                <span class="byline-separator">·</span>
                <button id="followBtn" 
                        class="follow-dot-btn <?php echo $isFollowingAuthor ? 'following' : ''; ?>" 
                        onclick="toggleFollow(<?php echo $post['user_id']; ?>)">
                    <span class="follow-dot"></span>
                    <span id="followText">
                        <?php echo $isFollowingAuthor ? 'Following' : 'Follow'; ?>
                    </span>
                </button>
            <?php endif; ?>
            
            <span class="byline-separator">·</span>
            <time class="byline-date"><?php echo formatDate($post['created_at']); ?></time>
            
            <?php if ($canEdit): ?>
                <div class="edit-actions-minimal">
                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="edit-btn">Edit</a>
                    <a href="manage-images.php?id=<?php echo $post['id']; ?>" class="edit-btn">Images</a>
                    <button onclick="deletePost(<?php echo $post['id']; ?>)" class="edit-btn edit-btn-danger">Delete</button>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Images -->
    <?php if (!empty($postImages)): 
        $imageCount = count($postImages);
        $imageClass = $imageCount === 1 ? 'single-image' : '';
    ?>
        <div class="article-images <?php echo $imageClass; ?>">
            <?php foreach ($postImages as $image): ?>
                <img src="<?php echo SITE_URL . htmlspecialchars($image['image_path']); ?>" 
                     alt="Article image" 
                     onclick="openImageModal(this.src)">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Content -->
    <div class="article-content" id="articleContent">
        <p style="color: #9ca3af; text-align: center;">Loading content...</p>
    </div>

    <!-- Actions -->
    <div class="article-actions">
        <button onclick="toggleLike(<?php echo $post['id']; ?>)" 
                class="action-btn <?php echo $userLiked ? 'liked' : ''; ?>" 
                id="likeBtn">
            <span id="likeCount"><?php echo $likeCount; ?></span>
            <span>Likes</span>
        </button>

        <button onclick="document.getElementById('commentTextarea')?.focus()" 
                class="action-btn">
            <span id="commentCount"><?php echo $commentCount; ?></span>
            <span>Comments</span>
        </button>

        <button onclick="copyPostUrl()" class="action-btn">
            <span>Share</span>
        </button>
    </div>

    <!-- Comments -->
    <section class="comments-section">
        <h2 class="comments-header">Comments (<?php echo $commentCount; ?>)</h2>

        <?php if ($currentUser): ?>
            <div class="comment-form">
                <textarea id="commentTextarea" 
                          placeholder="Share your thoughts..."></textarea>
                <button type="button" onclick="postComment()" class="btn btn-primary">
                    Post Comment
                </button>
            </div>
        <?php else: ?>
            <div class="signin-prompt">
                <a href="<?php echo SITE_URL; ?>/pages/login.php">Sign in</a> to join the conversation
            </div>
        <?php endif; ?>

        <div id="commentsList">
            <?php if (empty($comments)): ?>
                <p class="empty-comments">
                    No comments yet. Start the conversation!
                </p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item" id="comment-<?php echo $comment['id']; ?>">
                        <div class="comment-avatar">
                            <?php if (isset($comment['profile_picture']) && $comment['profile_picture']): ?>
                                <img src="<?php echo SITE_URL . htmlspecialchars($comment['profile_picture']); ?>" 
                                     alt="<?php echo htmlspecialchars($comment['username']); ?>">
                            <?php else: ?>
                                <div>
                                    <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="comment-body">
                            <div class="comment-author">
                                <?php echo htmlspecialchars($comment['username']); ?>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                            </div>
                            <div class="comment-meta">
                                <span><?php echo formatDate($comment['created_at']); ?></span>
                                <?php if ($currentUser && ($currentUser['id'] == $comment['user_id'] || isAdmin())): ?>
                                    <span>·</span>
                                    <button onclick="deleteComment(<?php echo $comment['id']; ?>)" 
                                            class="comment-delete-btn">
                                        Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <div class="back-link">
        <a href="<?php echo SITE_URL; ?>/index.php">
            ← Back to all posts
        </a>
    </div>
</article>

<!-- Image Modal -->
<div id="imageModal" onclick="closeImageModal()">
    <img id="modalImage" alt="Full size image">
    <span class="modal-close">×</span>
</div>

<script>
const postContent = <?php echo json_encode($post['content']); ?>;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderContent);
} else {
    renderContent();
}

function renderContent() {
    if (typeof renderMarkdown !== 'function') {
        console.error('renderMarkdown not found');
        document.getElementById('articleContent').innerHTML = '<pre>' + escapeHtml(postContent) + '</pre>';
        return;
    }
    
    try {
        const rendered = renderMarkdown(postContent);
        document.getElementById('articleContent').innerHTML = rendered;
    } catch (error) {
        console.error('Render error:', error);
        document.getElementById('articleContent').innerHTML = '<p style="color: #dc2626;">Failed to render content</p>';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleFollow(userId) {
    const btn = document.getElementById('followBtn');
    const text = document.getElementById('followText');
    const original = text.textContent;
    
    btn.disabled = true;
    text.textContent = '...';
    
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
                btn.classList.add('following');
                text.textContent = 'Following';
            } else {
                btn.classList.remove('following');
                text.textContent = 'Follow';
            }
        } else {
            text.textContent = original;
            alert(result.message || 'Failed to update follow status');
        }
        btn.disabled = false;
    })
    .catch(error => {
        console.error('Follow error:', error);
        text.textContent = original;
        btn.disabled = false;
        alert('Network error. Please try again.');
    });
}

function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
    document.body.style.overflow = '';
}

function deletePost(postId) {
    if (!confirm('Delete this post? This action cannot be undone.')) return;

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
            window.location.href = '<?php echo SITE_URL; ?>/index.php';
        } else {
            alert(result.message || 'Failed to delete post');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Network error. Please try again.');
    });
}

function toggleLike(postId) {
    <?php if (!$currentUser): ?>
        alert('Please sign in to like posts');
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
            const btn = document.getElementById('likeBtn');
            const count = document.getElementById('likeCount');
            
            if (result.liked) {
                btn.classList.add('liked');
            } else {
                btn.classList.remove('liked');
            }
            
            count.textContent = result.like_count;
        }
    })
    .catch(error => {
        console.error('Like error:', error);
    });
}

function copyPostUrl() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Link copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy link');
    });
}

function postComment() {
    const text = document.getElementById('commentTextarea').value.trim();
    
    if (!text) {
        alert('Please write a comment');
        return;
    }
    
    const formData = new FormData();
    formData.append('post_id', <?php echo $postId; ?>);
    formData.append('comment_text', text);
    formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
    
    fetch('<?php echo SITE_URL; ?>/api/add-comment-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to post comment');
        }
    })
    .catch(error => {
        console.error('Comment error:', error);
        alert('Network error. Please try again.');
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
            const comment = document.getElementById('comment-' + commentId);
            if (comment) comment.remove();
            const count = document.getElementById('commentCount');
            count.textContent = parseInt(count.textContent) - 1;
        } else {
            alert(result.message || 'Failed to delete comment');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('Network error. Please try again.');
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
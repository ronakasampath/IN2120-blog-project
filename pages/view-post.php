<?php
/**
 * View Single Blog Post - WITH ALL FEATURES
 * Save as: pages/view-post.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';
require_once __DIR__ . '/../includes/blog-enhanced.php';

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

// Get engagement data
$likeCount = getLikeCount($postId);
$commentCount = getCommentCount($postId);
$userLiked = $currentUser ? hasUserLiked($postId, $currentUser['id']) : false;
$comments = getComments($postId);

include __DIR__ . '/../includes/header.php';
?>

<main class="main-content">
    <div class="container">
        <div class="card">
            <h1 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            
            <div class="card-meta">
                By <strong><?php echo htmlspecialchars($post['username']); ?></strong> 
                on <?php echo formatDate($post['created_at']); ?>
                <?php if ($post['created_at'] != $post['updated_at']): ?>
                    <br><em>Last updated: <?php echo formatDate($post['updated_at']); ?></em>
                <?php endif; ?>
            </div>

            <?php if ($canEdit): ?>
                <div class="card-actions" style="margin-top: 1rem;">
                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">✏️ Edit</a>
                    <button onclick="deletePost(<?php echo $post['id']; ?>)" class="btn btn-danger btn-sm">🗑️ Delete</button>
                    <?php if (isAdmin() && $currentUser['id'] != $post['user_id']): ?>
                        <span class="admin-badge">ADMIN ACTION</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Featured Image -->
        <?php if (!empty($post['featured_image'])): ?>
            <div class="card">
                <img src="<?php echo SITE_URL . htmlspecialchars($post['featured_image']); ?>" alt="Featured" class="featured-image">
            </div>
        <?php endif; ?>

        <!-- Post Content -->
        <div class="card">
            <div class="post-content" id="postContent">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
        </div>

        <!-- Actions Bar: Like, Comment, Share -->
        <div class="card">
            <div class="post-actions-bar">
                <!-- Like Button -->
                <button onclick="toggleLike(<?php echo $post['id']; ?>)" class="like-button <?php echo $userLiked ? 'liked' : ''; ?>" id="likeBtn">
                    <span class="heart-icon"><?php echo $userLiked ? '❤️' : '🤍'; ?></span>
                    <span id="likeCount"><?php echo $likeCount; ?></span> 
                    <span>Like<?php echo $likeCount != 1 ? 's' : ''; ?></span>
                </button>

                <!-- Comment Count -->
                <button onclick="document.getElementById('commentForm').scrollIntoView({behavior: 'smooth'})" class="share-button">
                    💬 <span id="commentCount"><?php echo $commentCount; ?></span> Comment<?php echo $commentCount != 1 ? 's' : ''; ?>
                </button>

                <!-- Share Button -->
                <button onclick="copyPostUrl()" class="share-button">
                    🔗 Share
                </button>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card comments-section">
            <h3>Comments (<?php echo $commentCount; ?>)</h3>

            <!-- Add Comment Form -->
            <?php if ($currentUser): ?>
                <form id="commentForm" class="comment-form">
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    
                    <div class="form-group">
                        <textarea 
                            name="comment_text" 
                            class="form-control" 
                            rows="3" 
                            placeholder="Write a comment..." 
                            required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>
            <?php else: ?>
                <p><a href="<?php echo SITE_URL; ?>/pages/login.php">Login</a> to comment</p>
            <?php endif; ?>

            <!-- Display Comments -->
            <div id="commentsList" style="margin-top: 2rem;">
                <?php if (empty($comments)): ?>
                    <p style="color: var(--text-light);">No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" id="comment-<?php echo $comment['id']; ?>">
                            <div class="comment-avatar">
                                <?php if ($comment['profile_picture']): ?>
                                    <img src="<?php echo SITE_URL . htmlspecialchars($comment['profile_picture']); ?>" alt="Profile" class="profile-pic">
                                <?php else: ?>
                                    <div class="profile-pic" style="background: #e5e7eb; display: flex; align-items: center; justify-content: center;">
                                        👤
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="comment-content">
                                <div class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></div>
                                <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></div>
                                <div class="comment-meta">
                                    <?php echo formatDate($comment['created_at']); ?>
                                    <?php if ($currentUser && ($currentUser['id'] == $comment['user_id'] || isAdmin())): ?>
                                        • <button onclick="deleteComment(<?php echo $comment['id']; ?>)" style="color: var(--danger); background: none; border: none; cursor: pointer;">Delete</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">← Back to All Posts</a>
        </div>
    </div>
</main>

<script>
    const postContent = <?php echo json_encode($post['content']); ?>;
    document.getElementById('postContent').innerHTML = renderMarkdown(postContent);

    // Delete post
    function deletePost(postId) {
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
            alert(result.message);
            if (result.success) window.location.href = '<?php echo SITE_URL; ?>/index.php';
        })
        .catch(error => alert('Error occurred'));
    }

        // Post comment
    document.getElementById('commentForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Comment form submitted'); // DEBUG
        
        const commentText = this.comment_text.value;
        console.log('Comment text:', commentText); // DEBUG
        
        const formData = new FormData();
        formData.append('post_id', <?php echo $postId; ?>);
        formData.append('comment_text', commentText);
        formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
        
        console.log('Sending request to API...'); // DEBUG

        fetch('<?php echo SITE_URL; ?>/api/add-comment-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(r => {
            console.log('Response status:', r.status); // DEBUG
            return r.json();
        })
        .then(data => {
            console.log('Response data:', data); // DEBUG
            
            if (data.success) {
                alert('✅ Comment posted!');
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error); // DEBUG
            alert('❌ Error posting comment');
        });
    });


    // Toggle like
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
                const heartIcon = likeBtn.querySelector('.heart-icon');
                const likeCount = document.getElementById('likeCount');
                
                if (result.liked) {
                    likeBtn.classList.add('liked');
                    heartIcon.textContent = '❤️';
                } else {
                    likeBtn.classList.remove('liked');
                    heartIcon.textContent = '🤍';
                }
                
                likeCount.textContent = result.like_count;
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Copy post URL
    function copyPostUrl() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            showToast('✅ Link copied to clipboard!');
        }).catch(() => {
            alert('Link: ' + url);
        });
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'copy-toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Add comment
    document.getElementById('commentForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('<?php echo SITE_URL; ?>/api/add-comment-handler.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                location.reload(); // Reload to show new comment
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Error posting comment');
        }
    });

    // Delete comment
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
                alert(result.message);
            } else {
                alert(result.message);
            }
        });
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
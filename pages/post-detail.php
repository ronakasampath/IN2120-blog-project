<?php
/**
 * Single Post Detail View
 */
require_once __DIR__ . '/../includes/header.php';

// Mock Post Data (Replace with actual database fetch)
$post = [
    'id' => 1,
    'title' => 'Mastering Pure CSS Design and Tailwind Frameworks',
    'author_name' => 'Jane Doe',
    'author_id' => 200,
    'date' => 'October 23, 2025',
    'featured_image' => 'https://placehold.co/1200x400/4f46e5/ffffff?text=FEATURED+IMAGE',
    'content' => "
        <p>The transition from traditional, custom-written stylesheets to utility-first frameworks like Tailwind CSS marks a significant shift in web development methodology.</p>
        
        <h2>The Power of Pure CSS Variables</h2>
        <p>CSS variables (`:root` variables like `--primary`, `--text`) allow for global theme management.</p>

        <blockquote>This blog uses variables to define its aesthetic.</blockquote>
        
        <h3>Responsive Design with Media Queries</h3>
        <pre><code>@media (max-width: 768px) {
    .card { padding: 1rem; }
}</code></pre>
        
        <h2>Next Steps in Styling</h2>
        <ul>
            <li>More advanced `box-shadow` properties for depth.</li>
            <li>CSS Grid for complex, modern layouts.</li>
        </ul>
    ",
    'likes' => 42,
    'is_liked_by_current_user' => true
];

$comments = [
    [
        'id' => 10,
        'author' => 'Commenter1',
        'avatar' => 'https://placehold.co/40x40/f59e0b/ffffff?text=C1',
        'text' => 'Great overview! I love how clean the CSS looks.',
        'date' => '5 minutes ago'
    ],
    [
        'id' => 11,
        'author' => 'Commenter2',
        'avatar' => 'https://placehold.co/40x40/4f46e5/ffffff?text=C2',
        'text' => 'I appreciate the detailed responsive design.',
        'date' => '2 minutes ago'
    ]
];
?>

<style>
/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    font-weight: 600;
    font-size: 0.875rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 2px solid transparent;
    font-family: inherit;
}

.btn-primary {
    background: #111827;
    color: white;
    border-color: #111827;
    box-shadow: 0 1px 2px rgba(17, 24, 39, 0.1);
}

.btn-primary:hover {
    background: #1f2937;
    border-color: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.3);
}

.btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(17, 24, 39, 0.2);
}

/* Card Updates */
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card-meta {
    color: #6b7280;
    font-size: 0.875rem;
}

.auth-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

/* Featured Image */
.featured-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 8px;
    margin: 1.5rem 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

/* Post Content Styling */
.post-content {
    color: #1f2937;
    line-height: 1.8;
    font-size: 1.0625rem;
    margin: 2rem 0;
}

.post-content h2 {
    color: #1f2937;
    font-size: 1.75rem;
    font-weight: 700;
    margin: 2rem 0 1rem 0;
}

.post-content h3 {
    color: #1f2937;
    font-size: 1.375rem;
    font-weight: 600;
    margin: 1.5rem 0 0.75rem 0;
}

.post-content blockquote {
    border-left: 4px solid #111827;
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
    background: #f3f4f6;
    border-radius: 8px;
    color: #6b7280;
    font-style: italic;
}

.post-content pre {
    background: #111827;
    color: #e5e7eb;
    padding: 1.5rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.post-content code {
    background: #f3f4f6;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #111827;
}

.post-content pre code {
    background: transparent;
    padding: 0;
    color: inherit;
}

/* Post Actions Bar */
.post-actions-bar {
    display: flex;
    gap: 1rem;
    padding: 1.5rem 0;
    border-top: 2px solid #e5e7eb;
    border-bottom: 2px solid #e5e7eb;
    margin: 2rem 0;
}

.like-button,
.share-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid #e5e7eb;
    background: white;
    color: #1f2937;
}

.like-button:hover,
.share-button:hover {
    background: #f3f4f6;
    transform: translateY(-1px);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.like-button.liked {
    background: #fee2e2;
    border-color: #ef4444;
    color: #ef4444;
}

/* Comments Section */
.comments-section h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin: 3rem 0 2rem 0;
}

.comment-item {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.comment-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.comment-author {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.comment-text {
    color: #1f2937;
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.comment-meta {
    color: #9ca3af;
    font-size: 0.8125rem;
}

.profile-pic-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9375rem;
    color: #1f2937;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #111827;
    box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.1);
}

.alert-info {
    background: #dbeafe;
    color: #1e40af;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    border: 2px solid #3b82f6;
    margin-bottom: 1.5rem;
}

.alert-info a {
    color: #1e40af;
    font-weight: 600;
}

.card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .featured-image {
        height: 250px;
    }
    .post-actions-bar {
        flex-direction: column;
    }
    .card {
        padding: 1.5rem;
    }
}
</style>

<div class="card">
    <div class="card-header">
        <h1 class="auth-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="card-meta">
            Published by 
            <span style="font-weight: 600; color: var(--primary);">
                <?php echo htmlspecialchars($post['author_name']); ?>
            </span> 
            on <?php echo htmlspecialchars($post['date']); ?>
        </p>
    </div>

    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
         alt="<?php echo htmlspecialchars($post['title']); ?>" 
         class="featured-image"
         onerror="this.onerror=null; this.src='https://placehold.co/1200x400/e5e7eb/6b7280?text=Image+Not+Found';">

    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    
    <div class="post-actions-bar">
        <button id="likePostBtn" class="like-button <?php echo $post['is_liked_by_current_user'] ? 'liked' : ''; ?>" type="button">
            <i class="fas fa-heart heart-icon"></i> 
            <span>Like</span>
            (<span class="like-count"><?php echo $post['likes']; ?></span>)
        </button>

        <button id="sharePostBtn" class="share-button" type="button">
            <i class="fas fa-share-alt"></i> 
            <span>Share Link</span>
        </button>
    </div>
</div>

<div class="comments-section">
    <h2>Comments (<?php echo count($comments); ?>)</h2>

    <?php if (empty($comments)): ?>
        <p class="card-meta">Be the first to leave a comment!</p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment-item">
                <div class="comment-avatar">
                    <img src="<?php echo htmlspecialchars($comment['avatar']); ?>" 
                         alt="<?php echo htmlspecialchars($comment['author']); ?>" 
                         class="profile-pic-small">
                </div>
                <div class="comment-content">
                    <p class="comment-author">
                        <?php echo htmlspecialchars($comment['author']); ?>
                    </p>
                    <p class="comment-text">
                        <?php echo htmlspecialchars($comment['text']); ?>
                    </p>
                    <p class="comment-meta">
                        <?php echo htmlspecialchars($comment['date']); ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($currentUser): ?>
        <div class="comment-form card">
            <h3 class="card-title" style="margin-top: 0;">Leave a Comment</h3>
            <form action="<?php echo SITE_URL; ?>api/submit-comment.php" method="POST">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                
                <div class="form-group">
                    <label for="comment_content" class="form-label">Your Comment</label>
                    <textarea id="comment_content" name="content" class="form-control" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit Comment</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert-info">
            <p>Please <a href="<?php echo SITE_URL; ?>login.php">log in</a> to leave a comment.</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
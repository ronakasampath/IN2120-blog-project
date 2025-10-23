<?php
/**
 * Single Post Detail View
 * This displays a single post, including the new Post Actions Bar and Comments Section.
 */
// Path adjusted to reflect that post-detail.php is now in a subdirectory (pages/)
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
        <p>The transition from traditional, custom-written stylesheets to utility-first frameworks like Tailwind CSS marks a significant shift in web development methodology. While Tailwind provides thousands of pre-defined, small utility classes, understanding the principles of pure CSS is still fundamental to becoming an effective front-end developer.</p>
        
        <h2>The Power of Pure CSS Variables</h2>
        <p>As demonstrated in our core stylesheet, CSS variables (`:root` variables like `--primary`, `--text`) allow for global theme management. This approach makes site-wide color changes instant and reduces reliance on pre-compilation steps. This is the cornerstone of maintainable, scalable styling.</p>

        <blockquote>This blog uses variables to define its aesthetic, allowing for simple theme switching without touching the underlying HTML structure.</blockquote>
        
        <h3>Responsive Design with Media Queries</h3>
        <p>Our CSS includes simple, effective media queries:</p>
        <pre><code>@media (max-width: 768px) {
    .card { padding: 1rem; }
    .btn { width: 100%; }
}</code></pre>
        <p>These rules ensure that the user interface is fully **responsive**, adapting gracefully to smaller screens, especially for critical interactive elements like buttons and cards.</p>
        
        <h2>Next Steps in Styling</h2>
        <p>To continue enhancing the look and feel, we recommend exploring:</p>
        <ul>
            <li>More advanced `box-shadow` properties for depth.</li>
            <li>CSS Grid for complex, modern layouts.</li>
            <li>Subtle transitions on hover states, like those already implemented on `.post-item`.</li>
        </ul>
    ",
    'likes' => 42,
    'is_liked_by_current_user' => true // Mock status
];

// Mock Comments Data (Replace with actual database fetch)
$comments = [
    [
        'id' => 10,
        'author' => 'Commenter1',
        'avatar' => 'https://placehold.co/40x40/f59e0b/ffffff?text=C1',
        'text' => 'Great overview! I love how clean the CSS looks with the variables.',
        'date' => '5 minutes ago'
    ],
    [
        'id' => 11,
        'author' => 'Commenter2',
        'avatar' => 'https://placehold.co/40x40/4f46e5/ffffff?text=C2',
        'text' => 'I agree! I especially appreciate the detailed responsive design for mobile views.',
        'date' => '2 minutes ago'
    ]
];
?>

<div class="card">
    <div class="card-header">
        <!-- Title and Meta -->
        <h1 class="auth-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="card-meta">
            Published by 
            <span style="font-weight: 600; color: var(--primary);">
                <?php echo htmlspecialchars($post['author_name']); ?>
            </span> 
            on <?php echo htmlspecialchars($post['date']); ?>
        </p>
    </div>

    <!-- Featured Image -->
    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
         alt="<?php echo htmlspecialchars($post['title']); ?>" 
         class="featured-image"
         onerror="this.onerror=null; this.src='https://placehold.co/1200x400/e5e7eb/6b7280?text=Image+Not+Found';"
    >

    <!-- Post Content -->
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    
    <!-- New: Post Actions Bar (Like/Share) -->
    <div class="post-actions-bar">
        <!-- Like Button -->
        <button id="likePostBtn" class="like-button <?php echo $post['is_liked_by_current_user'] ? 'liked' : ''; ?>" type="button">
            <i class="fas fa-heart heart-icon"></i> 
            <span>Like</span>
            (<span class="like-count"><?php echo $post['likes']; ?></span>)
        </button>

        <!-- Share Button -->
        <button id="sharePostBtn" class="share-button" type="button">
            <i class="fas fa-share-alt"></i> 
            <span>Share Link</span>
        </button>
    </div>
</div>

<!-- New: Comments Section -->
<div class="comments-section">
    <h2>Comments (<?php echo count($comments); ?>)</h2>

    <!-- List of Comments -->
    <?php if (empty($comments)): ?>
        <p class="card-meta">Be the first to leave a comment!</p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment-item">
                <div class="comment-avatar">
                    <img src="<?php echo htmlspecialchars($comment['avatar']); ?>" 
                         alt="<?php echo htmlspecialchars($comment['author']); ?> Avatar" 
                         class="profile-pic profile-pic-small">
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

    <!-- Comment Form -->
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
        <div class="alert alert-info">
            <p>Please <a href="<?php echo SITE_URL; ?>login.php" style="font-weight: 600;">log in</a> to leave a comment.</p>
        </div>
    <?php endif; ?>
</div>

<?php
// Path adjusted to reflect that post-detail.php is now in a subdirectory (pages/)
require_once __DIR__ . '/../includes/footer.php';
?>

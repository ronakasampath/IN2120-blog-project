<?php
/**
 * Create Blog Post Page
 * Save as: pages/create-post.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Require login
requireLogin();

$pageTitle = 'Create Post';
$currentUser = getCurrentUser();
include __DIR__ . '/../includes/header.php';
?>

    <!-- Create Post Form -->
    <main class="main-content">
        <div class="container">
            <div class="card">
                <h1 class="card-title">Create New Blog Post</h1>
                <p style="color: var(--text-light);">Write your blog post using Markdown formatting</p>
            </div>

            <div id="message"></div>

            <div class="card">
                <form id="createPostForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Post Title</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-control" 
                            required
                            placeholder="Enter an engaging title"
                            maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label">Content (Markdown Supported)</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            class="form-control" 
                            required
                            placeholder="# Heading 1&#10;## Heading 2&#10;&#10;Write your content here...&#10;&#10;- Bullet point&#10;- Another point&#10;&#10;**Bold text** and *italic text*"
                            rows="15"></textarea>
                        <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                            Tip: Use # for headings, ** for bold, * for italic, - for lists
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="featured_image" class="form-label">Featured Image (Optional)</label>
                        <input 
                            type="file" 
                            id="featured_image" 
                            name="featured_image" 
                            class="form-control"
                            accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                            Add a cover image for your post (Max 5MB)
                        </small>
                    </div>

                    <!-- Image Preview -->
                    <div id="imagePreview" style="display: none; margin-top: 1rem;">
                        <img id="previewImg" style="max-width: 100%; max-height: 300px; border-radius: 6px; border: 2px solid #e5e7eb;">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Publish Post</button>
                        <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Markdown Preview -->
            <div class="card">
                <h3>Preview</h3>
                <div id="preview" class="post-content" style="min-height: 100px; padding: 1rem; background: var(--bg-gray); border-radius: 6px;">
                    <p style="color: var(--text-light);">Your preview will appear here...</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Simple markdown preview
        const contentTextarea = document.getElementById('content');
        const previewDiv = document.getElementById('preview');

        contentTextarea.addEventListener('input', function() {
            const markdown = this.value;
            previewDiv.innerHTML = renderMarkdown(markdown);
        });

        // Form submission
        document.getElementById('createPostForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const messageDiv = document.getElementById('message');
            const formData = new FormData(this);
            
            try {
                const response = await fetch('<?php echo SITE_URL; ?>/api/create-post-handler.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                    setTimeout(() => {
                        window.location.href = 'view-post.php?id=' + result.post_id;
                    }, 1000);
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="alert alert-error">An error occurred. Please try again.</div>';
            }
        });


        // Preview image
    document.getElementById('featured_image')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * Edit Post Page - WITH IMAGE PREVIEW
 * Save as: pages/edit-post.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';
require_once __DIR__ . '/../includes/image-functions.php';

requireLogin();
$currentUser = getCurrentUser();

$postId = intval($_GET['id'] ?? 0);
if (!$postId) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$post = getPost($postId);
if (!$post) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

// Check ownership
if ($post['user_id'] != $currentUser['id'] && !isAdmin()) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$images = getPostImages($postId);
$pageTitle = 'Edit Post';

include __DIR__ . '/../includes/header.php';
?>

<style>
.edit-container {
    max-width: 1600px;
    margin: 2rem auto;
    padding: 0 1rem;
    display: grid;
    grid-template-columns: 1fr 550px;
    gap: 2rem;
}

.editor-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.editor-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 2rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Rich Text Toolbar */
.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
}

.toolbar-group {
    display: flex;
    gap: 0.25rem;
    padding-right: 0.5rem;
    border-right: 1px solid #d1d5db;
}

.toolbar-group:last-child {
    border-right: none;
}

.toolbar-btn {
    padding: 0.5rem 0.75rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.toolbar-btn:hover {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

textarea.form-control {
    min-height: 400px;
    resize: vertical;
    font-family: 'Courier New', monospace;
    line-height: 1.6;
    border-radius: 0 0 8px 8px;
    border-top: none;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background: #4338ca;
    transform: translateY(-2px);
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 2px solid #e5e7eb;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Preview Section */
.preview-section {
    position: sticky;
    top: 80px;
    height: fit-content;
}

.preview-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 2rem;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
}

.preview-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
}

.preview-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.preview-meta {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.preview-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.preview-image-wrapper {
    position: relative;
    aspect-ratio: 4/3;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f4f6;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-image-order {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: rgba(79, 70, 229, 0.9);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 700;
    font-size: 0.75rem;
}

.preview-content {
    line-height: 1.8;
    color: #374151;
}

.preview-content h1 {
    font-size: 2rem;
    margin: 1.5rem 0 1rem;
    color: #1f2937;
    font-weight: 700;
}

.preview-content h2 {
    font-size: 1.5rem;
    margin: 1.25rem 0 0.75rem;
    color: #1f2937;
    font-weight: 600;
}

.preview-content h3 {
    font-size: 1.25rem;
    margin: 1rem 0 0.5rem;
    color: #1f2937;
    font-weight: 600;
}

.preview-content p {
    margin-bottom: 1rem;
}

.preview-content ul, .preview-content ol {
    margin-left: 2rem;
    margin-bottom: 1rem;
}

.preview-content code {
    background: #f3f4f6;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}

.preview-content pre {
    background: #1f2937;
    color: #f3f4f6;
    padding: 1.5rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.preview-content pre code {
    background: none;
    padding: 0;
}

.preview-content blockquote {
    border-left: 4px solid #4f46e5;
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #6b7280;
}

.preview-placeholder {
    text-align: center;
    padding: 3rem;
    color: #9ca3af;
}

@media (max-width: 1200px) {
    .edit-container {
        grid-template-columns: 1fr;
    }
    
    .preview-section {
        position: relative;
        top: 0;
    }
}
</style>

<div class="edit-container">
    <div class="editor-section">
        <div class="editor-card">
            <h1 class="section-title">
                <i class="fas fa-edit"></i>
                Edit Post
            </h1>
            
            <form id="editPostForm">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                
                <div class="form-group">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($post['title']); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">Select a category</option>
                        <option value="technology" <?php echo (isset($post['category']) && $post['category'] === 'technology') ? 'selected' : ''; ?>>Technology</option>
                        <option value="science" <?php echo (isset($post['category']) && $post['category'] === 'science') ? 'selected' : ''; ?>>Science</option>
                        <option value="art" <?php echo (isset($post['category']) && $post['category'] === 'art') ? 'selected' : ''; ?>>Art & Design</option>
                        <option value="ai" <?php echo (isset($post['category']) && $post['category'] === 'ai') ? 'selected' : ''; ?>>AI & Machine Learning</option>
                        <option value="business" <?php echo (isset($post['category']) && $post['category'] === 'business') ? 'selected' : ''; ?>>Business</option>
                        <option value="lifestyle" <?php echo (isset($post['category']) && $post['category'] === 'lifestyle') ? 'selected' : ''; ?>>Lifestyle</option>
                        <option value="education" <?php echo (isset($post['category']) && $post['category'] === 'education') ? 'selected' : ''; ?>>Education</option>
                        <option value="health" <?php echo (isset($post['category']) && $post['category'] === 'health') ? 'selected' : ''; ?>>Health & Wellness</option>
                        <option value="travel" <?php echo (isset($post['category']) && $post['category'] === 'travel') ? 'selected' : ''; ?>>Travel</option>
                        <option value="food" <?php echo (isset($post['category']) && $post['category'] === 'food') ? 'selected' : ''; ?>>Food & Cooking</option>
                        <option value="other" <?php echo (isset($post['category']) && $post['category'] === 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content" class="form-label">Content *</label>
                    
                    <!-- Toolbar -->
                    <div class="editor-toolbar">
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="insertFormat('# ', '')" title="Heading 1">
                                <strong>H1</strong>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('## ', '')" title="Heading 2">
                                <strong>H2</strong>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('### ', '')" title="Heading 3">
                                <strong>H3</strong>
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="insertFormat('**', '**')" title="Bold">
                                <strong>B</strong>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('*', '*')" title="Italic">
                                <em>I</em>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('~~', '~~')" title="Strikethrough">
                                <s>S</s>
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="insertFormat('[', '](url)')" title="Link">
                                <i class="fas fa-link"></i>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('`', '`')" title="Code">
                                <i class="fas fa-code"></i>
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="insertFormat('- ', '')" title="Bullet List">
                                <i class="fas fa-list-ul"></i>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('1. ', '')" title="Numbered List">
                                <i class="fas fa-list-ol"></i>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('> ', '')" title="Quote">
                                <i class="fas fa-quote-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <textarea id="content" 
                              name="content" 
                              class="form-control" 
                              placeholder="Write your content here..."
                              required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="<?php echo SITE_URL; ?>/pages/manage-images.php?id=<?php echo $postId; ?>" 
                       class="btn btn-success">
                        <i class="fas fa-images"></i> Manage Images
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $postId; ?>" 
                       class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="preview-section">
        <div class="preview-card">
            <div class="preview-header">
                <i class="fas fa-eye" style="font-size: 1.5rem;"></i>
                <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700;">Live Preview</h2>
            </div>
            
            <h1 class="preview-title" id="previewTitle">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>
            
            <div class="preview-meta">
                By <?php echo htmlspecialchars($post['username']); ?>
                <?php if (isset($post['category']) && $post['category']): ?>
                    <span id="previewCategory" style="margin-left: 0.5rem; background: #4f46e5; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                        <?php echo strtoupper(htmlspecialchars($post['category'])); ?>
                    </span>
                <?php else: ?>
                    <span id="previewCategory"></span>
                <?php endif; ?>
            </div>

            <!-- Display existing images -->
            <?php if (!empty($images)): ?>
                <div class="preview-images-grid">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="preview-image-wrapper">
                            <img src="<?php echo SITE_URL . htmlspecialchars($image['image_path']); ?>" 
                                 alt="Image <?php echo $index + 1; ?>" 
                                 class="preview-image">
                            <span class="preview-image-order">#<?php echo $index + 1; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="preview-content" id="previewContent">
                <div class="preview-placeholder">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                    <p style="margin-top: 1rem;">Loading preview...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
console.log('Edit post page loaded');

// Rich Text Editor Functions
function insertFormat(before, after) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    const replacement = before + (selectedText || 'text') + after;
    
    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    
    const newCursorPos = start + before.length + (selectedText ? selectedText.length : 4);
    textarea.setSelectionRange(newCursorPos, newCursorPos);
    textarea.focus();
    
    textarea.dispatchEvent(new Event('input'));
}

// Live Preview Updates
const titleInput = document.getElementById('title');
const contentInput = document.getElementById('content');
const categorySelect = document.getElementById('category');
const previewTitle = document.getElementById('previewTitle');
const previewContent = document.getElementById('previewContent');
const previewCategory = document.getElementById('previewCategory');

titleInput.addEventListener('input', function() {
    previewTitle.textContent = this.value || 'Untitled Post';
});

categorySelect.addEventListener('change', function() {
    if (this.value) {
        previewCategory.innerHTML = '<span style="margin-left: 0.5rem; background: #4f46e5; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">' + 
            this.value.toUpperCase() + '</span>';
    } else {
        previewCategory.innerHTML = '';
    }
});

contentInput.addEventListener('input', function() {
    updatePreview();
});

function updatePreview() {
    const content = contentInput.value.trim();
    
    if (!content) {
        previewContent.innerHTML = '<div class="preview-placeholder"><i class="fas fa-pencil-alt" style="font-size: 2rem;"></i><p style="margin-top: 1rem;">Start typing to see preview...</p></div>';
        return;
    }
    
    if (typeof renderMarkdown === 'function') {
        try {
            previewContent.innerHTML = renderMarkdown(content);
        } catch (error) {
            console.error('Markdown render error:', error);
            previewContent.innerHTML = '<p style="color: red;">Error rendering markdown</p>';
        }
    } else {
        console.error('renderMarkdown function not found!');
        previewContent.innerHTML = '<p>' + content.replace(/\n/g, '<br>') + '</p>';
    }
}

// Initialize preview on page load
window.addEventListener('load', function() {
    console.log('Page loaded, initializing preview');
    updatePreview();
});

// Also try to update immediately
if (document.readyState === 'complete') {
    updatePreview();
}

// Form Submit
document.getElementById('editPostForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/update-post-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Post updated successfully!');
            setTimeout(() => {
                window.location.href = '<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $postId; ?>';
            }, 1000);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

function showToast(message) {
    const toast = document.createElement('div');
    toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; font-weight: 600;';
    toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
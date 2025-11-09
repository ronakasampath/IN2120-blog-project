<?php
/**
 * Edit Post Page - Professional Publication Design
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

// Get categories
$db = getDB();
$categoriesStmt = $db->query("SELECT DISTINCT category FROM blogpost WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>

<style>
/* Professional Edit Layout */
.edit-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 3rem 2rem;
    display: grid;
    grid-template-columns: 1fr 500px;
    gap: 3rem;
}

/* Editor Section */
.editor-section {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.editor-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 2.5rem;
}

.section-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2rem;
    letter-spacing: -0.02em;
}

.form-group {
    margin-bottom: 2rem;
}

.form-label {
    display: block;
    margin-bottom: 0.625rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9375rem;
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9375rem;
    transition: all 0.2s;
    font-family: inherit;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #111827;
    box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.08);
}

select.form-control {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23374151' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.875rem center;
    background-size: 12px;
    padding-right: 2.5rem;
    appearance: none;
}

/* Markdown Toolbar */
.markdown-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 1rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
}

.toolbar-group {
    display: flex;
    gap: 0.375rem;
    padding-right: 0.75rem;
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
    min-width: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toolbar-btn:hover {
    background: #111827;
    color: white;
    border-color: #111827;
}

textarea.form-control {
    min-height: 500px;
    resize: vertical;
    font-family: 'Courier New', monospace;
    line-height: 1.7;
    border-radius: 0 0 8px 8px;
    border-top: none;
    font-size: 0.9375rem;
}

/* Action Buttons - UPDATED WITH THEME COLORS */
.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    flex: 1;
    min-width: 180px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
    border: 2px solid transparent;
    cursor: pointer;
    font-size: 0.9375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.625rem;
}

/* Primary Button - Save Changes (Dark) */
.btn-primary {
    background: #111827;
    color: white;
    border-color: #111827;
}

.btn-primary:hover {
    background: #1f2937;
    border-color: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.25);
}

.btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(17, 24, 39, 0.2);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Success Button - Manage Images (Green) */
.btn-success {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.btn-success:hover {
    background: #059669;
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-success:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

/* Secondary Button - Cancel (Gray) */
.btn-secondary {
    background: white;
    color: #6b7280;
    border-color: #d1d5db;
}

.btn-secondary:hover {
    background: #f9fafb;
    color: #111827;
    border-color: #9ca3af;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.btn-secondary:active {
    transform: translateY(0);
    box-shadow: none;
}

/* Danger Button - For delete actions if needed */
.btn-danger {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.btn-danger:hover {
    background: #dc2626;
    border-color: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-danger:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
}

/* Preview Section */
.preview-section {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.preview-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 2.5rem;
    max-height: calc(100vh - 140px);
    overflow-y: auto;
}

.preview-header {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.preview-label {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

.preview-category {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 1.25rem;
    opacity: 0.7;
}

.preview-title {
    font-size: 2.25rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 1rem;
    line-height: 1.25;
    letter-spacing: -0.025em;
}

.preview-meta {
    color: #9ca3af;
    font-size: 0.9375rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

/* Preview Images Grid */
.preview-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
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
    background: rgba(17, 24, 39, 0.9);
    color: white;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.75rem;
}

/* Preview Content - Article Styles */
.preview-content {
    font-size: 1.1875rem;
    line-height: 1.75;
    color: #374151;
}

.preview-content h1 {
    font-size: 2.25rem;
    font-weight: 700;
    color: #111827;
    margin: 3rem 0 1.5rem;
    line-height: 1.25;
    letter-spacing: -0.025em;
}

.preview-content h2 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin: 2.5rem 0 1.25rem;
    line-height: 1.3;
    letter-spacing: -0.02em;
}

.preview-content h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin: 2rem 0 1rem;
    line-height: 1.35;
}

.preview-content p {
    margin-bottom: 1.75rem;
}

.preview-content ul, .preview-content ol {
    margin: 1.75rem 0;
    padding-left: 2rem;
}

.preview-content li {
    margin-bottom: 0.875rem;
    line-height: 1.7;
}

.preview-content code {
    background: #f3f4f6;
    color: #dc2626;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.preview-content pre {
    background: #1f2937;
    color: #f3f4f6;
    padding: 1.75rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 2.5rem 0;
    line-height: 1.6;
}

.preview-content pre code {
    background: none;
    padding: 0;
    color: inherit;
}

.preview-content blockquote {
    border-left: 3px solid #d1d5db;
    padding-left: 1.75rem;
    margin: 2.5rem 0;
    font-style: italic;
    color: #6b7280;
}

.preview-content strong {
    font-weight: 700;
    color: #111827;
}

.preview-content a {
    color: #3b82f6;
    text-decoration: underline;
    text-decoration-color: #bfdbfe;
    text-underline-offset: 2px;
    transition: text-decoration-color 0.2s;
}

.preview-content a:hover {
    text-decoration-color: #3b82f6;
}

.preview-placeholder {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
    background: #f9fafb;
    border-radius: 8px;
}

.preview-empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

/* Responsive */
@media (max-width: 1200px) {
    .edit-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .preview-section {
        position: relative;
        top: 0;
    }
}

@media (max-width: 768px) {
    .edit-container {
        padding: 2rem 1.25rem;
    }
    
    .editor-card {
        padding: 2rem 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        min-width: auto;
        width: 100%;
    }
    
    .toolbar-btn {
        padding: 0.5rem;
        min-width: 32px;
    }
}



</style>

<div class="edit-container">
    <!-- Editor Section -->
    <div class="editor-section">
        <div class="editor-card">
            <h1 class="section-title">Edit Post</h1>
            
            <form id="editPostForm">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                
                <div class="form-group">
                    <label for="title" class="form-label">Post Title</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($post['title']); ?>" 
                           placeholder="Enter your post title..."
                           required>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">Select a category...</option>
                        <?php 
                        $defaultCategories = ['technology', 'science', 'art', 'ai', 'business', 'lifestyle', 'education', 'health', 'travel', 'food', 'other'];
                        $allCategories = array_unique(array_merge($defaultCategories, $categories));
                        sort($allCategories);
                        
                        foreach ($allCategories as $cat): 
                            $selected = (isset($post['category']) && $post['category'] === $cat) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $selected; ?>>
                                <?php echo ucfirst(htmlspecialchars($cat)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content" class="form-label">Post Content</label>
                    
                    <!-- Markdown Toolbar -->
                    <div class="markdown-toolbar">
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
                                🔗
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('`', '`')" title="Code">
                                &lt;/&gt;
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="insertFormat('- ', '')" title="Bullet List">
                                • List
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('1. ', '')" title="Numbered List">
                                1. List
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertFormat('> ', '')" title="Quote">
                                "
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
                        💾 Save Changes
                    </button>
                    <a href="<?php echo SITE_URL; ?>/pages/manage-images.php?id=<?php echo $postId; ?>" 
                       class="btn btn-success">
                        🖼️ Manage Images
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $postId; ?>" 
                       class="btn btn-secondary">
                        ✕ Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="preview-section">
        <div class="preview-card">
            <div class="preview-header">
                <span style="font-size: 1.5rem;">👁️</span>
                <h2 class="preview-label">Live Preview</h2>
            </div>
            
            <div id="previewCategoryContainer" style="display: none;">
                <span class="preview-category" id="previewCategory"></span>
            </div>
            
            <h1 class="preview-title" id="previewTitle">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>
            
            <div class="preview-meta">
                By <?php echo htmlspecialchars($post['username']); ?> • 
                <?php echo formatDate($post['updated_at'] ?? $post['created_at']); ?>
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
                    <div class="preview-empty-icon">⏳</div>
                    <p>Loading preview...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Markdown Toolbar Functions
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
const previewCategoryContainer = document.getElementById('previewCategoryContainer');

titleInput.addEventListener('input', function() {
    previewTitle.textContent = this.value || 'Untitled Post';
});

categorySelect.addEventListener('change', function() {
    if (this.value) {
        previewCategory.textContent = this.value.toUpperCase();
        previewCategoryContainer.style.display = 'block';
    } else {
        previewCategoryContainer.style.display = 'none';
    }
});

contentInput.addEventListener('input', function() {
    updatePreview();
});

function updatePreview() {
    const content = contentInput.value.trim();
    
    if (!content) {
        previewContent.innerHTML = `
            <div class="preview-placeholder">
                <div class="preview-empty-icon">✏️</div>
                <p>Start typing to see preview...</p>
            </div>
        `;
        return;
    }
    
    if (typeof renderMarkdown === 'function') {
        try {
            previewContent.innerHTML = renderMarkdown(content);
        } catch (error) {
            console.error('Markdown render error:', error);
            previewContent.innerHTML = '<p style="color: #dc2626;">Error rendering markdown</p>';
        }
    } else {
        console.error('renderMarkdown function not found!');
        previewContent.innerHTML = '<p>' + content.replace(/\n/g, '<br>') + '</p>';
    }
}

// Initialize preview on page load
window.addEventListener('load', function() {
    updatePreview();
    // Initialize category preview
    if (categorySelect.value) {
        previewCategory.textContent = categorySelect.value.toUpperCase();
        previewCategoryContainer.style.display = 'block';
    }
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
    submitBtn.innerHTML = '⏳ Saving...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/update-post-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('✓ Post updated successfully!');
            setTimeout(() => {
                window.location.href = '<?php echo SITE_URL; ?>/pages/view-post.php?id=<?php echo $postId; ?>';
            }, 1000);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast('✕ Error: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'error' ? '#ef4444' : '#10b981';
    toast.style.cssText = `position: fixed; bottom: 20px; right: 20px; background: ${bgColor}; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 10000; font-weight: 600; font-size: 0.9375rem;`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
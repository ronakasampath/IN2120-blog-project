<?php
/**
 * Create Post Page - THEMED TO MATCH HOMEPAGE
 * Save as: pages/create-post.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$pageTitle = 'Create New Post';
$currentUser = getCurrentUser();

// Get available categories
$db = getDB();
$categories = [];
try {
    $stmt = $db->query("SELECT DISTINCT category FROM blogpost WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $existingCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $existingCategories = [];
}

// Default categories with expanded options
$defaultCategories = [
    'general',
    'technology',
    'programming',
    'web-development',
    'artificial-intelligence',
    'lifestyle',
    'health',
    'fitness',
    'travel',
    'food',
    'photography',
    'business',
    'finance',
    'education',
    'science',
    'entertainment',
    'gaming',
    'sports',
    'art',
    'music',
    'books',
    'fashion',
    'personal',
    'tutorials',
    'reviews',
    'news'
];

// Merge and sort
$categories = array_unique(array_merge($defaultCategories, $existingCategories));
sort($categories);

include __DIR__ . '/../includes/header.php';
?>

<style>
/* Create Post Container - Matching Homepage Style */
.create-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 3rem 2rem;
    display: grid;
    grid-template-columns: 1fr 500px;
    gap: 3rem;
}

.editor-section {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Card Styling - Matching Homepage */
.editor-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 2.5rem;
    transition: box-shadow 0.2s;
}

.section-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2rem;
    letter-spacing: -0.02em;
}

/* Form Controls - Matching Homepage Style */
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

textarea.form-control {
    min-height: 500px;
    resize: vertical;
    font-family: 'Courier New', monospace;
    line-height: 1.7;
    font-size: 0.9375rem;
}

/* Category Management */
.category-input-wrapper {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.75rem;
}

.category-input-wrapper input {
    flex: 1;
}

.category-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    max-height: 180px;
    overflow-y: auto;
}

.category-badge {
    padding: 0.375rem 0.875rem;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.category-badge:hover {
    background: #e5e7eb;
    color: #111827;
    border-color: #d1d5db;
}

.markdown-hint {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.5rem;
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

/* Image Upload Section - Matching Homepage */
.upload-section {
    background: #f9fafb;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
}

.upload-section:hover {
    border-color: #111827;
    background: #f3f4f6;
}

.upload-section.dragover {
    border-color: #10b981;
    background: #d1fae5;
}

.upload-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.upload-btn {
    display: inline-block;
    padding: 0.875rem 1.75rem;
    background: #10b981;
    color: white;
    border: 2px solid #10b981;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.upload-btn:hover {
    background: #059669;
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

#imageInput {
    display: none;
}

/* Image Preview Grid */
.image-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
}

.image-preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e5e7eb;
    background: #f3f4f6;
}

.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-image-btn {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    cursor: pointer;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-weight: 700;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.remove-image-btn:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.image-count {
    display: inline-block;
    background: #10b981;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

/* Action Buttons - THEMED */
.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn {
    flex: 1;
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

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

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

/* Preview Section - Matching Homepage */
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

.preview-images {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
}

.preview-content h2 {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin: 2.5rem 0 1.25rem;
    line-height: 1.3;
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
    .create-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .preview-section {
        position: relative;
        top: 0;
    }
}

@media (max-width: 768px) {
    .create-container {
        padding: 2rem 1.25rem;
    }
    
    .editor-card {
        padding: 2rem 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .toolbar-btn {
        padding: 0.5rem;
        min-width: 32px;
    }
}
</style>

<div class="create-container">
    <!-- LEFT SECTION - EDITOR -->
    <div class="editor-section">
        <!-- Post Editor -->
        <div class="editor-card">
            <h1 class="section-title">Create New Post</h1>
            
            <form id="createPostForm">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="title" class="form-label">Post Title</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           class="form-control" 
                           placeholder="Enter an engaging title..." 
                           required>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Select a category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>">
                                <?php echo ucfirst(str_replace('-', ' ', htmlspecialchars($cat))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="category-input-wrapper">
                        <input type="text" 
                               id="customCategory" 
                               class="form-control" 
                               placeholder="Or create custom category (e.g., machine-learning)">
                        <button type="button" onclick="addCustomCategory()" class="btn-success">
                            + Add Custom
                        </button>
                    </div>
                    
                    <div class="category-badges">
                        <small style="color: #6b7280; width: 100%; display: block; margin-bottom: 0.5rem;">Quick select:</small>
                        <?php foreach ($categories as $cat): ?>
                            <span class="category-badge" onclick="selectCategory('<?php echo htmlspecialchars($cat); ?>')">
                                <?php echo strtoupper(str_replace('-', ' ', htmlspecialchars($cat))); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
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
                              placeholder="Write your content here... Use markdown for formatting!"
                              required></textarea>
                    <div class="markdown-hint">
                        💡 Use the toolbar above for quick markdown formatting
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        💾 Create Post
                    </button>
                    <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">
                        ✕ Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Image Upload Section -->
        <div class="editor-card">
            <h2 class="section-title">Post Images (Optional)</h2>
            
            <div class="upload-section" id="dropZone">
                <div class="upload-icon">🖼️</div>
                <h3 style="margin-bottom: 0.5rem; color: #111827;">Upload Images</h3>
                <p style="color: #6b7280; margin-bottom: 1.5rem;">
                    Drag & drop images here or click to browse
                </p>
                <label for="imageInput" class="upload-btn">Choose Images</label>
                <input type="file" id="imageInput" accept="image/*" multiple>
                <p style="color: #9ca3af; font-size: 0.875rem; margin-top: 1rem;">
                    Maximum 5MB per image • JPG, PNG, GIF, WebP
                </p>
            </div>

            <div id="imagePreviewContainer" style="display: none;">
                <span class="image-count" id="imageCount">0</span> image(s) selected
                <div id="imagePreviewGrid" class="image-preview-grid"></div>
            </div>
        </div>
    </div>

    <!-- RIGHT SECTION - LIVE PREVIEW -->
    <div class="preview-section">
        <div class="preview-card">
            <div class="preview-header">
                <span style="font-size: 1.5rem;">👁️</span>
                <span class="preview-label">Live Preview</span>
            </div>
            
            <div id="previewCategoryBadge" style="display: none;">
                <span class="preview-category" id="previewCategory">general</span>
            </div>
            
            <h1 class="preview-title" id="previewTitle">Untitled Post</h1>
            
            <div class="preview-meta">
                By <?php echo htmlspecialchars($currentUser['username']); ?> • Just now
            </div>
            
            <div id="previewImagesContainer"></div>
            
            <div class="preview-content" id="previewContent">
                <div class="preview-placeholder">
                    <div class="preview-empty-icon">✏️</div>
                    <p><strong>Start typing to see your post preview...</strong></p>
                    <p style="margin-top: 0.5rem; font-size: 0.875rem;">Your markdown will be rendered here in real-time</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedImages = [];

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

// Category selection helper
function selectCategory(category) {
    document.getElementById('category').value = category;
    document.getElementById('customCategory').value = '';
    updateCategoryPreview(category);
}

// Add custom category
function addCustomCategory() {
    const input = document.getElementById('customCategory');
    const customCat = input.value.trim().toLowerCase().replace(/\s+/g, '-');
    
    if (!customCat) {
        alert('Please enter a category name');
        return;
    }
    
    if (!/^[a-z0-9-]+$/.test(customCat)) {
        alert('Category can only contain lowercase letters, numbers, and hyphens');
        return;
    }
    
    const select = document.getElementById('category');
    const exists = Array.from(select.options).some(opt => opt.value === customCat);
    
    if (exists) {
        select.value = customCat;
        input.value = '';
        updateCategoryPreview(customCat);
        showToast('Category selected: ' + customCat);
        return;
    }
    
    const option = document.createElement('option');
    option.value = customCat;
    option.textContent = customCat.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    select.appendChild(option);
    
    select.value = customCat;
    input.value = '';
    updateCategoryPreview(customCat);
    
    showToast('Custom category added: ' + customCat);
}

// Live Category Preview
document.getElementById('category').addEventListener('change', function() {
    updateCategoryPreview(this.value);
});

function updateCategoryPreview(category) {
    const badge = document.getElementById('previewCategoryBadge');
    const categoryText = document.getElementById('previewCategory');
    
    if (category) {
        categoryText.textContent = category;
        badge.style.display = 'block';
    } else {
        badge.style.display = 'none';
    }
}

// Live Title Preview
document.getElementById('title').addEventListener('input', function() {
    document.getElementById('previewTitle').textContent = this.value || 'Untitled Post';
});

// Live Content Preview
document.getElementById('content').addEventListener('input', function() {
    const preview = document.getElementById('previewContent');
    if (this.value.trim()) {
        preview.innerHTML = renderMarkdown(this.value);
    } else {
        preview.innerHTML = `
            <div class="preview-placeholder">
                <div class="preview-empty-icon">✏️</div>
                <p><strong>Start typing to see your post preview...</strong></p>
                <p style="margin-top: 0.5rem; font-size: 0.875rem;">Your markdown will be rendered here in real-time</p>
            </div>
        `;
    }
});

// Drag & Drop Setup
const dropZone = document.getElementById('dropZone');
const imageInput = document.getElementById('imageInput');

dropZone.addEventListener('click', () => imageInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

imageInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

// Handle File Upload
function handleFiles(files) {
    for (let file of files) {
        if (!file.type.startsWith('image/')) {
            alert(`${file.name} is not an image file`);
            continue;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert(`${file.name} is too large (max 5MB)`);
            continue;
        }
        selectedImages.push(file);
    }
    updateImagePreviews();
}

// Update Image Previews
function updateImagePreviews() {
    const container = document.getElementById('imagePreviewContainer');
    const grid = document.getElementById('imagePreviewGrid');
    const count = document.getElementById('imageCount');
    const previewContainer = document.getElementById('previewImagesContainer');
    
    if (selectedImages.length === 0) {
        container.style.display = 'none';
        previewContainer.innerHTML = '';
        return;
    }
    
    container.style.display = 'block';
    count.textContent = selectedImages.length;
    grid.innerHTML = '';
    
    previewContainer.innerHTML = '<div class="preview-images" id="previewImages"></div>';
    const previewImages = document.getElementById('previewImages');
    
    selectedImages.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            // Left panel thumbnail
            const leftDiv = document.createElement('div');
            leftDiv.className = 'image-preview-item';
            leftDiv.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index + 1}">
                <button type="button" class="remove-image-btn" onclick="removeImage(${index})">×</button>
            `;
            grid.appendChild(leftDiv);
            
            // Right panel preview
            const rightDiv = document.createElement('div');
            rightDiv.className = 'preview-image-wrapper';
            rightDiv.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index + 1}" class="preview-image">
                <span class="preview-image-order">#${index + 1}</span>
            `;
            previewImages.appendChild(rightDiv);
        };
        reader.readAsDataURL(file);
    });
}

// Remove Image
function removeImage(index) {
    selectedImages.splice(index, 1);
    updateImagePreviews();
}

// Form Submit Handler
document.getElementById('createPostForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Creating post...';
    
    const formData = new FormData(this);
    
    try {
        // Step 1: Create the post
        const response = await fetch('<?php echo SITE_URL; ?>/api/create-post-handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }
        
        const postId = result.post_id;
        
        // Step 2: Upload images if any
        if (selectedImages.length > 0) {
            submitBtn.innerHTML = `⏳ Uploading ${selectedImages.length} image(s)...`;
            
            const imageFormData = new FormData();
            imageFormData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
            imageFormData.append('post_id', postId);
            
            selectedImages.forEach(file => {
                imageFormData.append('images[]', file);
            });
            
            const imageResponse = await fetch('<?php echo SITE_URL; ?>/api/upload-post-images-handler.php', {
                method: 'POST',
                body: imageFormData
            });
            
            const imageResult = await imageResponse.json();
            
            if (!imageResult.success) {
                console.warn('Image upload warning:', imageResult.message);
            }
        }
        
        showToast('✓ Post created successfully!');
        setTimeout(() => {
            window.location.href = '<?php echo SITE_URL; ?>/pages/view-post.php?id=' + postId;
        }, 1200);
        
    } catch (error) {
        showToast('✕ Error: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Toast Notification
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
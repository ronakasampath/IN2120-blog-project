<?php
/**
 * Create Post Page - WITH CATEGORY SELECTION
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
    $stmt = $db->query("SELECT DISTINCT category FROM blogPost WHERE category IS NOT NULL AND category != '' ORDER BY category");
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
.create-container {
    display: grid;
    grid-template-columns: 1fr 550px;
    gap: 2rem;
    max-width: 1600px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.left-section {
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

select.form-control {
    cursor: pointer;
    background-color: white;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23374151' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 12px;
    padding-right: 2.5rem;
    appearance: none;
}

textarea.form-control {
    min-height: 400px;
    resize: vertical;
    font-family: 'Courier New', monospace;
    line-height: 1.6;
}

.markdown-hint {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.5rem;
}

.category-input-wrapper {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.category-input-wrapper input {
    flex: 1;
}

.category-input-wrapper button {
    padding: 0.875rem 1.5rem;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.category-input-wrapper button:hover {
    background: #059669;
}

.category-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
    max-height: 200px;
    overflow-y: auto;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 6px;
}

.category-badge {
    padding: 0.375rem 0.75rem;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}

.category-badge:hover {
    background: #e5e7eb;
    border-color: #9ca3af;
}

.image-upload-section {
    background: #f9fafb;
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
}

.image-upload-section:hover {
    border-color: #4f46e5;
    background: #f0f4ff;
}

.image-upload-section.dragover {
    border-color: #10b981;
    background: #d1fae5;
}

.upload-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.upload-btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #4f46e5;
    color: white;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.upload-btn:hover {
    background: #4338ca;
}

#imageInput {
    display: none;
}

.image-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.image-preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e5e7eb;
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
    width: 28px;
    height: 28px;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
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
    border-radius: 6px;
    font-weight: 600;
    margin-bottom: 1rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
}

.btn {
    flex: 1;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-primary {
    background: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background: #4338ca;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 2px solid #e5e7eb;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* RIGHT SECTION - LIVE PREVIEW */
.right-section {
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

.preview-icon {
    font-size: 1.5rem;
}

.preview-label {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.preview-category {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1rem;
    text-transform: capitalize;
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
}

.preview-images {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.875rem;
}

.preview-content {
    line-height: 1.8;
    color: #374151;
}

.preview-placeholder {
    text-align: center;
    padding: 3rem;
    color: #9ca3af;
    background: #f9fafb;
    border-radius: 8px;
    border: 2px dashed #e5e7eb;
}

.preview-empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

@media (max-width: 1200px) {
    .create-container {
        grid-template-columns: 1fr;
    }
    
    .right-section {
        position: relative;
        top: 0;
    }
}
</style>

<div class="create-container">
    <!-- LEFT SECTION - EDITOR -->
    <div class="left-section">
        <!-- Post Editor -->
        <div class="editor-card">
            <h1 class="section-title">
                <span></span>
                Create New Post
            </h1>
            
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
                        <button type="button" onclick="addCustomCategory()">+ Add Custom</button>
                    </div>
                    
                    <div class="category-badges">
                        <small style="color: #6b7280; width: 100%; display: block; margin-bottom: 0.5rem;">Quick select:</small>
                        <?php 
                        // Show all categories as badges
                        foreach ($categories as $cat): 
                        ?>
                            <span class="category-badge" onclick="selectCategory('<?php echo htmlspecialchars($cat); ?>')">
                                <?php echo strtoupper(str_replace('-', ' ', htmlspecialchars($cat))); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="content" class="form-label">Post Content</label>
                    
                    <?php include __DIR__ . '/../includes/markdown-toolbox.php'; ?>
                    
                    <textarea id="content" 
                              name="content" 
                              class="form-control" 
                              placeholder="Write your post content here... Use the toolbox above for formatting help!" 
                              required></textarea>
                    <div class="markdown-hint">
                        Use the formatting toolbox above or type markdown directly
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Create Post
                    </button>
                    <a href="<?php echo SITE_URL; ?>/index.php" class="btn btn-secondary">
                        ← Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Image Upload Section -->
        <div class="editor-card">
            <h2 class="section-title">
                <span></span>
                Post Images (Optional)
            </h2>
            
            <div class="image-upload-section" id="dropZone">
                <div class="upload-icon"></div>
                <h3 style="margin-bottom: 0.5rem; color: #1f2937;">Upload Images</h3>
                <p style="color: #6b7280; margin-bottom: 1rem;">
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
    <div class="right-section">
        <div class="preview-card">
            <div class="preview-header">
                <span class="preview-icon"></span>
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
                    <div class="preview-empty-icon"></div>
                    <p><strong>Start typing to see your post preview...</strong></p>
                    <p style="margin-top: 0.5rem; font-size: 0.875rem;">Your markdown will be rendered here in real-time</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedImages = [];

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
    
    // Validate format (alphanumeric and hyphens only)
    if (!/^[a-z0-9-]+$/.test(customCat)) {
        alert('Category can only contain lowercase letters, numbers, and hyphens');
        return;
    }
    
    // Check if already exists
    const select = document.getElementById('category');
    const exists = Array.from(select.options).some(opt => opt.value === customCat);
    
        if (exists) {
        // Just select it
        select.value = customCat;
        input.value = '';
        updateCategoryPreview(customCat);
        showToast('Category selected: ' + customCat);
        return;
    }
    
    // Add new option
    const option = document.createElement('option');
    option.value = customCat;
    option.textContent = customCat.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    select.appendChild(option);
    
    // Select it
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
                <div class="preview-empty-icon"></div>
                <p><strong>Start typing to see your post preview...</strong></p>
                <p style="margin-top: 0.5rem; font-size: 0.875rem;">Your markdown will be rendered here in real-time</p>
            </div>
        `;
    }
});

// Drag & Drop Setup
const dropZone = document.getElementById('dropZone');
const imageInput = document.getElementById('imageInput');

dropZone.addEventListener('click', () => {
    imageInput.click();
});

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

// Update Image Previews (Both Panels)
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
    
    // Show left panel preview
    container.style.display = 'block';
    count.textContent = selectedImages.length;
    grid.innerHTML = '';
    
    // Setup right panel preview container
    previewContainer.innerHTML = '<div class="preview-images" id="previewImages"></div>';
    const previewImages = document.getElementById('previewImages');
    
    // Generate previews for each image
    selectedImages.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            // Left panel thumbnail with remove button
            const leftDiv = document.createElement('div');
            leftDiv.className = 'image-preview-item';
            leftDiv.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index + 1}">
                <button type="button" class="remove-image-btn" onclick="removeImage(${index})">×</button>
            `;
            grid.appendChild(leftDiv);
            
            // Right panel preview image
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
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating post...';
    
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
        
        // Step 2: Upload images if any were selected
        if (selectedImages.length > 0) {
            submitBtn.textContent = 'Uploading ' + selectedImages.length + ' image(s)...';
            
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
                // Continue anyway - post was created successfully
            }
        }
        
    // Success!
    showToast('Post created successfully!');
        setTimeout(() => {
            window.location.href = '<?php echo SITE_URL; ?>/pages/view-post.php?id=' + postId;
        }, 1200);
        
    } catch (error) {
        alert('Error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});

// Toast Notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-weight: 600;
        animation: slideIn 0.3s ease;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
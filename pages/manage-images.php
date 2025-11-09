<?php
/**
 * Manage Post Images Page
 * Save as: pages/manage-images.php
 * Features: Upload, Reorder (Drag & Drop), Delete images
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
$pageTitle = 'Manage Images - ' . htmlspecialchars($post['title']);

include __DIR__ . '/../includes/header.php';
?>

<style>
.manage-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.page-header {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.post-title {
    color: #6b7280;
    font-size: 1.1rem;
}

.back-link {
    display: inline-block;
    margin-top: 1rem;
    color: #4f46e5;
    text-decoration: none;
    font-weight: 600;
}

.back-link:hover {
    text-decoration: underline;
}

.upload-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

.upload-area {
    background: #f9fafb;
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.upload-area:hover {
    border-color: #4f46e5;
    background: #f0f4ff;
}

.upload-area.dragover {
    border-color: #10b981;
    background: #d1fae5;
}

.upload-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.upload-btn {
    display: inline-block;
    padding: 0.875rem 1.75rem;
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

.images-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.image-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s;
    cursor: move;
    position: relative;
}

.image-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-4px);
}

.image-card.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.image-card.drag-over {
    border-color: #4f46e5;
    border-style: dashed;
    background: #f0f4ff;
}

.image-wrapper {
    position: relative;
    aspect-ratio: 4/3;
    background: #f3f4f6;
}

.image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-order {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    background: rgba(79, 70, 229, 0.95);
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.drag-handle {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: rgba(255, 255, 255, 0.95);
    color: #6b7280;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    cursor: move;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.image-info {
    padding: 1rem;
    background: #f9fafb;
}

.image-filename {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.75rem;
    word-break: break-word;
}

.image-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-delete {
    flex: 1;
    padding: 0.625rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.btn-delete:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

.btn-primary {
    padding: 0.625rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.btn-primary:hover {
    background: #4338ca;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.instruction-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.instruction-title {
    font-weight: 700;
    margin-bottom: 0.75rem;
    font-size: 1.1rem;
}

.instruction-list {
    list-style: none;
    padding: 0;
}

.instruction-list li {
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
}

.instruction-list li::before {
    content: '→';
    position: absolute;
    left: 0;
    font-weight: 700;
}

@media (max-width: 768px) {
    .images-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="manage-container">
    <div class="page-header">
        <h1 class="page-title">Manage Post Images</h1>
        <div class="post-title">
            Post: <strong><?php echo htmlspecialchars($post['title']); ?></strong>
        </div>
        <a href="<?php echo SITE_URL; ?>/pages/edit-post.php?id=<?php echo $postId; ?>" class="back-link">
            ← Back to Edit Post
        </a>
    </div>

    <!-- Upload Section -->
    <div class="upload-section">
        <h2 class="section-title">
            <span>📤</span>
            Upload New Images
        </h2>
        
        <div class="upload-area" id="uploadDropZone">
            <div class="upload-icon"></div>
            <h3 style="margin-bottom: 0.5rem; color: #1f2937;">Drop images here or click to browse</h3>
            <p style="color: #6b7280; margin-bottom: 1.5rem;">
                Select multiple images to upload at once
            </p>
            <label for="imageInput" class="upload-btn">Choose Images</label>
            <input type="file" id="imageInput" accept="image/*" multiple>
            <p style="color: #9ca3af; font-size: 0.875rem; margin-top: 1rem;">
                Maximum 5MB per image • JPG, PNG, GIF, WebP
            </p>
        </div>
    </div>

    <!-- Images Grid -->
    <div class="images-section">
        <h2 class="section-title">
            <span></span>
            Current Images (<?php echo count($images); ?>)
        </h2>

        <div class="instruction-box">
            <div class="instruction-title">How to manage your images:</div>
            <ul class="instruction-list">
                <li>Drag & drop images to reorder them</li>
                <li>The number badge shows the display order</li>
                <li>Click delete to remove an image permanently</li>
                <li>Changes are saved automatically when you reorder</li>
            </ul>
        </div>

        <?php if (empty($images)): ?>
            <div class="empty-state">
                 <div class="empty-icon"></div>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No images yet</h3>
                <p style="color: #9ca3af;">Upload some images using the section above</p>
            </div>
        <?php else: ?>
            <div class="images-grid" id="imagesGrid">
                <?php foreach ($images as $index => $image): ?>
                    <div class="image-card" 
                         draggable="true" 
                         data-image-id="<?php echo $image['id']; ?>"
                         data-order=" $image['display_order'];">
                        <div class="image-wrapper">
                            <img src="<?php echo SITE_URL . htmlspecialchars($image['image_path']); ?>" 
                                 alt="Image <?php echo $index + 1; ?>">
                            <div class="image-order"><?php echo $index + 1; ?></div>
                            <div class="drag-handle" title="Drag to reorder">⋮⋮</div>
                        </div>
                        <div class="image-info">
                            <div class="image-filename">
                                <?php echo basename($image['image_path']); ?>
                            </div>
                            <div class="image-actions">
                                        <button class="btn-delete" 
                                                onclick="deleteImage(<?php echo $image['id']; ?>, this)">
                                            Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const postId = <?php echo $postId; ?>;
let draggedElement = null;

// Image Upload
const uploadDropZone = document.getElementById('uploadDropZone');
const imageInput = document.getElementById('imageInput');

uploadDropZone.addEventListener('click', () => imageInput.click());

uploadDropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadDropZone.classList.add('dragover');
});

uploadDropZone.addEventListener('dragleave', () => {
    uploadDropZone.classList.remove('dragover');
});

uploadDropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadDropZone.classList.remove('dragover');
    handleFileUpload(e.dataTransfer.files);
});

imageInput.addEventListener('change', (e) => {
    handleFileUpload(e.target.files);
});

async function handleFileUpload(files) {
    if (files.length === 0) return;

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
    formData.append('post_id', postId);

    for (let file of files) {
        if (!file.type.startsWith('image/')) {
            alert(`${file.name} is not an image`);
            continue;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert(`${file.name} is too large (max 5MB)`);
            continue;
        }
        formData.append('images[]', file);
    }

    try {
            showToast('Uploading images...', 'info');
        
        const response = await fetch('<?php echo SITE_URL; ?>/api/upload-post-images-handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
                showToast('Images uploaded successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
            showToast('Error: ' + error.message, 'error');
    }
}

// Drag and Drop Reordering
const imagesGrid = document.getElementById('imagesGrid');
if (imagesGrid) {
    const imageCards = imagesGrid.querySelectorAll('.image-card');

    imageCards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
        card.addEventListener('dragover', handleDragOver);
        card.addEventListener('drop', handleDrop);
        card.addEventListener('dragenter', handleDragEnter);
        card.addEventListener('dragleave', handleDragLeave);
    });
}

function handleDragStart(e) {
    draggedElement = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.image-card').forEach(card => {
        card.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    if (this !== draggedElement) {
        this.classList.add('drag-over');
    }
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    e.stopPropagation();
    e.preventDefault();

    if (draggedElement !== this) {
        // Reorder in DOM
        const allCards = [...imagesGrid.querySelectorAll('.image-card')];
        const draggedIndex = allCards.indexOf(draggedElement);
        const targetIndex = allCards.indexOf(this);

        if (draggedIndex < targetIndex) {
            this.parentNode.insertBefore(draggedElement, this.nextSibling);
        } else {
            this.parentNode.insertBefore(draggedElement, this);
        }

        // Update order numbers
        updateOrderNumbers();
        
        // Save new order
        saveNewOrder();
    }

    return false;
}

function updateOrderNumbers() {
    const cards = imagesGrid.querySelectorAll('.image-card');
    cards.forEach((card, index) => {
        const orderBadge = card.querySelector('.image-order');
        orderBadge.textContent = index + 1;
    });
}

async function saveNewOrder() {
    const cards = imagesGrid.querySelectorAll('.image-card');
    const newOrder = [];

    cards.forEach((card, index) => {
        newOrder.push({
            id: parseInt(card.dataset.imageId),
            order: index
        });
    });

    try {
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
        formData.append('post_id', postId);
        formData.append('order', JSON.stringify(newOrder));

        const response = await fetch('<?php echo SITE_URL; ?>/api/reorder-images-handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
                showToast('Order saved!', 'success');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast('❌ Failed to save order', 'error');
        console.error(error);
    }
}

async function deleteImage(imageId, button) {
    if (!confirm('Delete this image permanently?')) return;

    button.disabled = true;
    button.textContent = 'Deleting...';

    try {
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
        formData.append('image_id', imageId);

        const response = await fetch('<?php echo SITE_URL; ?>/api/delete-image-handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast('Image deleted!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
        button.disabled = false;
        button.textContent = 'Delete';
    }
}

function showToast(message, type = 'info') {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6'
    };

    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${colors[type]};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-weight: 600;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
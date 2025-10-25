<?php
/**
 * Manage Post Images
 * Save as: pages/manage-images.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/blog.php';
require_once __DIR__ . '/../includes/image-functions.php';

requireLogin();

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

$currentUser = getCurrentUser();
if ($post['user_id'] != $currentUser['id'] && !isAdmin()) {
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

$images = getPostImages($postId);
$pageTitle = 'Manage Images';
include __DIR__ . '/../includes/header.php';
?>

<style>
.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}

.image-item {
    position: relative;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    background: #f9fafb;
}

.image-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

.image-item-actions {
    padding: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
}

.image-order {
    background: #4f46e5;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.drag-handle {
    cursor: move;
    padding: 0.5rem;
    color: #6b7280;
}

.upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s;
}

.upload-zone:hover {
    border-color: #4f46e5;
    background: #f0f4ff;
}

.upload-zone.dragover {
    border-color: #4f46e5;
    background: #e0e7ff;
}
</style>

<main class="main-content">
    <div class="container">
        <div class="card">
            <h1>Manage Images for: <?php echo htmlspecialchars($post['title']); ?></h1>
            <p style="color: var(--text-light);">Upload and organize images for this post</p>
            <a href="view-post.php?id=<?php echo $postId; ?>" class="btn btn-secondary btn-sm">← Back to Post</a>
        </div>

        <div id="message"></div>

        <!-- Upload Section -->
        <div class="card">
            <h2>Upload Images</h2>
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                
                <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
                    <input type="file" 
                           id="fileInput" 
                           name="images[]" 
                           multiple 
                           accept="image/*" 
                           style="display: none;">
                    <p style="font-size: 3rem; margin: 0;">📸</p>
                    <p style="font-size: 1.2rem; font-weight: 600; margin: 0.5rem 0;">Click or drag images here</p>
                    <p style="color: var(--text-light); font-size: 0.9rem;">Support: JPG, PNG, GIF, WEBP (Max 5MB each)</p>
                </div>
                
                <div id="preview" style="margin-top: 1rem;"></div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Upload Images</button>
            </form>
        </div>

        <!-- Current Images -->
        <div class="card">
            <h2>Current Images (<?php echo count($images); ?>)</h2>
            <p style="color: var(--text-light); font-size: 0.9rem;">Drag to reorder • First image shows in preview</p>
            
            <?php if (empty($images)): ?>
                <p style="color: var(--text-light); padding: 2rem; text-align: center;">No images uploaded yet</p>
            <?php else: ?>
                <div class="image-grid" id="imageGrid">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="image-item" data-image-id="<?php echo $image['id']; ?>" draggable="true">
                            <img src="<?php echo SITE_URL . htmlspecialchars($image['image_path']); ?>" alt="Post image">
                            <div class="image-item-actions">
                                <span class="image-order">#<?php echo $index + 1; ?></span>
                                <span class="drag-handle">☰</span>
                                <button onclick="deleteImage(<?php echo $image['id']; ?>)" class="btn btn-danger btn-sm">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    // Debug: Check configuration
    console.log('=== CONFIGURATION ===');
    console.log('Current page:', window.location.href);
    console.log('Post ID:', <?php echo $postId; ?>);
    
    // File input preview
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        preview.innerHTML = '';
        
        if (e.target.files.length === 0) {
            return;
        }
        
        Array.from(e.target.files).forEach(file => {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                console.warn('Skipping non-image file:', file.name);
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                console.warn('File too large:', file.name);
                alert(`File ${file.name} is too large (max 5MB)`);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.cssText = 'display: inline-block; margin: 0.5rem; position: relative;';
                div.innerHTML = `<img src="${e.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 6px; border: 2px solid #e5e7eb;">`;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // Upload form
    document.getElementById('uploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const messageDiv = document.getElementById('message');
        const fileInput = document.getElementById('fileInput');
        const formData = new FormData(this);
        
        // Validate files
        if (!fileInput.files || fileInput.files.length === 0) {
            messageDiv.innerHTML = '<div class="alert alert-error">❌ Please select at least one image</div>';
            return;
        }
        
        // Debug
        console.log('=== UPLOAD DEBUG ===');
        console.log('Post ID:', formData.get('post_id'));
        console.log('CSRF Token:', formData.get('csrf_token'));
        console.log('Files:', fileInput.files.length);
        for (let i = 0; i < fileInput.files.length; i++) {
            console.log(`File ${i}:`, fileInput.files[i].name, fileInput.files[i].size, fileInput.files[i].type);
        }
        
        messageDiv.innerHTML = '<div class="alert alert-info">Uploading...</div>';
        
        // Disable submit button
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading...';
        
        try {
            // Use relative path from pages/ to api/
            const uploadUrl = '../api/upload-post-images-handler.php';
            console.log('Upload URL:', uploadUrl);
            
            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', {
                'content-type': response.headers.get('content-type'),
                'content-length': response.headers.get('content-length')
            });
            
            const text = await response.text();
            console.log('Response text:', text);
            
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Server returned invalid response: ' + text.substring(0, 200));
            }
            
            console.log('Parsed result:', result);
            
            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success">✅ ' + result.message + '</div>';
                fileInput.value = '';
                document.getElementById('preview').innerHTML = '';
                setTimeout(() => location.reload(), 1500);
            } else {
                messageDiv.innerHTML = '<div class="alert alert-error">❌ ' + result.message + '</div>';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Upload Images';
            }
        } catch (error) {
            console.error('Fetch error:', error);
            messageDiv.innerHTML = '<div class="alert alert-error">❌ Upload failed: ' + error.message + '</div>';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Upload Images';
        }
    });

    // Delete image
    function deleteImage(imageId) {
        if (!confirm('Delete this image? This cannot be undone.')) return;
        
        const formData = new FormData();
        formData.append('image_id', imageId);
        formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
        
        fetch('../api/delete-image-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ Image deleted');
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('❌ Delete failed: ' + error.message);
        });
    }

    // Drag and drop reorder
    let draggedItem = null;

    document.querySelectorAll('.image-item').forEach(item => {
        item.addEventListener('dragstart', function() {
            draggedItem = this;
            this.style.opacity = '0.5';
        });

        item.addEventListener('dragend', function() {
            this.style.opacity = '';
        });

        item.addEventListener('dragover', function(e) {
            e.preventDefault();
        });

        item.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedItem !== this) {
                const allItems = [...document.querySelectorAll('.image-item')];
                const draggedIndex = allItems.indexOf(draggedItem);
                const droppedIndex = allItems.indexOf(this);
                
                if (draggedIndex < droppedIndex) {
                    this.parentNode.insertBefore(draggedItem, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(draggedItem, this);
                }
                
                saveNewOrder();
            }
        });
    });

    function saveNewOrder() {
        const items = document.querySelectorAll('.image-item');
        const imageIds = Array.from(items).map(item => item.dataset.imageId);
        
        const formData = new FormData();
        formData.append('post_id', <?php echo $postId; ?>);
        formData.append('image_ids', JSON.stringify(imageIds));
        formData.append('csrf_token', '<?php echo getCSRFToken(); ?>');
        
        fetch('../api/reorder-images-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Order saved successfully');
                location.reload();
            } else {
                console.error('Failed to save order:', data.message);
            }
        })
        .catch(error => {
            console.error('Reorder error:', error);
        });
    }
    
    // Drag and drop on upload zone
    const uploadZone = document.querySelector('.upload-zone');
    
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('fileInput').files = files;
            document.getElementById('fileInput').dispatchEvent(new Event('change'));
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
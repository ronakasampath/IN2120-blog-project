<?php
/**
 * Post Images Management Functions - COMPLETE
 * Save as: includes/image-functions.php
 */

/**
 * Upload multiple images for a post
 */
function uploadPostImages($postId, $files) {
    $db = getDB();
    $results = [];
    $errors = [];
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Get current max order
    try {
        $stmt = $db->prepare("SELECT MAX(image_order) as max_order FROM post_images WHERE post_id = ?");
        $stmt->execute([$postId]);
        $result = $stmt->fetch();
        $currentOrder = ($result['max_order'] ?? -1) + 1;
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage(), 'uploaded' => [], 'errors' => []];
    }
    
    // Handle both single and multiple file uploads
    if (!is_array($files['tmp_name'])) {
        $files = [
            'tmp_name' => [$files['tmp_name']],
            'name' => [$files['name']],
            'size' => [$files['size']],
            'error' => [$files['error']]
        ];
    }
    
    foreach ($files['tmp_name'] as $key => $tmpName) {
        if (empty($tmpName) || $files['error'][$key] !== UPLOAD_ERR_OK) {
            continue;
        }
        
        // Validate size
        if ($files['size'][$key] > $maxSize) {
            $errors[] = "{$files['name'][$key]} is too large (max 5MB)";
            continue;
        }
        
        // Validate extension
        $ext = strtolower(pathinfo($files['name'][$key], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "{$files['name'][$key]} has invalid file type";
            continue;
        }
        
        // Create upload directory
        $uploadDir = __DIR__ . '/../uploads/posts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '_' . $key . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($tmpName, $filepath)) {
            $imagePath = '/uploads/posts/' . $filename;
            
            // Save to database
            try {
                $stmt = $db->prepare("INSERT INTO post_images (post_id, image_path, image_order) VALUES (?, ?, ?)");
                $stmt->execute([$postId, $imagePath, $currentOrder]);
                $results[] = ['path' => $imagePath, 'order' => $currentOrder];
                $currentOrder++;
            } catch (PDOException $e) {
                $errors[] = "Database error for {$files['name'][$key]}: " . $e->getMessage();
            }
        } else {
            $errors[] = "Failed to save {$files['name'][$key]}";
        }
    }
    
    return [
        'success' => count($results) > 0,
        'uploaded' => $results,
        'errors' => $errors,
        'count' => count($results),
        'message' => count($results) > 0 ? count($results) . ' image(s) uploaded successfully' : 'No images uploaded'
    ];
}

/**
 * Get all images for a post
 */
function getPostImages($postId) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("
            SELECT id, image_path, image_order 
            FROM post_images 
            WHERE post_id = ? 
            ORDER BY image_order ASC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get post images error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get first image (for preview)
 */
function getFirstPostImage($postId) {
    $images = getPostImages($postId);
    return !empty($images) ? $images[0]['image_path'] : null;
}

/**
 * Delete post image
 */
function deletePostImage($imageId, $userId) {
    $db = getDB();
    
    try {
        // Get image info and check ownership
        $stmt = $db->prepare("
            SELECT pi.image_path, bp.user_id 
            FROM post_images pi 
            JOIN blogPost bp ON pi.post_id = bp.id 
            WHERE pi.id = ?
        ");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();
        
        if (!$image) {
            return ['success' => false, 'message' => 'Image not found'];
        }
        
        if ($image['user_id'] != $userId && !isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        // Delete file
        $filePath = __DIR__ . '/..' . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete from database
        $stmt = $db->prepare("DELETE FROM post_images WHERE id = ?");
        $stmt->execute([$imageId]);
        
        return ['success' => true, 'message' => 'Image deleted'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete image'];
    }
}

/**
 * Reorder images
 */
function reorderPostImages($postId, $imageIds) {
    $db = getDB();
    
    try {
        $db->beginTransaction();
        
        $order = 0;
        foreach ($imageIds as $imageId) {
            $stmt = $db->prepare("UPDATE post_images SET image_order = ? WHERE id = ? AND post_id = ?");
            $stmt->execute([$order, $imageId, $postId]);
            $order++;
        }
        
        $db->commit();
        return ['success' => true, 'message' => 'Images reordered'];
    } catch (PDOException $e) {
        $db->rollBack();
        return ['success' => false, 'message' => 'Failed to reorder images'];
    }
}
?>
<?php
/**
 * Enhanced Blog Functions - CORRECTED VERSION
 * Save as: includes/blog-enhanced.php
 */

// Don't include other files here - they're already included in pages
// This file just provides additional functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
/**
 * Upload image (profile or post)
 */
function uploadImage($file, $type = 'profile') {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large (max 5MB)'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $folder = ($type === 'profile') ? 'profiles' : 'posts';
    $uploadDir = __DIR__ . '/../uploads/' . $folder . '/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => '/uploads/' . $folder . '/' . $filename
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to save file'];
}

/**
 * Update user profile picture
 */
function updateProfilePicture($userId, $file) {
    $db = getDB();
    
    $upload = uploadImage($file, 'profile');
    if (!$upload['success']) {
        return $upload;
    }
    
    try {
        // Delete old profile picture
        $stmt = $db->prepare("SELECT profile_picture FROM user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && $user['profile_picture']) {
            $oldFile = __DIR__ . '/../uploads/profiles/' . basename($user['profile_picture']);
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        
        // Update database
        $stmt = $db->prepare("UPDATE user SET profile_picture = ? WHERE id = ?");
        $stmt->execute([$upload['path'], $userId]);
        
        return [
            'success' => true,
            'message' => 'Profile picture updated!',
            'path' => $upload['path']
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error'];
    }
}

/**
 * Add featured image to post
 */
function addPostImage($postId, $file) {
    $db = getDB();
    
    $upload = uploadImage($file, 'post');
    if (!$upload['success']) {
        return $upload;
    }
    
    try {
        $stmt = $db->prepare("UPDATE blogPost SET featured_image = ? WHERE id = ?");
        $stmt->execute([$upload['path'], $postId]);
        
        return [
            'success' => true,
            'message' => 'Image uploaded!',
            'path' => $upload['path']
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error'];
    }
}

/**
 * Add comment to post
 */
function addComment($postId, $userId, $commentText) {
    $db = getDB();
    
    if (empty(trim($commentText))) {
        return ['success' => false, 'message' => 'Comment cannot be empty'];
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt->execute([$postId, $userId, trim($commentText)]);
        
        return [
            'success' => true,
            'message' => 'Comment added!',
            'comment_id' => $db->lastInsertId()
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to add comment'];
    }
}

/**
 * Get comments for post
 */
function getComments($postId) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("
            SELECT 
                c.id,
                c.comment_text,
                c.created_at,
                c.user_id,
                u.username,
                u.profile_picture
            FROM comments c
            JOIN user u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Delete comment
 */
function deleteComment($commentId, $userId) {
    $db = getDB();
    
    try {
        // Check ownership
        $stmt = $db->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        
        if (!$comment || ($comment['user_id'] != $userId && !isAdmin())) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        
        return ['success' => true, 'message' => 'Comment deleted'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete comment'];
    }
}

/**
 * Toggle like on post
 */
function toggleLike($postId, $userId) {
    $db = getDB();
    
    try {
        // Check if already liked
        $stmt = $db->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        
        if ($stmt->fetch()) {
            // Unlike
            $stmt = $db->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $userId]);
            return ['success' => true, 'liked' => false, 'message' => 'Unliked'];
        } else {
            // Like
            $stmt = $db->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$postId, $userId]);
            return ['success' => true, 'liked' => true, 'message' => 'Liked!'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to toggle like'];
    }
}

/**
 * Get like count for post
 */
function getLikeCount($postId) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
        $stmt->execute([$postId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Check if user liked post
 */
function hasUserLiked($postId, $userId) {
    if (!$userId) return false;
    
    $db = getDB();
    
    try {
        $stmt = $db->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get comment count for post
 */
function getCommentCount($postId) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM comments WHERE post_id = ?");
        $stmt->execute([$postId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}
?>
<?php
/**
 * Blog Functions - COMPLETE WITH ADMIN
 * Save as: includes/blog.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/blog-enhanced.php';
/**
 * Create blog post
 */
function createPost($userId, $title, $content) {
    $db = getDB();
    
    if (empty(trim($title))) {
        return ['success' => false, 'message' => 'Title is required'];
    }
    
    if (empty(trim($content))) {
        return ['success' => false, 'message' => 'Content is required'];
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO blogPost (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$userId, trim($title), trim($content)]);
        
        return [
            'success' => true,
            'message' => 'Post created successfully!',
            'post_id' => $db->lastInsertId()
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to create post'];
    }
}

/**
 * Get all posts
 */
function getAllPosts() {
    $db = getDB();
    
    try {
        $stmt = $db->query("
            SELECT 
                bp.id, 
                bp.title, 
                bp.content, 
                bp.created_at, 
                bp.updated_at,
                u.username,
                u.id as user_id
            FROM blogPost bp
            JOIN user u ON bp.user_id = u.id
            ORDER BY bp.created_at DESC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get single post
 */
function getPost($postId) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("
            SELECT 
                bp.id, 
                bp.title, 
                bp.content, 
                bp.created_at, 
                bp.updated_at,
                bp.user_id,
                u.username
            FROM blogPost bp
            JOIN user u ON bp.user_id = u.id
            WHERE bp.id = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get posts by user
 */
function getPostsByUser($userId) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("
            SELECT 
                bp.id, 
                bp.title, 
                bp.content, 
                bp.created_at, 
                bp.updated_at,
                u.username
            FROM blogPost bp
            JOIN user u ON bp.user_id = u.id
            WHERE bp.user_id = ?
            ORDER BY bp.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Update post - Admin can edit any post
 */
function updatePost($postId, $userId, $title, $content) {
    $db = getDB();
    
    if (empty(trim($title))) {
        return ['success' => false, 'message' => 'Title is required'];
    }
    
    if (empty(trim($content))) {
        return ['success' => false, 'message' => 'Content is required'];
    }
    
    // Check if post exists
    $post = getPost($postId);
    if (!$post) {
        return ['success' => false, 'message' => 'Post not found'];
    }
    
    // Check ownership - Allow if user owns post OR user is admin
    if ($post['user_id'] != $userId && !isAdmin()) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }
    
    try {
        $stmt = $db->prepare("UPDATE blogPost SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([trim($title), trim($content), $postId]);
        return ['success' => true, 'message' => 'Post updated successfully!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update post'];
    }
}

/**
 * Delete post - Admin can delete any post
 */
function deletePost(int $postId, int $userId): array
{
    $db = getDB();

    // Check if the post exists
    $post = getPost($postId);
    if (!$post) {
        return ['success' => false, 'message' => 'Post not found'];
    }

    // Check authorization - Allow if user owns post OR user is admin
    if ($post['user_id'] != $userId && !isAdmin()) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }

    // Perform the deletion
    try {
        $stmt = $db->prepare("DELETE FROM blogPost WHERE id = :id");
        $stmt->execute(['id' => $postId]);

        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Post deleted successfully.'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete post.'];
        }

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error during deletion.'];
    }
}

/**
 * Format date
 */
function formatDate($datetime) {
    return date('F j, Y \a\t g:i A', strtotime($datetime));
}

/**
 * Get excerpt
 */
function getExcerpt($content, $length = 150) {
    $text = strip_tags($content);
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}
?>
<?php
/**
 * Blog Functions - FINAL CORRECTED VERSION (Using getDB() function)
 * Ensures correct database access using the provided getDB() function.
 * Save as: includes/blog.php
 */

// FIX: Correct the path to point from 'includes' up to the root, then into 'config'.
require_once __DIR__ . '/../config/database.php';


/**
 * Create blog post
 */
function createPost($userId, $title, $content) {
    $db = getDB(); // FIX: Use getDB() to get connection
    
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
    $db = getDB(); // FIX: Use getDB() to get connection
    
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
    $db = getDB(); // FIX: Use getDB() to get connection
    
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
    $db = getDB(); // FIX: Use getDB() to get connection
    
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
 * Update post
 */
function updatePost($postId, $userId, $title, $content) {
    $db = getDB(); // FIX: Use getDB() to get connection
    
    if (empty(trim($title))) {
        return ['success' => false, 'message' => 'Title is required'];
    }
    
    if (empty(trim($content))) {
        return ['success' => false, 'message' => 'Content is required'];
    }
    
    // Check ownership
    $post = getPost($postId); 
    if (!$post) {
        return ['success' => false, 'message' => 'Post not found'];
    }
    
    if ($post['user_id'] != $userId) {
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
 * Delete post (Corrected signature for handler)
 */
function deletePost(int $postId, int $userId): array
{
    $db = getDB(); // FIX: Use getDB() to get connection

    // 1. Check if the post exists and belongs to the user (Authorization)
    $post = getPost($postId); 
    if (!$post) {
        return ['success' => false, 'message' => 'Post not found'];
    }

    if ($post['user_id'] != $userId) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }

    // 2. Perform the deletion
    try {
        $stmt = $db->prepare("DELETE FROM blogPost WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $postId, 'user_id' => $userId]);

        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Post deleted successfully.'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete post (no rows affected).'];
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
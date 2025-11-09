<?php
/**
 * Enhanced Blog Functions - COMPLETE WITH COMMENTS & LIKES
 * Save as: includes/blog-enhanced.php
 */

require_once __DIR__ . '/../config/database.php';

/**
 * ========================================
 * COMMENT FUNCTIONS
 * ========================================
 */

/**
 * Add a comment to a post
 */
function addComment($postId, $userId, $commentText) {
    $db = getDB();
    
    try {
        // Validate inputs
        if (empty(trim($commentText))) {
            return ['success' => false, 'message' => 'Comment text is required'];
        }
        
        if (strlen($commentText) > 1000) {
            return ['success' => false, 'message' => 'Comment is too long'];
        }
        
        // Check if post exists
        $stmt = $db->prepare("SELECT id FROM blogpost WHERE id = ?");
        $stmt->execute([$postId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Post not found'];
        }
        
        // Check if comment table exists
        $stmt = $db->query("SHOW TABLES LIKE 'comment'");
        if ($stmt->rowCount() === 0) {
            // Create comment table
            $db->exec("
                CREATE TABLE comment (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    user_id INT NOT NULL,
                    comment_text TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (post_id) REFERENCES blogpost(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
        
        // Insert comment
        $stmt = $db->prepare("
            INSERT INTO comment (post_id, user_id, comment_text, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$postId, $userId, trim($commentText)]);
        
        $commentId = $db->lastInsertId();
        
        // Get the inserted comment with user info
        $stmt = $db->prepare("
            SELECT 
                c.id,
                c.comment_text,
                c.created_at,
                c.user_id,
                u.username,
                u.profile_picture
            FROM comment c
            JOIN user u ON c.user_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        
        return [
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment
        ];
        
    } catch (PDOException $e) {
        error_log("Add comment error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to add comment'];
    }
}

/**
 * Get comments for a post
 */
function getComments($postId) {
    $db = getDB();
    
    try {
        // Check if comment table exists
        $stmt = $db->query("SHOW TABLES LIKE 'comment'");
        if ($stmt->rowCount() === 0) {
            return [];
        }
        
        $stmt = $db->prepare("
            SELECT 
                c.id,
                c.comment_text,
                c.created_at,
                c.user_id,
                u.username,
                u.profile_picture
            FROM comment c
            JOIN user u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get comments error: " . $e->getMessage());
        return [];
    }
}

/**
 * Delete a comment
 */
function deleteComment($commentId, $userId) {
    $db = getDB();
    
    try {
        // Check if comment table exists
        $stmt = $db->query("SHOW TABLES LIKE 'comment'");
        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Comment not found'];
        }
        
        // Get comment to check ownership
        $stmt = $db->prepare("SELECT user_id FROM comment WHERE id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        
        if (!$comment) {
            return ['success' => false, 'message' => 'Comment not found'];
        }
        
        // Check if user owns the comment or is admin
        if ($comment['user_id'] != $userId && !isAdmin()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        // Delete comment
        $stmt = $db->prepare("DELETE FROM comment WHERE id = ?");
        $stmt->execute([$commentId]);
        
        return ['success' => true, 'message' => 'Comment deleted successfully'];
        
    } catch (PDOException $e) {
        error_log("Delete comment error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete comment'];
    }
}

/**
 * Get comment count for a post
 */
function getCommentCount($postId) {
    $db = getDB();
    
    try {
        // Check if comment table exists
        $stmt = $db->query("SHOW TABLES LIKE 'comment'");
        if ($stmt->rowCount() === 0) {
            return 0;
        }
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM comment WHERE post_id = ?");
        $stmt->execute([$postId]);
        return $stmt->fetchColumn();
        
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Format comment date
 */
function formatCommentDate($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}

/**
 * ========================================
 * LIKE FUNCTIONS
 * ========================================
 */

/**
 * Toggle like on a post
 */
function toggleLike($postId, $userId) {
    $db = getDB();
    
    try {
        // Check if post exists
        $stmt = $db->prepare("SELECT id FROM blogpost WHERE id = ?");
        $stmt->execute([$postId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Post not found'];
        }
        
        // Check if like table exists
        $stmt = $db->query("SHOW TABLES LIKE 'post_like'");
        if ($stmt->rowCount() === 0) {
            // Create like table
            $db->exec("
                CREATE TABLE post_like (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    user_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_like (post_id, user_id),
                    FOREIGN KEY (post_id) REFERENCES blogpost(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
        
        // Check if already liked
        $stmt = $db->prepare("SELECT id FROM post_like WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        
        if ($stmt->fetch()) {
            // Unlike
            $stmt = $db->prepare("DELETE FROM post_like WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $userId]);
            $liked = false;
            $message = 'Post unliked';
        } else {
            // Like
            $stmt = $db->prepare("INSERT INTO post_like (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$postId, $userId]);
            $liked = true;
            $message = 'Post liked';
        }
        
        // Get new like count
        $likeCount = getLikeCount($postId);
        
        return [
            'success' => true,
            'message' => $message,
            'liked' => $liked,
            'like_count' => $likeCount
        ];
        
    } catch (PDOException $e) {
        error_log("Toggle like error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to toggle like'];
    }
}

/**
 * Get like count for a post
 */
function getLikeCount($postId) {
    $db = getDB();
    
    try {
        // Check if like table exists
        $stmt = $db->query("SHOW TABLES LIKE 'post_like'");
        if ($stmt->rowCount() === 0) {
            return 0;
        }
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM post_like WHERE post_id = ?");
        $stmt->execute([$postId]);
        return (int)$stmt->fetchColumn();
        
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Check if user has liked a post
 */
function hasUserLiked($postId, $userId) {
    $db = getDB();
    
    try {
        // Check if like table exists
        $stmt = $db->query("SHOW TABLES LIKE 'post_like'");
        if ($stmt->rowCount() === 0) {
            return false;
        }
        
        $stmt = $db->prepare("SELECT id FROM post_like WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        return $stmt->fetch() !== false;
        
    } catch (PDOException $e) {
        return false;
    }
}
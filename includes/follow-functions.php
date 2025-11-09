<?php
/**
 * Follow System Functions - FIXED VERSION
 * Save as: includes/follow-functions.php
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is following another user
 */
function isFollowing($followerId, $userId) {
    if (!$followerId || !$userId) return false;
    
    $db = getDB();
    try {
        // FIXED: Use followed_id instead of user_id
        $stmt = $db->prepare("SELECT id FROM followers WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$followerId, $userId]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Error in isFollowing: " . $e->getMessage());
        return false;
    }
}

/**
 * Follow a user
 */
function followUser($followerId, $userId) {
    if (!$followerId || !$userId || $followerId == $userId) {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $db = getDB();
    try {
        // FIXED: Use followed_id instead of user_id
        $stmt = $db->prepare("INSERT IGNORE INTO followers (follower_id, followed_id) VALUES (?, ?)");
        $stmt->execute([$followerId, $userId]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Now following user'];
        } else {
            return ['success' => false, 'message' => 'Already following'];
        }
    } catch (PDOException $e) {
        error_log("Error in followUser: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to follow user: ' . $e->getMessage()];
    }
}

/**
 * Unfollow a user
 */
function unfollowUser($followerId, $userId) {
    if (!$followerId || !$userId) {
        return ['success' => false, 'message' => 'Invalid request'];
    }
    
    $db = getDB();
    try {
        // FIXED: Use followed_id instead of user_id
        $stmt = $db->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$followerId, $userId]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Unfollowed user'];
        } else {
            return ['success' => false, 'message' => 'Not following this user'];
        }
    } catch (PDOException $e) {
        error_log("Error in unfollowUser: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to unfollow user: ' . $e->getMessage()];
    }
}

/**
 * Get follower count for a user
 */
function getFollowerCount($userId) {
    $db = getDB();
    try {
        // FIXED: Use followed_id instead of user_id
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM followers WHERE followed_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getFollowerCount: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get following count for a user
 */
function getFollowingCount($userId) {
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM followers WHERE follower_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getFollowingCount: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get IDs of users that the current user is following
 */
function getFollowedUserIds($userId) {
    $db = getDB();
    try {
        // FIXED: Use followed_id instead of user_id
        $stmt = $db->prepare("SELECT followed_id FROM followers WHERE follower_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error in getFollowedUserIds: " . $e->getMessage());
        return [];
    }
}

/**
 * Get posts from followed users only
 */
function getFollowingPosts($userId, $category = '', $searchQuery = '') {
    $db = getDB();
    
    $followedIds = getFollowedUserIds($userId);
    
    if (empty($followedIds)) {
        return [];
    }
    
    $placeholders = str_repeat('?,', count($followedIds) - 1) . '?';
    
    $sql = "SELECT 
                bp.id, 
                bp.title, 
                bp.content,
                bp.category,
                bp.created_at, 
                bp.updated_at,
                u.username,
                u.id as user_id
            FROM blogPost bp
            JOIN user u ON bp.user_id = u.id
            WHERE bp.user_id IN ($placeholders)";
    
    $params = $followedIds;
    
    if ($category) {
        $sql .= " AND bp.category = ?";
        $params[] = $category;
    }
    
    if ($searchQuery) {
        $sql .= " AND (bp.title LIKE ? OR u.username LIKE ? OR bp.category LIKE ?)";
        $searchTerm = "%{$searchQuery}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " ORDER BY bp.created_at DESC";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting following posts: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user profile data
 */
function getUserProfile($userId) {
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT id, username, email, bio, profile_picture, role, created_at FROM user WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error in getUserProfile: " . $e->getMessage());
        return null;
    }
}

/**
 * Update user bio
 */
function updateUserBio($userId, $bio) {
    $db = getDB();
    try {
        $stmt = $db->prepare("UPDATE user SET bio = ? WHERE id = ?");
        $stmt->execute([trim($bio), $userId]);
        return ['success' => true, 'message' => 'Bio updated successfully'];
    } catch (PDOException $e) {
        error_log("Error in updateUserBio: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update bio: ' . $e->getMessage()];
    }
}

/**
 * Get list of followers for a user
 */
function getFollowers($userId) {
    $db = getDB();
    try {
        $stmt = $db->prepare("
            SELECT u.id, u.username, u.profile_picture, u.created_at
            FROM followers f
            JOIN user u ON f.follower_id = u.id
            WHERE f.followed_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getFollowers: " . $e->getMessage());
        return [];
    }
}

/**
 * Get list of users that a user is following
 */
function getFollowing($userId) {
    $db = getDB();
    try {
        $stmt = $db->prepare("
            SELECT u.id, u.username, u.profile_picture, u.created_at
            FROM followers f
            JOIN user u ON f.followed_id = u.id
            WHERE f.follower_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getFollowing: " . $e->getMessage());
        return [];
    }
}
?>
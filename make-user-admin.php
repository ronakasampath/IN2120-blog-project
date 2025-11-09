<?php
/**
 * Make User Admin - Simple One-Click Tool
 * Save as: make-user-admin.php (in root)
 * Visit: http://localhost/Idea-canvas/make-user-admin.php
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Make User Admin</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        .user-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        .user-card:hover {
            border-color: #4f46e5;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
        }
        .user-info {
            flex: 1;
        }
        .user-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        .user-email {
            color: #6b7280;
            font-size: 0.95rem;
        }
        .user-id {
            color: #9ca3af;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-left: 1rem;
        }
        .badge-admin {
            background: #ef4444;
            color: white;
        }
        .badge-user {
            background: #e5e7eb;
            color: #6b7280;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #4f46e5;
            color: white;
        }
        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-disabled {
            background: #d1d5db;
            color: #9ca3af;
            cursor: not-allowed;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1rem 0;
            border: 2px solid #6ee7b7;
            font-size: 1.1rem;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1rem 0;
            border: 2px solid #fca5a5;
        }
        .empty {
            text-align: center;
            padding: 3rem;
            color: #9ca3af;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border: 2px solid #fbbf24;
        }
        .footer {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e5e7eb;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>Make User Admin</h1>";
echo "<p class='subtitle'>Grant admin privileges to existing users</p>";

// Handle form submission
if (isset($_GET['action']) && $_GET['action'] === 'promote' && isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    
    try {
        $db = getDB();
        
        // Get user details
        $stmt = $db->prepare("SELECT username, email, role FROM user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "<div class='error'>User not found!</div>";
        } elseif ($user['role'] === 'admin') {
            echo "<div class='error'>User <strong>" . htmlspecialchars($user['username']) . "</strong> is already an admin!</div>";
        } else {
            // Promote to admin
            $stmt = $db->prepare("UPDATE user SET role = 'admin' WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo "<div class='success'>
                <h2>Success!</h2>
                <p><strong>" . htmlspecialchars($user['username']) . "</strong> (" . htmlspecialchars($user['email']) . ") is now an <strong>ADMIN</strong>!</p>
                <p style='margin-top: 1rem;'>They can now:</p>
                <ul>
                    <li>Edit any post</li>
                    <li>Delete any post</li>
                    <li>Delete any comment</li>
                    <li>Full admin access</li>
                </ul>
            </div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Handle demote action
if (isset($_GET['action']) && $_GET['action'] === 'demote' && isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("SELECT username, role FROM user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "<div class='error'>❌ User not found!</div>";
        } elseif ($user['role'] === 'user') {
            echo "<div class='error'>User is already a regular user!</div>";
        } else {
            $stmt = $db->prepare("UPDATE user SET role = 'user' WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo "<div class='success'><strong>" . htmlspecialchars($user['username']) . "</strong> demoted to regular user.</div>";
        }
    } catch (PDOException $e) {
            echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Display all users
try {
    $db = getDB();
    $stmt = $db->query("SELECT id, username, email, role, created_at FROM user ORDER BY role DESC, created_at ASC");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<div class='empty'>
            <h2>No Users Found</h2>
            <p>There are no users in the database yet.</p>
        </div>";
    } else {
        echo "<h2 style='margin-top: 2rem;'>All Users (" . count($users) . ")</h2>";
        
        foreach ($users as $user) {
            $isAdmin = $user['role'] === 'admin';
            $badgeClass = $isAdmin ? 'badge-admin' : 'badge-user';
            $badgeText = strtoupper($user['role']);
            
            echo "<div class='user-card'>
                <div class='user-info'>
                    <div class='user-name'>
                        " . htmlspecialchars($user['username']) . "
                        <span class='badge $badgeClass'>$badgeText</span>
                    </div>
                    <div class='user-email'>" . htmlspecialchars($user['email']) . "</div>
                    <div class='user-id'>ID: {$user['id']} • Created: " . date('M d, Y', strtotime($user['created_at'])) . "</div>
                </div>
                <div>";
            
            if ($isAdmin) {
                echo "<a href='?action=demote&user_id={$user['id']}' class='btn btn-danger' onclick='return confirm(\"Remove admin privileges from {$user['username']}?\")'>
                    Demote to User
                </a>";
            } else {
                echo "<a href='?action=promote&user_id={$user['id']}' class='btn btn-primary' onclick='return confirm(\"Make {$user['username']} an admin?\")'>
                    Make Admin
                </a>";
            }
            
            echo "</div>
            </div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>Could not load users: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<div class='warning'>
    <strong>SECURITY WARNING:</strong>
    <p>Delete this file immediately after making your admin user!</p>
    <p>This tool has no authentication and could be accessed by anyone.</p>
</div>";

echo "<div class='footer'>
    <a href='index.php' class='btn btn-success'>🏠 Go to Homepage</a>
    <a href='pages/login.php' class='btn btn-primary' style='margin-left: 1rem;'>Login</a>
    <p style='margin-top: 1rem; color: #ef4444;'><strong>Remember to delete this file!</strong></p>
</div>";

echo "</div>
</body>
</html>";
?>
<?php
/**
 * Create Admin User
 * Save as: create-admin.php (in root folder)
 * Visit: http://localhost/Idea-canvas/create-admin.php
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Create Admin User</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f3f4f6;
            padding: 2rem;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border: 2px solid #6ee7b7;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border: 2px solid #fca5a5;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .credentials {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            margin: 1rem 0;
            font-size: 1.1rem;
        }
        .credentials strong {
            color: #4f46e5;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 1rem;
        }
        .btn:hover {
            background: #4338ca;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border: 2px solid #fbbf24;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        table th, table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        table th {
            background: #f9fafb;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-admin {
            background: #ef4444;
            color: white;
        }
        .badge-user {
            background: #10b981;
            color: white;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
        }
        .form-submit {
            background: #4f46e5;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
        }
        .form-submit:hover {
            background: #4338ca;
        }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>Create Admin User</h1>";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        try {
            $db = getDB();
            
            // Check if username or email already exists
            $stmt = $db->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                echo "<div class='error'><strong>Error:</strong> Username or email already exists!</div>";
            } else {
                // Create admin user
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                
                $stmt = $db->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, 'admin')");
                $stmt->execute([$username, $email, $hashedPassword]);
                
                echo "<div class='success'>
                    <h2>Admin User Created Successfully!</h2>
                </div>";
                
                echo "<div class='credentials'>
                    <h3>Your Admin Credentials:</h3>
                    <p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>
                    <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                    <p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>
                    <p><strong>Role:</strong> <span class='badge badge-admin'>ADMIN</span></p>
                </div>";
                
                echo "<div class='warning'>
                    <strong>IMPORTANT:</strong>
                    <ul>
                        <li>Save these credentials in a secure place</li>
                        <li>Delete this file (create-admin.php) immediately for security</li>
                        <li>Change your password after first login</li>
                    </ul>
                </div>";
                
                echo "<a href='pages/login.php' class='btn'>Go to Login Page</a>";
                
                // Show success, don't show form again
                $hideForm = true;
            }
            
        } catch (PDOException $e) {
            echo "<div class='error'><strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        echo "<div class='error'><strong>Validation Errors:</strong><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul></div>";
    }
}

// Show existing users
try {
    $db = getDB();
    $stmt = $db->query("SELECT id, username, email, role, created_at FROM user ORDER BY role DESC, created_at DESC");
    $users = $stmt->fetchAll();
    
    if (!empty($users)) {
        echo "<div class='info'>
            <h3>Existing Users in Database:</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($users as $user) {
            $badgeClass = $user['role'] === 'admin' ? 'badge-admin' : 'badge-user';
            echo "<tr>
                <td>" . $user['id'] . "</td>
                <td><strong>" . htmlspecialchars($user['username']) . "</strong></td>
                <td>" . htmlspecialchars($user['email']) . "</td>
                <td><span class='badge $badgeClass'>" . strtoupper($user['role']) . "</span></td>
                <td>" . date('M d, Y', strtotime($user['created_at'])) . "</td>
            </tr>";
        }
        
        echo "</tbody>
            </table>
        </div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>Could not fetch existing users.</div>";
}

// Show form if not submitted successfully
if (!isset($hideForm)):
?>

<form method="POST" action="">
    <h2>Create New Admin Account</h2>
    
    <div class="form-group">
        <label class="form-label" for="username">Username *</label>
        <input type="text" 
               id="username" 
               name="username" 
               class="form-control" 
               placeholder="admin" 
               required 
               minlength="3"
               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label class="form-label" for="email">Email Address *</label>
        <input type="email" 
               id="email" 
               name="email" 
               class="form-control" 
               placeholder="admin@example.com" 
               required
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label class="form-label" for="password">Password *</label>
        <input type="password" 
               id="password" 
               name="password" 
               class="form-control" 
               placeholder="At least 6 characters" 
               required 
               minlength="6">
    </div>
    
    <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm Password *</label>
        <input type="password" 
               id="confirm_password" 
               name="confirm_password" 
               class="form-control" 
               placeholder="Re-enter password" 
               required>
    </div>
    
    <button type="submit" class="form-submit">Create Admin User</button>
</form>

<div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #e5e7eb;">
    <h3>Quick Start Guide:</h3>
    <ol>
        <li>Fill in the form above to create your admin account</li>
        <li>Save your credentials securely</li>
        <li><strong>Delete this file</strong> after creating admin</li>
        <li>Login to your admin account</li>
        <li>Create content and manage users</li>
    </ol>
</div>

<?php endif; ?>

    </div>
</body>
</html>
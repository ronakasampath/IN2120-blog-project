-- =====================================================
-- BLOG APPLICATION DATABASE SETUP
-- Run this in phpMyAdmin (http://localhost/phpmyadmin)
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS blog_application CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_application;

-- Users Table
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Posts Table
CREATE TABLE IF NOT EXISTS blogPost (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample User (Password: password123)
INSERT INTO user (username, email, password, role) VALUES 
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert Sample Blog Posts
INSERT INTO blogPost (user_id, title, content) VALUES 
(1, 'Welcome to Our Blog Platform', '# Welcome!\n\nThis is a sample blog post to demonstrate our platform.\n\n## Features\n- Easy to use markdown editor\n- User authentication\n- Create, edit, and delete posts\n\nEnjoy blogging!'),
(1, 'Getting Started with Blogging', '# Getting Started\n\nHere are some tips for your first blog post:\n\n1. Choose an interesting topic\n2. Write engaging content\n3. Use markdown for formatting\n\n**Happy blogging!**');

-- Verify tables created
SHOW TABLES;
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blog_d');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'StoryCraft');
define('SITE_URL', 'http://localhost/blog');
define('POSTS_PER_PAGE', 6);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_DIR', 'uploads/');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 6);

// Allowed file types for uploads
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt']);

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Set timezone
date_default_timezone_set('UTC');
?>
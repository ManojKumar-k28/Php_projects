<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'blog_db');

// File Upload Configuration
define('UPLOAD_DIR', 'uploads/');
define('IMAGE_DIR', UPLOAD_DIR . 'images/');
define('DOCUMENT_DIR', UPLOAD_DIR . 'documents/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt']);

// Site Configuration
define('SITE_NAME', 'ModernBlog');
define('POSTS_PER_PAGE', 6);

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(IMAGE_DIR)) {
    mkdir(IMAGE_DIR, 0755, true);
}
if (!file_exists(DOCUMENT_DIR)) {
    mkdir(DOCUMENT_DIR, 0755, true);
}

// Start session
session_start();

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
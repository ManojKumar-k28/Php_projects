<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'models/Post.php';
require_once 'models/FileUpload.php';

$post = new Post($pdo);
$fileUpload = new FileUpload($pdo);
$currentUser = $auth->getCurrentUser();

// Get post ID
$postId = intval($_GET['id'] ?? 0);
if (!$postId) {
    header('Location: index.php');
    exit;
}

// Get post
$postData = $post->getById($postId);
if (!$postData) {
    header('Location: index.php?error=Post not found');
    exit;
}

// Increment views
$post->incrementViews($postId);

// Get post files
$files = $fileUpload->getPostFiles($postId);

// Check if user can edit this post
$canEdit = $currentUser && $post->canEdit($postId, $currentUser['id'], $currentUser['role']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($postData['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <meta name="description" content="<?php echo htmlspecialchars($postData['excerpt'] ?: substr(strip_tags($postData['content']), 0, 160)); ?>">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <main class="main">
        <div class="container">
            <article class="post-article">
                <header class="post-header">
                    <?php if ($postData['featured_image']): ?>
                        <div class="post-featured-image">
                            <img src="<?php echo htmlspecialchars($postData['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($postData['title']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-title-section">
                        <h1 class="post-title"><?php echo htmlspecialchars($postData['title']); ?></h1>
                        
                        <div class="post-meta">
                            <span class="author">👤 <?php echo htmlspecialchars($postData['author_name']); ?></span>
                            <span class="date">📅 <?php echo date('F j, Y', strtotime($postData['created_at'])); ?></span>
                            <span class="views">👁️ <?php echo number_format($postData['views']); ?> views</span>
                            <?php if ($postData['updated_at'] !== $postData['created_at']): ?>
                                <span class="updated">✏️ Updated <?php echo date('M j, Y', strtotime($postData['updated_at'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($canEdit): ?>
                        <div class="post-actions">
                            <a href="edit_post.php?id=<?php echo $postData['id']; ?>" class="btn btn-primary btn-sm">✏️ Edit</a>
                            <a href="admin_posts.php" class="btn btn-secondary btn-sm">📋 Manage Posts</a>
                        </div>
                    <?php endif; ?>
                </header>

                <div class="post-content">
                    <?php if ($postData['excerpt']): ?>
                        <div class="post-excerpt">
                            <p><em><?php echo htmlspecialchars($postData['excerpt']); ?></em></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-body">
                        <?php echo $postData['content']; ?>
                    </div>
                    
                    <?php if (!empty($files)): ?>
                        <div class="post-attachments">
                            <h3>📎 Attachments</h3>
                            <div class="attachments-grid">
                                <?php foreach ($files as $file): ?>
                                    <div class="attachment-item">
                                        <div class="attachment-icon">
                                            <?php
                                            $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                                            $icon = match($ext) {
                                                'jpg', 'jpeg', 'png', 'gif' => '🖼️',
                                                'pdf' => '📄',
                                                'doc', 'docx' => '📝',
                                                'txt' => '📄',
                                                default => '📎'
                                            };
                                            echo $icon;
                                            ?>
                                        </div>
                                        <div class="attachment-info">
                                            <a href="view_file.php?id=<?php echo $file['id']; ?>" 
                                               class="attachment-name">
                                                <?php echo htmlspecialchars($file['original_name']); ?>
                                            </a>
                                            <div class="attachment-size">
                                                <?php echo formatFileSize($file['file_size']); ?>
                                            </div>
                                            <div class="attachment-actions">
                                                <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                    <a href="view_file.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-primary">🔍 View</a>
                                                <?php elseif ($ext === 'pdf'): ?>
                                                    <a href="view_file.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-primary">📖 Read</a>
                                                <?php elseif (in_array($ext, ['txt', 'md'])): ?>
                                                    <a href="view_file.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-primary">📝 Read</a>
                                                <?php endif; ?>
                                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" 
                                                   download="<?php echo htmlspecialchars($file['original_name']); ?>" 
                                                   class="btn btn-sm btn-secondary">📥 Download</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
            
            <div class="post-navigation">
                <a href="index.php" class="btn btn-secondary">← Back to Stories</a>
                <?php if ($currentUser): ?>
                    <a href="create_post.php" class="btn btn-primary">✨ Write Your Story</a>
                <?php else: ?>
                    <button onclick="showRegisterModal()" class="btn btn-primary">✨ Join Community</button>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php if (!$currentUser): ?>
        <?php include 'templates/auth_modals.php'; ?>
    <?php endif; ?>
    <?php include 'templates/footer.php'; ?>

    <script src="assets/js/validation.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 1) . ' ' . $units[$pow];
}
?>
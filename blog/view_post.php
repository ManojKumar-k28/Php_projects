<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'file_handler.php';

$postId = intval($_GET['id'] ?? 0);

if (!$postId) {
    header('Location: index.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$fileHandler = new FileHandler($pdo);

// Get post details
$stmt = $pdo->prepare("
    SELECT p.*, u.username as author_name 
    FROM posts p 
    JOIN users u ON p.author_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

// Update view count
$stmt = $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
$stmt->execute([$postId]);

// Get post files
$files = $fileHandler->getPostFiles($postId);

// Separate images and documents
$images = array_filter($files, function($file) {
    return $file['file_type'] === 'image';
});

$documents = array_filter($files, function($file) {
    return $file['file_type'] === 'document';
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">
                    <a href="index.php"><?php echo SITE_NAME; ?></a>
                </h1>
                <nav class="nav">
                    <a href="index.php" class="btn btn-secondary">← Back to Posts</a>
                    <?php if ($currentUser && $currentUser['id'] == $post['author_id']): ?>
                        <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Edit Post</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <article class="post-detail">
                <header class="post-header">
                    <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <div class="post-meta">
                        <div class="meta-info">
                            <span class="author">By <?php echo htmlspecialchars($post['author_name']); ?></span>
                            <span class="date"><?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?></span>
                            <span class="views">👁️ <?php echo $post['views']; ?> views</span>
                        </div>
                        
                        <?php if ($post['status'] === 'draft'): ?>
                            <span class="status-badge draft">Draft</span>
                        <?php endif; ?>
                    </div>
                </header>

                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>

                <!-- Images Section -->
                <?php if (!empty($images)): ?>
                    <section class="post-images">
                        <h3>Images</h3>
                        <div class="image-gallery">
                            <?php foreach ($images as $image): ?>
                                <div class="image-item">
                                    <img src="<?php echo htmlspecialchars($image['file_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($image['original_name']); ?>"
                                         onclick="openImageModal('<?php echo htmlspecialchars($image['file_path']); ?>', '<?php echo htmlspecialchars($image['original_name']); ?>')">
                                    <div class="image-info">
                                        <span class="image-name"><?php echo htmlspecialchars($image['original_name']); ?></span>
                                        <span class="image-size"><?php echo FileHandler::formatFileSize($image['file_size']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Documents Section -->
                <?php if (!empty($documents)): ?>
                    <section class="post-documents">
                        <h3>Documents</h3>
                        <div class="document-list">
                            <?php foreach ($documents as $document): ?>
                                <div class="document-item">
                                    <div class="document-info">
                                        <span class="document-icon">
                                            <?php 
                                            $extension = pathinfo($document['original_name'], PATHINFO_EXTENSION);
                                            echo FileHandler::getFileIcon('document', $extension); 
                                            ?>
                                        </span>
                                        <div class="document-details">
                                            <span class="document-name"><?php echo htmlspecialchars($document['original_name']); ?></span>
                                            <span class="document-size"><?php echo FileHandler::formatFileSize($document['file_size']); ?></span>
                                        </div>
                                    </div>
                                    <div class="document-actions">
                                        <?php if (strtolower($extension) === 'pdf'): ?>
                                            <a href="<?php echo htmlspecialchars($document['file_path']); ?>" 
                                               target="_blank" class="btn btn-sm btn-secondary">View</a>
                                        <?php endif; ?>
                                        <a href="<?php echo htmlspecialchars($document['file_path']); ?>" 
                                           download="<?php echo htmlspecialchars($document['original_name']); ?>"
                                           class="btn btn-sm btn-primary">Download</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <footer class="post-footer">
                    <div class="post-actions">
                        <button onclick="sharePost()" class="btn btn-secondary">Share</button>
                        <?php if ($currentUser && $currentUser['id'] == $post['author_id']): ?>
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Edit Post</a>
                        <?php endif; ?>
                    </div>
                </footer>
            </article>
        </div>
    </main>

    <!-- Image Modal -->
    <div id="imageModal" class="modal image-modal">
        <div class="modal-content image-modal-content">
            <span class="close" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" src="" alt="">
            <div class="image-modal-caption" id="imageCaption"></div>
        </div>
    </div>

    <script>
        function openImageModal(src, caption) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const captionText = document.getElementById('imageCaption');
            
            modal.style.display = 'block';
            modalImg.src = src;
            captionText.textContent = caption;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function sharePost() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo addslashes($post['title']); ?>',
                    text: '<?php echo addslashes($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 100)); ?>',
                    url: window.location.href
                });
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(window.location.href).then(function() {
                    alert('Post URL copied to clipboard!');
                });
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target === modal) {
                closeImageModal();
            }
        }

        // Keyboard navigation for modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>
</html>
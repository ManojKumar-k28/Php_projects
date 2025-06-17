<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'file_handler.php';

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$postId = intval($_GET['id'] ?? 0);
$currentUser = $auth->getCurrentUser();
$fileHandler = new FileHandler($pdo);

if (!$postId) {
    header('Location: index.php');
    exit;
}

// Get post details
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND author_id = ?");
$stmt->execute([$postId, $currentUser['id']]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle file deletion
if (isset($_POST['delete_file'])) {
    $fileId = intval($_POST['file_id']);
    if ($fileHandler->deleteFile($fileId)) {
        $success = 'File deleted successfully.';
    } else {
        $error = 'Failed to delete file.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_file'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required.';
    } else {
        try {
            // Update post
            $stmt = $pdo->prepare("
                UPDATE posts 
                SET title = ?, content = ?, excerpt = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND author_id = ?
            ");
            
            $stmt->execute([$title, $content, $excerpt, $status, $postId, $currentUser['id']]);
            
            // Handle file uploads
            if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                    if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['files']['name'][$i],
                            'type' => $_FILES['files']['type'][$i],
                            'tmp_name' => $_FILES['files']['tmp_name'][$i],
                            'size' => $_FILES['files']['size'][$i]
                        ];
                        
                        try {
                            $fileHandler->uploadFile($file, $postId);
                        } catch (Exception $e) {
                            $error .= "File '{$file['name']}': " . $e->getMessage() . "<br>";
                        }
                    }
                }
            }
            
            if (empty($error)) {
                $success = 'Post updated successfully!';
                // Refresh post data
                $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND author_id = ?");
                $stmt->execute([$postId, $currentUser['id']]);
                $post = $stmt->fetch();
            }
            
        } catch (Exception $e) {
            $error = 'Error updating post: ' . $e->getMessage();
        }
    }
}

// Get post files
$files = $fileHandler->getPostFiles($postId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - <?php echo SITE_NAME; ?></title>
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
                    <a href="view_post.php?id=<?php echo $postId; ?>" class="btn btn-secondary">← Back to Post</a>
                    <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="form-container">
                <h2>Edit Post</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="post-form">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required 
                               value="<?php echo htmlspecialchars($post['title']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
                        <textarea id="excerpt" name="excerpt" rows="3" 
                                  placeholder="Optional brief description of your post..."><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Content *</label>
                        <textarea id="content" name="content" rows="15" required 
                                  placeholder="Write your post content here..."><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>

                    <!-- Existing Files -->
                    <?php if (!empty($files)): ?>
                        <div class="form-group">
                            <label>Attached Files</label>
                            <div class="existing-files">
                                <?php foreach ($files as $file): ?>
                                    <div class="file-item existing-file">
                                        <div class="file-info">
                                            <span class="file-icon">
                                                <?php echo FileHandler::getFileIcon($file['file_type'], pathinfo($file['original_name'], PATHINFO_EXTENSION)); ?>
                                            </span>
                                            <div class="file-details">
                                                <span class="file-name"><?php echo htmlspecialchars($file['original_name']); ?></span>
                                                <span class="file-size"><?php echo FileHandler::formatFileSize($file['file_size']); ?></span>
                                            </div>
                                        </div>
                                        <div class="file-actions">
                                            <?php if ($file['file_type'] === 'image'): ?>
                                                <button type="button" onclick="previewImage('<?php echo htmlspecialchars($file['file_path']); ?>')" 
                                                        class="btn btn-sm btn-secondary">Preview</button>
                                            <?php elseif (pathinfo($file['original_name'], PATHINFO_EXTENSION) === 'pdf'): ?>
                                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" 
                                                   class="btn btn-sm btn-secondary">View</a>
                                            <?php endif; ?>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this file?')">
                                                <input type="hidden" name="delete_file" value="1">
                                                <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="files">Add New Files</label>
                        <div class="file-upload-container">
                            <input type="file" id="files" name="files[]" multiple 
                                   accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.txt">
                            <div class="file-upload-info">
                                <p>Supported formats:</p>
                                <ul>
                                    <li><strong>Images:</strong> JPG, PNG, GIF, WebP</li>
                                    <li><strong>Documents:</strong> PDF, DOC, DOCX, TXT</li>
                                </ul>
                                <p>Maximum file size: <?php echo MAX_FILE_SIZE / 1024 / 1024; ?>MB per file</p>
                            </div>
                        </div>
                        <div id="file-preview" class="file-preview"></div>
                    </div>

                    <div class="form-actions">
                        <div class="status-selection">
                            <label>
                                <input type="radio" name="status" value="draft" 
                                       <?php echo $post['status'] === 'draft' ? 'checked' : ''; ?>>
                                Save as Draft
                            </label>
                            <label>
                                <input type="radio" name="status" value="published" 
                                       <?php echo $post['status'] === 'published' ? 'checked' : ''; ?>>
                                Publish
                            </label>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="view_post.php?id=<?php echo $postId; ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Post</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Image Preview Modal -->
    <div id="imageModal" class="modal image-modal">
        <div class="modal-content image-modal-content">
            <span class="close" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" src="" alt="">
        </div>
    </div>

    <script>
        // File preview functionality
        document.getElementById('files').addEventListener('change', function(e) {
            const preview = document.getElementById('file-preview');
            preview.innerHTML = '';
            
            if (e.target.files.length > 0) {
                const fileList = document.createElement('div');
                fileList.className = 'selected-files';
                
                Array.from(e.target.files).forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    
                    const fileIcon = getFileIcon(file.type, file.name);
                    const fileSize = formatFileSize(file.size);
                    
                    fileItem.innerHTML = `
                        <span class="file-icon">${fileIcon}</span>
                        <span class="file-name">${file.name}</span>
                        <span class="file-size">${fileSize}</span>
                    `;
                    
                    fileList.appendChild(fileItem);
                });
                
                preview.appendChild(fileList);
            }
        });

        function previewImage(src) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            
            modal.style.display = 'block';
            modalImg.src = src;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function getFileIcon(mimeType, fileName) {
            const extension = fileName.split('.').pop().toLowerCase();
            
            if (mimeType.startsWith('image/')) {
                return '🖼️';
            }
            
            switch (extension) {
                case 'pdf': return '📄';
                case 'doc':
                case 'docx': return '📝';
                case 'txt': return '📋';
                default: return '📁';
            }
        }

        function formatFileSize(size) {
            const units = ['B', 'KB', 'MB', 'GB'];
            let unit = 0;
            
            while (size >= 1024 && unit < units.length - 1) {
                size /= 1024;
                unit++;
            }
            
            return Math.round(size * 100) / 100 + ' ' + units[unit];
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target === modal) {
                closeImageModal();
            }
        }
    </script>
</body>
</html>
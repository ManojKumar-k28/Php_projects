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

$currentUser = $auth->getCurrentUser();
$fileHandler = new FileHandler($pdo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required.';
    } else {
        try {
            // Insert post
            $stmt = $pdo->prepare("
                INSERT INTO posts (title, content, excerpt, author_id, status) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$title, $content, $excerpt, $currentUser['id'], $status]);
            $postId = $pdo->lastInsertId();
            
            // Handle file uploads
            if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                $uploadedFiles = [];
                
                for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                    if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['files']['name'][$i],
                            'type' => $_FILES['files']['type'][$i],
                            'tmp_name' => $_FILES['files']['tmp_name'][$i],
                            'size' => $_FILES['files']['size'][$i]
                        ];
                        
                        try {
                            $uploadedFile = $fileHandler->uploadFile($file, $postId);
                            $uploadedFiles[] = $uploadedFile;
                        } catch (Exception $e) {
                            // Continue with other files if one fails
                            $error .= "File '{$file['name']}': " . $e->getMessage() . "<br>";
                        }
                    }
                }
            }
            
            if (empty($error)) {
                $success = 'Post created successfully!';
                // Redirect after successful creation
                header("Location: view_post.php?id=$postId");
                exit;
            }
            
        } catch (Exception $e) {
            $error = 'Error creating post: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - <?php echo SITE_NAME; ?></title>
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
                    <span>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?></span>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="form-container">
                <h2>Create New Post</h2>
                
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
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
                        <textarea id="excerpt" name="excerpt" rows="3" 
                                  placeholder="Optional brief description of your post..."><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Content *</label>
                        <textarea id="content" name="content" rows="15" required 
                                  placeholder="Write your post content here..."><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="files">Attach Files</label>
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
                                <input type="radio" name="status" value="draft" checked>
                                Save as Draft
                            </label>
                            <label>
                                <input type="radio" name="status" value="published">
                                Publish Now
                            </label>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Post</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

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
    </script>
</body>
</html>
<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'models/Post.php';
require_once 'models/FileUpload.php';

$post = new Post($pdo);
$fileUpload = new FileUpload($pdo);
$currentUser = $auth->getCurrentUser();

// Require login
$auth->requireLogin();

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

// Check if user can edit this post
if (!$post->canEdit($postId, $currentUser['id'], $currentUser['role'])) {
    header('Location: index.php?error=Access denied');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Delete post
        $result = $post->delete($postId);
        if ($result['success']) {
            header('Location: index.php?success=Post deleted successfully');
            exit;
        } else {
            $error = $result['error'];
        }
    } else {
        // Update post
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $featuredImage = $postData['featured_image'];
        
        // Handle featured image upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $fileUpload->upload($_FILES['featured_image']);
            if ($uploadResult['success']) {
                // Delete old image if exists
                if ($featuredImage && file_exists($featuredImage)) {
                    unlink($featuredImage);
                }
                $featuredImage = $uploadResult['filepath'];
            } else {
                $error = $uploadResult['error'];
            }
        }
        
        if (empty($error)) {
            $result = $post->update($postId, $title, $content, $excerpt, $featuredImage);
            
            if ($result['success']) {
                // Handle additional file uploads
                if (isset($_FILES['attachments'])) {
                    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmpName) {
                        if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['attachments']['name'][$key],
                                'tmp_name' => $tmpName,
                                'size' => $_FILES['attachments']['size'][$key],
                                'type' => $_FILES['attachments']['type'][$key],
                                'error' => $_FILES['attachments']['error'][$key]
                            ];
                            $fileUpload->upload($file, $postId);
                        }
                    }
                }
                
                $success = 'Post updated successfully!';
                // Refresh post data
                $postData = $post->getById($postId);
            } else {
                $error = implode('<br>', $result['errors']);
            }
        }
    }
}

// Get post files
$files = $fileUpload->getPostFiles($postId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="form-container">
                <div class="form-header">
                    <h1>✏️ Edit Post</h1>
                    <p>Update your story</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="post-form" id="editPostForm">
                    <div class="form-group">
                        <label for="title">Post Title *</label>
                        <input type="text" id="title" name="title" required 
                               placeholder="Enter an engaging title for your post"
                               value="<?php echo htmlspecialchars($postData['title']); ?>">
                        <div class="form-error" id="title-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
                        <textarea id="excerpt" name="excerpt" rows="3" 
                                  placeholder="Brief description of your post (optional)"><?php echo htmlspecialchars($postData['excerpt']); ?></textarea>
                        <div class="form-help">This will be shown in post previews</div>
                    </div>

                    <div class="form-group">
                        <label for="content">Content *</label>
                        <textarea id="content" name="content" rows="15" required 
                                  placeholder="Write your amazing story here..."><?php echo htmlspecialchars($postData['content']); ?></textarea>
                        <div class="form-error" id="content-error"></div>
                        <div class="form-help">You can use basic HTML tags like &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;h3&gt;, &lt;ul&gt;, &lt;li&gt;</div>
                    </div>

                    <div class="form-group">
                        <label for="featured_image">Featured Image</label>
                        <?php if ($postData['featured_image']): ?>
                            <div class="current-image">
                                <img src="<?php echo htmlspecialchars($postData['featured_image']); ?>" 
                                     alt="Current featured image" style="max-width: 200px; margin-bottom: 1rem;">
                                <p>Current featured image</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="featured_image" name="featured_image" 
                               accept="image/jpeg,image/jpg,image/png,image/gif">
                        <div class="form-help">Upload a new featured image to replace the current one (JPG, PNG, GIF - max 5MB)</div>
                    </div>

                    <?php if (!empty($files)): ?>
                        <div class="form-group">
                            <label>Current Attachments</label>
                            <div class="current-files">
                                <?php foreach ($files as $file): ?>
                                    <div class="file-item">
                                        <span class="file-name"><?php echo htmlspecialchars($file['original_name']); ?></span>
                                        <button type="button" onclick="deleteFile(<?php echo $file['id']; ?>)" 
                                                class="btn btn-sm btn-danger">Delete</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="attachments">Add More Files</label>
                        <input type="file" id="attachments" name="attachments[]" multiple 
                               accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
                        <div class="form-help">Upload additional files to attach to your post</div>
                    </div>

                    <div class="form-actions">
                        <a href="view_post.php?id=<?php echo $postId; ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">💾 Update Post</button>
                        <button type="button" onclick="confirmDelete()" class="btn btn-danger">🗑️ Delete Post</button>
                    </div>
                </form>
                
                <!-- Hidden delete form -->
                <form id="deleteForm" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="delete">
                </form>
            </div>
        </div>
    </main>

    <?php include 'templates/footer.php'; ?>

    <script src="assets/js/validation.js"></script>
    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }
        
        function deleteFile(fileId) {
            if (confirm('Are you sure you want to delete this file?')) {
                fetch('api/delete_file.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ file_id: fileId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting file: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting file');
                });
            }
        }
        
        // Form validation
        document.getElementById('editPostForm').addEventListener('submit', function(e) {
            let isValid = true;
            const title = document.getElementById('title');
            const content = document.getElementById('content');
            
            clearErrors();
            
            if (title.value.trim().length < 5) {
                showError('title-error', 'Title must be at least 5 characters long');
                isValid = false;
            }
            
            if (content.value.trim().length < 50) {
                showError('content-error', 'Content must be at least 50 characters long');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
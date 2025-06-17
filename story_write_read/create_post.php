<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'models/Post.php';
require_once 'models/FileUpload.php';

// Require login
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

$post = new Post($pdo);
$fileUpload = new FileUpload($pdo);
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $featuredImage = '';
    
    // Handle featured image upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = $fileUpload->upload($_FILES['featured_image']);
        if ($uploadResult['success']) {
            $featuredImage = $uploadResult['filepath'];
        } else {
            $error = $uploadResult['error'];
        }
    }
    
    if (empty($error)) {
        $result = $post->create($title, $content, $excerpt, $currentUser['id'], $featuredImage);
        
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
                        $fileUpload->upload($file, $result['post_id']);
                    }
                }
            }
            
            header('Location: view_post.php?id=' . $result['post_id']);
            exit;
        } else {
            $error = implode('<br>', $result['errors']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="form-container">
                <div class="form-header">
                    <h1>✨ Create New Post</h1>
                    <p>Share your story with the world</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="post-form" id="createPostForm">
                    <div class="form-group">
                        <label for="title">Post Title *</label>
                        <input type="text" id="title" name="title" required 
                               placeholder="Enter an engaging title for your post"
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                        <div class="form-error" id="title-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
                        <textarea id="excerpt" name="excerpt" rows="3" 
                                  placeholder="Brief description of your post (optional)"><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                        <div class="form-help">This will be shown in post previews</div>
                    </div>

                    <div class="form-group">
                        <label for="content">Content *</label>
                        <textarea id="content" name="content" rows="15" required 
                                  placeholder="Write your amazing story here..."><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                        <div class="form-error" id="content-error"></div>
                        <div class="form-help">You can use basic HTML tags like &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;h3&gt;, &lt;ul&gt;, &lt;li&gt;</div>
                    </div>

                    <div class="form-group">
                        <label for="featured_image">Featured Image</label>
                        <input type="file" id="featured_image" name="featured_image" 
                               accept="image/jpeg,image/jpg,image/png,image/gif">
                        <div class="form-help">Upload a featured image for your post (JPG, PNG, GIF - max 5MB)</div>
                    </div>

                    <div class="form-group">
                        <label for="attachments">Additional Files</label>
                        <input type="file" id="attachments" name="attachments[]" multiple 
                               accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
                        <div class="form-help">Upload additional files to attach to your post</div>
                    </div>

                    <div class="form-actions">
                        <button type="button" onclick="history.back()" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">📝 Publish Post</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'templates/footer.php'; ?>

    <script src="assets/js/validation.js"></script>
    <script>
        // Enhanced validation for post creation
        document.getElementById('createPostForm').addEventListener('submit', function(e) {
            let isValid = true;
            const title = document.getElementById('title');
            const content = document.getElementById('content');
            
            // Clear previous errors
            clearErrors();
            
            // Validate title
            if (title.value.trim().length < 5) {
                showError('title-error', 'Title must be at least 5 characters long');
                isValid = false;
            }
            
            // Validate content
            if (content.value.trim().length < 50) {
                showError('content-error', 'Content must be at least 50 characters long');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Character counter for content
        const contentTextarea = document.getElementById('content');
        const counter = document.createElement('div');
        counter.className = 'character-counter';
        counter.style.textAlign = 'right';
        counter.style.color = '#666';
        counter.style.fontSize = '0.85rem';
        counter.style.marginTop = '0.5rem';
        contentTextarea.parentNode.insertBefore(counter, contentTextarea.nextSibling);
        
        contentTextarea.addEventListener('input', function() {
            const length = this.value.length;
            counter.textContent = `${length} characters`;
            counter.style.color = length < 50 ? '#ef4444' : '#666';
        });
        
        // Trigger initial count
        contentTextarea.dispatchEvent(new Event('input'));
    </script>
</body>
</html>
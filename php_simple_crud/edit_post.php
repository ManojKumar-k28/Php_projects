<?php
require_once 'config.php';
requireLogin();

$message = '';
$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    header('Location: dashboard.php');
    exit();
}

// Get the post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $_SESSION['user_id']]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($title) || empty($content)) {
        $message = 'Title and content are required.';
    } else {
        $imagePath = $post['image_path'];
        $pdfPath = $post['pdf_path'];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageResult = uploadFile($_FILES['image'], 'image');
            if ($imageResult['success']) {
                // Delete old image if exists
                if ($imagePath && file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $imagePath = $imageResult['path'];
            } else {
                $message = 'Image upload error: ' . $imageResult['message'];
            }
        }
        
        // Handle PDF upload
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            $pdfResult = uploadFile($_FILES['pdf'], 'pdf');
            if ($pdfResult['success']) {
                // Delete old PDF if exists
                if ($pdfPath && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
                $pdfPath = $pdfResult['path'];
            } else {
                $message = 'PDF upload error: ' . $pdfResult['message'];
            }
        }
        
        if (empty($message)) {
            $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, image_path = ?, pdf_path = ? WHERE id = ? AND user_id = ?");
            
            if ($stmt->execute([$title, $content, $imagePath, $pdfPath, $post_id, $_SESSION['user_id']])) {
                header('Location: dashboard.php?updated=1');
                exit();
            } else {
                $message = 'Error updating post. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-title">📰 Blog</h1>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <div class="form-container" style="max-width: 800px;">
                <h2 class="form-title">Edit Blog</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Blog Title</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Featured Image</label>
                        <?php if ($post['image_path']): ?>
                            <div style="margin-bottom: 1rem;">
                                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Current Image" style="max-width: 200px; height: auto; border-radius: 8px;">
                                <p style="color: #718096; font-size: 0.9rem; margin-top: 0.5rem;">Current image</p>
                            </div>
                        <?php endif; ?>
                        <div class="file-input-group">
                            <input type="file" id="image" name="image" accept="image/*" class="form-control">
                            <p style="margin-top: 0.5rem; color: #718096; font-size: 0.9rem;">
                                Leave empty to keep current image. Supported formats: JPG, PNG, GIF (Max 5MB)
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pdf">PDF Attachment</label>
                        <?php if ($post['pdf_path']): ?>
                            <div style="margin-bottom: 1rem;">
                                <a href="<?php echo htmlspecialchars($post['pdf_path']); ?>" target="_blank" class="attachment-link">
                                    📄 View Current PDF
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="file-input-group">
                            <input type="file" id="pdf" name="pdf" accept=".pdf" class="form-control">
                            <p style="margin-top: 0.5rem; color: #718096; font-size: 0.9rem;">
                                Leave empty to keep current PDF. PDF files only (Max 5MB)
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn">Update Article</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
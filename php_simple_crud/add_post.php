<?php
require_once 'config.php';
requireLogin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($title) || empty($content)) {
        $message = 'Title and content are required.';
    } else {
        $imagePath = null;
        $pdfPath = null;
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageResult = uploadFile($_FILES['image'], 'image');
            if ($imageResult['success']) {
                $imagePath = $imageResult['path'];
            } else {
                $message = 'Image upload error: ' . $imageResult['message'];
            }
        }
        
        // Handle PDF upload
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            $pdfResult = uploadFile($_FILES['pdf'], 'pdf');
            if ($pdfResult['success']) {
                $pdfPath = $pdfResult['path'];
            } else {
                $message = 'PDF upload error: ' . $pdfResult['message'];
            }
        }
        
        if (empty($message)) {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, image_path, pdf_path) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$_SESSION['user_id'], $title, $content, $imagePath, $pdfPath])) {
                header('Location: dashboard.php?success=1');
                exit();
            } else {
                $message = 'Error creating post. Please try again.';
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
    <title>Add blog </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-title">📰 Blogs</h1>
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
                <h2 class="form-title">Write New Blog</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Blog Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" class="form-control" rows="10" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Featured Image (Optional)</label>
                        <div class="file-input-group">
                            <input type="file" id="image" name="image" accept="image/*" class="form-control">
                            <p style="margin-top: 0.5rem; color: #718096; font-size: 0.9rem;">
                                Supported formats: JPG, PNG, GIF (Max 5MB)
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pdf">PDF Attachment (Optional)</label>
                        <div class="file-input-group">
                            <input type="file" id="pdf" name="pdf" accept=".pdf" class="form-control">
                            <p style="margin-top: 0.5rem; color: #718096; font-size: 0.9rem;">
                                PDF files only (Max 5MB)
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn">Publish Blog</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
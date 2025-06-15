<?php
require_once 'config.php';
requireLogin();

$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    header('Location: dashboard.php');
    exit();
}

// Get the post to verify ownership and get file paths
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $_SESSION['user_id']]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: dashboard.php');
    exit();
}

// Delete the post
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
if ($stmt->execute([$post_id, $_SESSION['user_id']])) {
    // Delete associated files
    if ($post['image_path'] && file_exists($post['image_path'])) {
        unlink($post['image_path']);
    }
    if ($post['pdf_path'] && file_exists($post['pdf_path'])) {
        unlink($post['pdf_path']);
    }
    
    header('Location: dashboard.php?deleted=1');
} else {
    header('Location: dashboard.php?error=1');
}
exit();
?>
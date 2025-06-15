<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();

// Get user's posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$userPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total posts count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$totalPosts = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Blog Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-title">📰 Blog Hub</h1>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="add_post.php" class="nav-link">Add Blog</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <div class="dashboard-header">
                <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p>Manage your blogs and create new content</p>
            </div>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalPosts; ?></div>
                    <div class="stat-label">Total Blogs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                    <div class="stat-label">Member Since</div>
                </div>
            </div>

            <div class="articles-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2>Your Blogs</h2>
                    <a href="add_post.php" class="btn">Write New Blog</a>
                </div>

                <?php if (empty($userPosts)): ?>
                    <div class="empty-state">
                        <h3>No blogs yet</h3>
                        <p>Start sharing your knowledge with the world!</p>
                        <a href="add_post.php" class="cta-button">Write Your First Blog</a>
                    </div>
                <?php else: ?>
                    <div class="articles-grid">
                        <?php foreach ($userPosts as $post): ?>
                            <article class="article-card">
                                <?php if ($post['image_path']): ?>
                                    <div class="article-image">
                                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Blog Image">
                                    </div>
                                <?php endif; ?>
                                <div class="article-content">
                                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <p class="article-excerpt"><?php echo htmlspecialchars(substr($post['content'], 0, 150)) . '...'; ?></p>
                                    <div class="article-meta">
                                        <span class="date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                        <?php if ($post['updated_at'] != $post['created_at']): ?>
                                            <span class="updated">Updated: <?php echo date('M j, Y', strtotime($post['updated_at'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                        <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">View</a>
                                        <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">Edit</a>
                                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this blog?')">Delete</a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
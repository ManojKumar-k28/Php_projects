<?php
require_once 'config.php';

// Get all posts
$stmt = $pdo->prepare("
    SELECT p.*, u.username 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-title">📰 Blog</h1>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="add_post.php" class="nav-link">Add Blog</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <div class="hero-section">
                <h1>Welcome to My Blog</h1>
                <p>Discover amazing Blogs and share your knowledge with the world</p>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="cta-button">Get Started</a>
                <?php endif; ?>
            </div>

            <div class="articles-section">
                <h2>Latest Blogs</h2>
                <div class="articles-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="article-card">
                            <?php if ($post['image_path']): ?>
                                <div class="article-image">
                                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Article Image">
                                </div>
                            <?php endif; ?>
                            <div class="article-content">
                                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                <p class="article-excerpt"><?php echo htmlspecialchars(substr($post['content'], 0, 150)) . '...'; ?></p>
                                <div class="article-meta">
                                    <span class="author">By <?php echo htmlspecialchars($post['username']); ?></span>
                                    <span class="date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                                <a href="view_post.php?id=<?php echo $post['id']; ?>" class="read-more">Read More</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <h3>No Blogs yet</h3>
                        <p>Be the first to share your knowledge!</p>
                        <?php if (isLoggedIn()): ?>
                            <a href="add_post.php" class="cta-button">Write Blogs</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024  Blog. Built with PHP & Modern Design.</p>
        </div>
    </footer>
</body>
</html>
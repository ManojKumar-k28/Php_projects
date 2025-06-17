<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';

// Get search query
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * POSTS_PER_PAGE;

// Build search query
$searchWhere = '';
$searchParams = [];

if (!empty($search)) {
    $searchWhere = "WHERE (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
    $searchTerm = "%$search%";
    $searchParams = [$searchTerm, $searchTerm, $searchTerm];
}

// Get total posts count
$countSql = "SELECT COUNT(*) FROM posts p JOIN users u ON p.author_id = u.id $searchWhere";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($searchParams);
$totalPosts = $countStmt->fetchColumn();
$totalPages = ceil($totalPosts / POSTS_PER_PAGE);

// Get posts
$sql = "
    SELECT p.*, u.username as author_name,
           (SELECT COUNT(*) FROM post_files pf WHERE pf.post_id = p.id) as file_count
    FROM posts p 
    JOIN users u ON p.author_id = u.id 
    $searchWhere
    ORDER BY p.created_at DESC 
    LIMIT ? OFFSET ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge($searchParams, [POSTS_PER_PAGE, $offset]));
$posts = $stmt->fetchAll();

$currentUser = $auth->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Share Your Stories</title>
    <link rel="stylesheet" href="assets/css/ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <main class="main">
        <div class="container">
            <!-- Hero Section -->
            <section class="hero-section">
                <div class="hero-content">
                    <h1 class="hero-title">Share Your Amazing Stories</h1>
                    <p class="hero-subtitle">Connect, inspire, and engage with a community of passionate writers and readers</p>
                </div>
            </section>

            <!-- Search Section -->
            <section class="search-section">
                <div class="search-container">
                    <form method="GET" class="search-form">
                        <input 
                            type="text" 
                            name="search" 
                            value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="🔍 Discover amazing stories..." 
                            class="search-input"
                        >
                        <button type="submit" class="search-btn">Search</button>
                    </form>
                    <?php if (!empty($search)): ?>
                        <div class="search-results">
                            <p>✨ Found <?php echo $totalPosts; ?> amazing result(s) for "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
                            <a href="index.php" class="clear-search">Clear Search</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Posts Grid -->
            <section class="posts-section">
                <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <div class="no-posts-icon">📝</div>
                        <h2>No Stories Yet</h2>
                        <p><?php echo !empty($search) ? 'Try a different search term to discover more stories.' : 'Be the first to share your amazing story with the world!'; ?></p>
                        <?php if (!$currentUser): ?>
                            <button onclick="showRegisterModal()" class="btn btn-primary">🚀 Start Your Journey</button>
                        <?php else: ?>
                            <a href="create_post.php" class="btn btn-primary">✨ Create Your First Post</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach ($posts as $post): ?>
                            <article class="post-card hover-lift" onclick="location.href='view_post.php?id=<?php echo $post['id']; ?>'">
                                <?php if ($post['featured_image']): ?>
                                    <div class="post-image">
                                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>"
                                             loading="lazy">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="post-content">
                                    <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    
                                    <div class="post-meta">
                                        <span class="author">👤 <?php echo htmlspecialchars($post['author_name']); ?></span>
                                        <span class="date">📅 <?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                        <?php if ($post['file_count'] > 0): ?>
                                            <span class="file-count">📎 <?php echo $post['file_count']; ?> files</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="post-excerpt">
                                        <?php 
                                        $excerpt = $post['excerpt'] ?: substr(strip_tags($post['content']), 0, 150);
                                        echo htmlspecialchars($excerpt);
                                        if (strlen($excerpt) >= 150) echo '...';
                                        ?>
                                    </div>
                                    
                                    <div class="post-stats">
                                        <span class="views">👁️ <?php echo number_format($post['views']); ?> views</span>
                                        <span class="read-more">Read Story →</span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(['search' => $search, 'page' => $page - 1]); ?>" 
                           class="pagination-btn">← Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?<?php echo http_build_query(['search' => $search, 'page' => $i]); ?>" 
                           class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query(['search' => $search, 'page' => $page + 1]); ?>" 
                           class="pagination-btn">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'templates/auth_modals.php'; ?>
    <?php include 'templates/footer.php'; ?>

    <script src="assets/js/validation.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
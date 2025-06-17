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
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo">
                    <a href="index.php"><?php echo SITE_NAME; ?></a>
                </h1>
                
                <nav class="nav">
                    <?php if ($currentUser): ?>
                        <a href="create_post.php" class="btn btn-primary">✨ New Post</a>
                        <div class="user-menu">
                            <span>👋 <?php echo htmlspecialchars($currentUser['username']); ?></span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="btn btn-secondary">Logout</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <button onclick="showLoginModal()" class="btn btn-primary">Login</button>
                        <button onclick="showRegisterModal()" class="btn btn-secondary">Register</button>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

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

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideLoginModal()">&times;</span>
            <h2>Welcome Back! 👋</h2>
            <?php if (isset($error) && isset($_POST['action']) && $_POST['action'] === 'login'): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="username">Username or Email:</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username or email">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <center><button type="submit" style="top:10px;"class="btn btn-primary">🚀 Login</button></center>
            </form>
            <p class="modal-footer">
                Don't have an account? 
                <a href="#" onclick="hideLoginModal(); showRegisterModal();">Create one here</a>
            </p>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideRegisterModal()">&times;</span>
            <h2>Join Our Community! ✨</h2>
            <?php if (isset($error) && isset($_POST['action']) && $_POST['action'] === 'register'): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="reg_username">Username:</label>
                    <input type="text" id="reg_username" name="username" required placeholder="Choose a unique username">
                </div>
                <div class="form-group">
                    <label for="reg_email">Email:</label>
                    <input type="email" id="reg_email" name="email" required placeholder="Enter your email address">
                </div>
                <div class="form-group">
                    <label for="reg_password">Password:</label>
                    <input type="password" id="reg_password" name="password" required minlength="6" placeholder="Create a strong password (min 6 chars)">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Confirm your password">
               <button type="submit" style="top:10px;" class="btn btn-primary">🎉 Create Account</button>
                </div>
                
            </form>
            <p class="modal-footer">
                Already have an account? 
                <a href="#" onclick="hideRegisterModal(); showLoginModal();">Login here</a>
            </p>
        </div>
    </div>

    <script>
        function showLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function hideLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showRegisterModal() {
            document.getElementById('registerModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function hideRegisterModal() {
            document.getElementById('registerModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const loginModal = document.getElementById('loginModal');
            const registerModal = document.getElementById('registerModal');
            if (event.target === loginModal) {
                hideLoginModal();
            }
            if (event.target === registerModal) {
                hideRegisterModal();
            }
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLoginModal();
                hideRegisterModal();
            }
        });

        // Show register modal if there was a registration error or success
        <?php if (isset($_POST['action']) && $_POST['action'] === 'register'): ?>
            showRegisterModal();
        <?php endif; ?>

        // Show login modal if there was a login error
        <?php if (isset($_POST['action']) && $_POST['action'] === 'login' && isset($error)): ?>
            showLoginModal();
        <?php endif; ?>

        // Smooth scroll for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add loading animation to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('loading');
                    }, 3000);
                }
            });
        });
    </script>

    <style>
        .hero-section {
            text-align: center;
            padding: 4rem 0;
            margin-bottom: 2rem;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #fff 0%, rgba(255, 255, 255, 0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            line-height: 1.6;
        }

        .no-posts-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
        }
    </style>
</body>
</html>
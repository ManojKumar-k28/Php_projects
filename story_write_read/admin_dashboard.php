<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'models/User.php';
require_once 'models/Post.php';

// Require admin access
$auth->requireAdmin();
$currentUser = $auth->getCurrentUser();

$userModel = new User($pdo);
$postModel = new Post($pdo);

// Get statistics
$userStats = $userModel->getStats();
$postStats = [
    'total_posts' => fetchCount($pdo, "SELECT COUNT(*) FROM posts"),
    'total_views' => fetchCount($pdo, "SELECT SUM(views) FROM posts"),
    'posts_today' => fetchCount($pdo, "SELECT COUNT(*) FROM posts WHERE DATE(created_at) = CURDATE()"),
    'posts_this_month' => fetchCount($pdo, "SELECT COUNT(*) FROM posts WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")
];

// Get recent posts
$recentPosts = fetchAll($pdo, "
    SELECT p.*, u.username as author_name 
    FROM posts p 
    JOIN users u ON p.author_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");

// Get recent users
$recentUsers = fetchAll($pdo, "
    SELECT id, username, email, role, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="dashboard-header">
                <h1>📊 Admin Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($currentUser['username']); ?>!</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($userStats['total_users']); ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($postStats['total_posts']); ?></div>
                        <div class="stat-label">Total Posts</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($postStats['total_views']); ?></div>
                        <div class="stat-label">Total Views</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($postStats['posts_today']); ?></div>
                        <div class="stat-label">Posts Today</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>🚀 Quick Actions</h2>
                <div class="actions-grid">
                    <a href="admin_users.php" class="action-card">
                        <div class="action-icon">👥</div>
                        <h3>Manage Users</h3>
                        <p>View and manage user accounts</p>
                    </a>
                    
                    <a href="admin_posts.php" class="action-card">
                        <div class="action-icon">📝</div>
                        <h3>Manage Posts</h3>
                        <p>Review and manage all posts</p>
                    </a>
                    
                    <a href="create_post.php" class="action-card">
                        <div class="action-icon">✨</div>
                        <h3>Create Post</h3>
                        <p>Write a new blog post</p>
                    </a>
                    
                    <a href="index.php" class="action-card">
                        <div class="action-icon">🏠</div>
                        <h3>View Site</h3>
                        <p>Visit the main website</p>
                    </a>
                </div>
            </div>

            <div class="dashboard-content">
                <!-- Recent Posts -->
                <div class="dashboard-section">
                    <h2>📝 Recent Posts</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Views</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPosts as $post): ?>
                                    <tr>
                                        <td>
                                            <a href="view_post.php?id=<?php echo $post['id']; ?>" class="post-link">
                                                <?php echo htmlspecialchars(substr($post['title'], 0, 50)); ?>
                                                <?php if (strlen($post['title']) > 50) echo '...'; ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                        <td><?php echo number_format($post['views']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                        <td>
                                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="section-footer">
                        <a href="admin_posts.php" class="btn btn-secondary">View All Posts</a>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="dashboard-section">
                    <h2>👥 Recent Users</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if ($user['id'] !== $currentUser['id']): ?>
                                                <button onclick="changeUserRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')" 
                                                        class="btn btn-sm btn-secondary">Change Role</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="section-footer">
                        <a href="admin_users.php" class="btn btn-secondary">View All Users</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'templates/footer.php'; ?>

    <script>
        function changeUserRole(userId, currentRole) {
            const roles = ['user', 'editor', 'admin'];
            const currentIndex = roles.indexOf(currentRole);
            const nextRole = roles[(currentIndex + 1) % roles.length];
            
            if (confirm(`Change user role to "${nextRole}"?`)) {
                fetch('api/change_user_role.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        user_id: userId, 
                        role: nextRole 
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error changing user role: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error changing user role');
                });
            }
        }
    </script>
</body>
</html>
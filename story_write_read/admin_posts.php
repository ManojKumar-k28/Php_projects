<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'models/Post.php';

// Require admin access
$auth->requireAdmin();
$currentUser = $auth->getCurrentUser();

$postModel = new Post($pdo);

// Get page number and search
$page = max(1, intval($_GET['page'] ?? 1));
$search = $_GET['search'] ?? '';

// Get posts
$postData = $postModel->getPosts($search, $page, 20);
$posts = $postData['posts'];
$totalPages = $postData['pages'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>📝 Manage Posts</h1>
                <p>Total: <?php echo number_format($postData['total']); ?> posts</p>
                <div class="page-actions">
                    <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                    <a href="create_post.php" class="btn btn-primary">✨ New Post</a>
                </div>
            </div>

            <!-- Search -->
            <div class="search-container">
                <form method="GET" class="search-form">
                    <input 
                        type="text" 
                        name="search" 
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="🔍 Search posts..." 
                        class="search-input"
                    >
                    <button type="submit" class="search-btn">Search</button>
                </form>
                <?php if (!empty($search)): ?>
                    <div class="search-results">
                        <p>Found <?php echo $postData['total']; ?> result(s) for "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
                        <a href="admin_posts.php" class="clear-search">Clear Search</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Views</th>
                            <th>Files</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo $post['id']; ?></td>
                                <td>
                                    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="post-link">
                                        <?php echo htmlspecialchars(substr($post['title'], 0, 50)); ?>
                                        <?php if (strlen($post['title']) > 50) echo '...'; ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                <td><?php echo number_format($post['views']); ?></td>
                                <td>
                                    <?php if ($post['file_count'] > 0): ?>
                                        📎 <?php echo $post['file_count']; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                        <?php echo date('M j, Y', strtotime($post['updated_at'])); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                                        <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <button onclick="deletePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['title'])); ?>')" 
                                                class="btn btn-sm btn-danger">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(['search' => $search, 'page' => $page - 1]); ?>" class="pagination-btn">← Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?<?php echo http_build_query(['search' => $search, 'page' => $i]); ?>" 
                           class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query(['search' => $search, 'page' => $page + 1]); ?>" class="pagination-btn">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'templates/footer.php'; ?>

    <script>
        function deletePost(postId, title) {
            if (confirm(`Are you sure you want to delete the post "${title}"? This action cannot be undone.`)) {
                fetch('api/delete_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ post_id: postId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting post: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting post');
                });
            }
        }
    </script>
</body>
</html>
<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'models/User.php';

// Require admin access
$auth->requireAdmin();
$currentUser = $auth->getCurrentUser();

$userModel = new User($pdo);

// Get page number
$page = max(1, intval($_GET['page'] ?? 1));

// Get users
$userData = $userModel->getUsers($page, 20);
$users = $userData['users'];
$totalPages = $userData['pages'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>👥 Manage Users</h1>
                <p>Total: <?php echo number_format($userData['total']); ?> users</p>
                <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Posts</th>
                            <th>Joined</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($user['post_count']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <?php echo date('M j, Y', strtotime($user['last_login'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] !== $currentUser['id']): ?>
                                        <div class="action-buttons">
                                            <button onclick="changeUserRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')" 
                                                    class="btn btn-sm btn-primary">Change Role</button>
                                            <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" 
                                                    class="btn btn-sm btn-danger">Delete</button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Current User</span>
                                    <?php endif; ?>
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
                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">← Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'templates/footer.php'; ?>

    <script>
        function changeUserRole(userId, currentRole) {
            const roles = ['user', 'editor', 'admin'];
            const roleSelect = document.createElement('select');
            roleSelect.style.padding = '8px';
            roleSelect.style.marginRight = '10px';
            
            roles.forEach(role => {
                const option = document.createElement('option');
                option.value = role;
                option.textContent = role.charAt(0).toUpperCase() + role.slice(1);
                option.selected = role === currentRole;
                roleSelect.appendChild(option);
            });
            
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed; top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.8); display: flex; align-items: center;
                justify-content: center; z-index: 1000;
            `;
            
            const content = document.createElement('div');
            content.style.cssText = `
                background: white; padding: 2rem; border-radius: 8px;
                text-align: center; min-width: 300px;
            `;
            content.innerHTML = `
                <h3>Change User Role</h3>
                <p>Select new role for this user:</p>
                <div style="margin: 1rem 0;">
                    ${roleSelect.outerHTML}
                </div>
                <button onclick="confirmRoleChange()" class="btn btn-primary">Change Role</button>
                <button onclick="closeModal()" class="btn btn-secondary">Cancel</button>
            `;
            
            modal.appendChild(content);
            document.body.appendChild(modal);
            
            window.confirmRoleChange = function() {
                const newRole = content.querySelector('select').value;
                
                fetch('api/change_user_role.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        user_id: userId, 
                        role: newRole 
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error changing user role: ' + data.error);
                    }
                    closeModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error changing user role');
                    closeModal();
                });
            };
            
            window.closeModal = function() {
                document.body.removeChild(modal);
            };
        }
        
        function deleteUser(userId, username) {
            if (confirm(`Are you sure you want to delete user "${username}"? This will transfer their posts to the admin account.`)) {
                fetch('api/delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting user: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting user');
                });
            }
        }
    </script>
</body>
</html>
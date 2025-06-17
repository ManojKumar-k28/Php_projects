<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">
                    <span class="logo-icon">📖</span>
                    <?php echo SITE_NAME; ?>
                </a>
            </div>
            
            <nav class="nav">
                <?php if ($currentUser): ?>
                    <?php if ($auth->canAccessAdmin()): ?>
                        <a href="admin_dashboard.php" class="nav-link">📊 Dashboard</a>
                    <?php endif; ?>
                    <a href="create_post.php" class="btn btn-primary">✨ New Post</a>
                    <div class="user-menu">
                        <span class="user-greeting">👋 <?php echo htmlspecialchars($currentUser['username']); ?></span>
                        <div class="user-dropdown">
                            <span class="role-badge role-<?php echo $currentUser['role']; ?>">
                                <?php echo ucfirst($currentUser['role']); ?>
                            </span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <button onclick="showLoginModal()" class="btn btn-outline">Login</button>
                    <button onclick="showRegisterModal()" class="btn btn-primary">Register</button>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
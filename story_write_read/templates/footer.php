<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>📖 <?php echo SITE_NAME; ?></h3>
                <p>A modern platform for sharing your amazing stories with the world. Connect, inspire, and engage with a community of passionate writers and readers.</p>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php">🏠 Home</a></li>
                    <?php if ($currentUser): ?>
                        <li><a href="create_post.php">✨ Write Story</a></li>
                        <?php if ($auth->canAccessAdmin()): ?>
                            <li><a href="admin_dashboard.php">📊 Dashboard</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="#" onclick="showLoginModal()">🔑 Login</a></li>
                        <li><a href="#" onclick="showRegisterModal()">🚀 Join Us</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Community</h4>
                <ul class="footer-links">
                    <li><a href="#">📝 Writing Tips</a></li>
                    <li><a href="#">💡 Story Ideas</a></li>
                    <li><a href="#">👥 Community Guidelines</a></li>
                    <li><a href="#">📧 Contact Us</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Made with ❤️ for storytellers everywhere.</p>
        </div>
    </div>
</footer>
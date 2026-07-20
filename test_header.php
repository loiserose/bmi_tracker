<?php
// Test the header
require_once 'includes/header.php';
?>

<!-- ============================================
🌿 PAGE CONTENT GOES HERE
============================================ -->
<div class="page-content" style="padding: 30px 20px; text-align: center;">
    <h2 style="color: var(--primary-green);">🌿 Header Test Page</h2>
    <p style="color: var(--text-secondary); margin-top: 10px;">
        This page tests the header and navigation.
    </p>
    
    <?php if (isLoggedIn()): ?>
        <div style="margin-top: 20px; padding: 20px; background: var(--bg-light); border-radius: var(--radius-sm);">
            <p>✅ You are <strong>logged in</strong> as: <?php echo htmlspecialchars($current_user['username'] ?? ''); ?></p>
            <p style="font-size: 14px; color: var(--text-muted);">User ID: <?php echo $current_user['id'] ?? ''; ?></p>
        </div>
    <?php else: ?>
        <div style="margin-top: 20px; padding: 20px; background: #FFF3E0; border-radius: var(--radius-sm);">
            <p>🔒 You are <strong>not logged in</strong></p>
            <p style="font-size: 14px; color: var(--text-muted);">
                <a href="<?php echo APP_URL; ?>login.php" style="color: var(--primary-green); font-weight: 600;">Login</a> or 
                <a href="<?php echo APP_URL; ?>register.php" style="color: var(--primary-green); font-weight: 600;">Register</a>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php
// Footer
require_once 'includes/footer.php';
?>
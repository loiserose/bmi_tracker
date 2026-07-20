<?php
// =============================================
// 🌿 FOOTER - Page Footer & Scripts
// =============================================
// This file is included at the bottom of every page
// It closes the main content, adds the footer,
// and includes all JavaScript files
// =============================================
?>
        </main>
        
        <!-- ============================================
        🌿 APP FOOTER
        ============================================ -->
        <footer class="app-footer">
            <div class="footer-content">
                <div class="footer-brand">
                    <span>🌿</span>
                    <span class="footer-name">Nature BMI</span>
                </div>
                <div class="footer-links">
                    <a href="<?php echo APP_URL; ?>index.php">Home</a>
                    <?php if (isLoggedIn()): ?>
                    <a href="<?php echo APP_URL; ?>history.php">History</a>
                    <a href="<?php echo APP_URL; ?>profile.php">Profile</a>
                    <?php else: ?>
                    <a href="<?php echo APP_URL; ?>login.php">Login</a>
                    <a href="<?php echo APP_URL; ?>register.php">Register</a>
                    <?php endif; ?>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
                    <p class="footer-version">v<?php echo APP_VERSION; ?></p>
                </div>
            </div>
        </footer>
        
    </div>
    <!-- End of app-container -->
    
    <!-- ============================================
    🌿 TOAST NOTIFICATION
    ============================================ -->
    <div id="toast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Success!</span>
    </div>
    
    <!-- ============================================
    🌿 JAVASCRIPT
    ============================================ -->
    <!-- Main JavaScript -->
    <script src="<?php echo APP_URL; ?>assets/js/script.js"></script>
    
    <!-- Chart.js for history page (only load if on history page) -->
    <?php if (basename($_SERVER['PHP_SELF'], '.php') === 'history'): ?>
    <script src="<?php echo APP_URL; ?>assets/js/chart.js"></script>
    <?php endif; ?>
    
    <!-- Page-specific JavaScript -->
    <?php 
    $page = basename($_SERVER['PHP_SELF'], '.php');
    if (file_exists(__DIR__ . '/../assets/js/pages/' . $page . '.js')): 
    ?>
    <script src="<?php echo APP_URL; ?>assets/js/pages/<?php echo $page; ?>.js"></script>
    <?php endif; ?>
    
</body>
</html>
<?php
// =============================================
// 🌿 FOOTER
// =============================================
?>
        </main>
        
        <footer class="app-footer">
            <div class="footer-content">
                <div class="footer-brand">
                    <span>🌿</span>
                    <span class="footer-name">Nature BMI</span>
                </div>
                <div class="footer-links">
                    <a href="<?php echo APP_URL; ?>index.php">Home</a>
                    <?php if (isLoggedIn()): ?>
                    <a href="<?php echo APP_URL; ?>history.php">History</a>
                    <a href="<?php echo APP_URL; ?>profile.php">Profile</a>
                    <?php else: ?>
                    <a href="<?php echo APP_URL; ?>login.php">Login</a>
                    <a href="<?php echo APP_URL; ?>register.php">Register</a>
                    <?php endif; ?>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
                    <p class="footer-version">v<?php echo APP_VERSION; ?></p>
                </div>
            </div>
        </footer>
        
    </div>
    
    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Success!</span>
    </div>
    
    <script src="<?php echo APP_URL; ?>assets/js/script.js"></script>
    
</body>
</html>
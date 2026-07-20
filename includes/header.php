<?php
// =============================================
// 🌿 HEADER - Navigation & Meta
// =============================================

// Require database if not already loaded
if (!function_exists('getDB')) {
    require_once __DIR__ . '/../config/database.php';
}

$current_user = getCurrentUser();
$is_logged_in = isLoggedIn();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo APP_NAME; ?> - <?php echo ucfirst($current_page); ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>
<body>
    <div class="app-container">
        
        <!-- HEADER -->
        <header class="app-header">
            <div class="header-content">
                <div class="header-top">
                    <div class="app-brand">
                        <span class="brand-icon">🌿</span>
                        <div class="brand-text">
                            <h1>Nature BMI</h1>
                            <span class="brand-tagline">find your balance · grow with health</span>
                        </div>
                    </div>
                    
                    <?php if ($is_logged_in && $current_user): ?>
                    <div class="header-user">
                        <div class="user-menu" onclick="toggleUserMenu()">
                            <span class="user-avatar" style="background: <?php echo $current_user['height_cm'] ? '#43A047' : '#4CAF50'; ?>;">
                                <?php echo strtoupper(substr($current_user['username'] ?? 'U', 0, 1)); ?>
                            </span>
                            <span class="user-name"><?php echo htmlspecialchars($current_user['username'] ?? 'User'); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        
                        <div class="user-dropdown" id="userDropdown">
                            <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                            <a href="history.php"><i class="fas fa-history"></i> History</a>
                            <a href="index.php"><i class="fas fa-calculator"></i> New Calculation</a>
                            <hr>
                            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="auth-buttons">
                        <a href="login.php" class="btn-login">Login</a>
                        <a href="register.php" class="btn-register">Sign Up</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        
        <!-- =============================================
        🌿 MAIN NAVIGATION - USING RELATIVE PATHS
        ============================================= -->
        <?php if ($is_logged_in): ?>
        <nav class="main-nav">
            <ul>
                <li class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="<?php echo $current_page === 'history' ? 'active' : ''; ?>">
                    <a href="history.php">
                        <i class="fas fa-chart-simple"></i>
                        <span>History</span>
                    </a>
                </li>
                <li class="<?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        
        <main class="main-content">
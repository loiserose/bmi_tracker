<?php
// =============================================
// ?? LOGIN PAGE
// =============================================

require_once 'config/database.php';

// Redirect already signed-in users to home
if (isLoggedIn()) {
    redirect('index.php');
}

require_once 'includes/header.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email/username and password.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND is_active = 1 LIMIT 1");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch();

            if ($user && verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                redirect('index.php');
            }

            $error = 'Invalid email/username or password. Please try again.';
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = 'An error occurred while signing in. Please try again later.';
        }
    }
}
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h1 class="page-title"><i class="fas fa-sign-in-alt"></i> Login</h1>
    <p class="page-subtitle">Sign in to access your BMI tracker and history.</p>
</div>

<div class="auth-card">
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="auth-form">
        <div class="form-group">
            <label class="form-label" for="email">Email or Username</label>
            <input
                type="text"
                id="email"
                name="email"
                class="form-control"
                value="<?php echo htmlspecialchars($email); ?>"
                placeholder="Enter your email or username"
                required
            />
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Enter your password"
                required
            />
        </div>

        <button type="submit" class="btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Login
        </button>
    </form>

    <div class="auth-footer">
        <p>Don't have an account? <a href="register.php">Register now</a></p>
    </div>
</div>

<?php require_once 'includes/footer.php';

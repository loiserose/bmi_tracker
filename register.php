<?php
// =============================================
// ?? REGISTER PAGE
// =============================================

require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect already signed-in users to home
if (isLoggedIn()) {
    redirect('index.php');
}

require_once 'includes/header.php';

$error = '';
$username = '';
$email = '';
$full_name = '';
$age = '';
$gender = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $age = sanitize($_POST['age'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match. Please try again.';
    } else {
        $usernameValidation = validateUsername($username);
        $emailValidation = validateEmail($email);
        $passwordValidation = validatePassword($password);

        if (!$usernameValidation['valid']) {
            $error = $usernameValidation['message'];
        } elseif (!$emailValidation['valid']) {
            $error = $emailValidation['message'];
        } elseif (!$passwordValidation['valid']) {
            $error = $passwordValidation['message'];
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare('SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?');
                $stmt->execute([$username, $email]);
                $existing = $stmt->fetch();

                if ($existing && $existing['count'] > 0) {
                    $error = 'That username or email is already registered. Please choose another.';
                } else {
                    $hashedPassword = hashPassword($password);
                    $stmt = $db->prepare('INSERT INTO users (username, email, password, full_name, age, gender, created_at, updated_at, is_active) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)');
                    $stmt->execute([
                        $username,
                        $email,
                        $hashedPassword,
                        $full_name ?: null,
                        $age !== '' ? intval($age) : null,
                        in_array($gender, ['male', 'female', 'other']) ? $gender : null
                    ]);

                    $_SESSION['user_id'] = $db->lastInsertId();
                    redirect('index.php');
                }
            } catch (PDOException $e) {
                error_log('Registration error: ' . $e->getMessage());
                $error = 'Unable to register right now. Please try again later.';
            }
        }
    }
}
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h1 class="page-title"><i class="fas fa-user-plus"></i> Register</h1>
    <p class="page-subtitle">Create a new account to track your BMI and health history.</p>
</div>

<div class="auth-card">
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" class="auth-form">
        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                class="form-control"
                value="<?php echo htmlspecialchars($username); ?>"
                placeholder="Choose a username"
                required
            />
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                value="<?php echo htmlspecialchars($email); ?>"
                placeholder="Enter your email"
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
                placeholder="Create a password"
                required
            />
        </div>

        <div class="form-group">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                class="form-control"
                placeholder="Repeat your password"
                required
            />
        </div>

        <div class="form-group">
            <label class="form-label" for="full_name">Full Name</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                class="form-control"
                value="<?php echo htmlspecialchars($full_name); ?>"
                placeholder="Optional"
            />
        </div>

        <div class="form-group">
            <label class="form-label" for="age">Age</label>
            <input
                type="number"
                id="age"
                name="age"
                class="form-control"
                value="<?php echo htmlspecialchars($age); ?>"
                placeholder="Optional"
                min="10"
                max="120"
            />
        </div>

        <div class="form-group">
            <label class="form-label" for="gender">Gender</label>
            <select id="gender" name="gender" class="form-control">
                <option value="" <?php echo $gender === '' ? 'selected' : ''; ?>>Not specified</option>
                <option value="male" <?php echo $gender === 'male' ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo $gender === 'female' ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo $gender === 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fas fa-user-plus"></i>
            Register
        </button>
    </form>

    <div class="auth-footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php require_once 'includes/footer.php';

<?php
// =============================================
// 🌿 PROFILE PAGE
// =============================================

require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

require_once 'includes/header.php';

$user = getCurrentUser();
if (!$user) {
    redirect('login.php');
}

$settings = getUserSettings($user['id']);
$stats = getBMIStatistics($user['id']);
$historyCount = count(getUserBMIHistory($user['id'], 1000));

$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = sanitize($_POST['gender'] ?? '');
    $height = floatval($_POST['height'] ?? 0);
    $weight = floatval($_POST['weight'] ?? 0);
    
    $updateData = [];
    
    if (!empty($full_name)) $updateData['full_name'] = $full_name;
    if ($age > 0) $updateData['age'] = $age;
    if (!empty($gender)) $updateData['gender'] = $gender;
    if ($height > 0) $updateData['height_cm'] = $height;
    if ($weight > 0) $updateData['weight_kg'] = $weight;
    
    if (!empty($updateData)) {
        if (updateUserProfile($user['id'], $updateData)) {
            $message = 'Profile updated successfully!';
            // Refresh user data
            $user = getCurrentUser();
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all password fields.';
    } elseif (!verifyPassword($current_password, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
        if (updateUserPassword($user['id'], $new_password)) {
            $message = 'Password changed successfully!';
        } else {
            $error = 'Failed to change password. Please try again.';
        }
    }
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $theme = sanitize($_POST['theme'] ?? 'light');
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    $unit = sanitize($_POST['measurement_unit'] ?? 'metric');
    $calories = intval($_POST['daily_goal_calories'] ?? 2000);
    
    $settingsData = [
        'theme' => $theme,
        'notifications' => $notifications,
        'measurement_unit' => $unit,
        'daily_goal_calories' => $calories
    ];
    
    if (updateUserSettings($user['id'], $settingsData)) {
        $message = 'Settings updated successfully!';
        $settings = getUserSettings($user['id']);
    } else {
        $error = 'Failed to update settings.';
    }
}
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-user-circle"></i> My Profile
    </h1>
    <p class="page-subtitle">Manage your account and health information</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success" style="background: #E8F5E9; color: #2E7D32; padding: 14px 18px; border-radius: 12px; margin-bottom: 16px; border-left: 4px solid #2E7D32;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error" style="background: #FFEBEE; color: #E53935; padding: 14px 18px; border-radius: 12px; margin-bottom: 16px; border-left: 4px solid #E53935;">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- Profile Stats -->
<div class="profile-stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px;">
    <div class="stat-card" style="background: var(--bg-light); padding: 16px; border-radius: var(--radius-sm); text-align: center;">
        <div class="stat-number" style="font-size: 24px; font-weight: 800; color: var(--primary-green);">
            <?php echo $historyCount; ?>
        </div>
        <div class="stat-label" style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Checks</div>
    </div>
    <div class="stat-card" style="background: var(--bg-light); padding: 16px; border-radius: var(--radius-sm); text-align: center;">
        <div class="stat-number" style="font-size: 24px; font-weight: 800; color: var(--primary-green);">
            <?php echo $stats && $stats['current'] ? $stats['current']['label'] : '—'; ?>
        </div>
        <div class="stat-label" style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Current BMI</div>
    </div>
    <div class="stat-card" style="background: var(--bg-light); padding: 16px; border-radius: var(--radius-sm); text-align: center;">
        <div class="stat-number" style="font-size: 24px; font-weight: 800; color: var(--primary-green);">
            <?php echo $stats && $stats['overall'] ? $stats['overall']['avg_bmi'] : '—'; ?>
        </div>
        <div class="stat-label" style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Average BMI</div>
    </div>
</div>

<!-- Profile Information -->
<div style="background: var(--bg-light); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px;">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-user" style="color: var(--primary-green);"></i> Profile Information
    </h3>
    
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background: #e8eee8;">
            </div>
            
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: #e8eee8;">
            </div>
            
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label class="form-label">Age</label>
                <input type="number" name="age" class="form-control" value="<?php echo $user['age'] ?? ''; ?>" placeholder="Age" min="10" max="120">
            </div>
            
            <div class="form-group">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-control">
                    <option value="">Not specified</option>
                    <option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                    <option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Height (cm)</label>
                <input type="number" name="height" class="form-control" value="<?php echo $user['height_cm'] ?? ''; ?>" placeholder="Height in cm" step="0.1">
            </div>
            
            <div class="form-group">
                <label class="form-label">Weight (kg)</label>
                <input type="number" name="weight" class="form-control" value="<?php echo $user['weight_kg'] ?? ''; ?>" placeholder="Weight in kg" step="0.1">
            </div>
        </div>
        
        <button type="submit" name="update_profile" class="btn-primary" style="margin-top: 16px;">
            <i class="fas fa-save"></i> Update Profile
        </button>
    </form>
</div>

<!-- Change Password -->
<div style="background: var(--bg-light); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px;">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-lock" style="color: var(--primary-green);"></i> Change Password
    </h3>
    
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
        </div>
        
        <button type="submit" name="change_password" class="btn-primary" style="background: linear-gradient(135deg, #FB8C00, #F57C00);">
            <i class="fas fa-key"></i> Change Password
        </button>
    </form>
</div>

<!-- Settings -->
<div style="background: var(--bg-light); border-radius: var(--radius-md); padding: 24px;">
    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-cog" style="color: var(--primary-green);"></i> Preferences
    </h3>
    
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Theme</label>
            <select name="theme" class="form-control">
                <option value="light" <?php echo ($settings['theme'] ?? 'light') === 'light' ? 'selected' : ''; ?>>Light</option>
                <option value="dark" <?php echo ($settings['theme'] ?? 'light') === 'dark' ? 'selected' : ''; ?>>Dark</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Measurement Unit</label>
            <select name="measurement_unit" class="form-control">
                <option value="metric" <?php echo ($settings['measurement_unit'] ?? 'metric') === 'metric' ? 'selected' : ''; ?>>Metric (cm/kg)</option>
                <option value="imperial" <?php echo ($settings['measurement_unit'] ?? 'metric') === 'imperial' ? 'selected' : ''; ?>>Imperial (ft/lbs)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Daily Calorie Goal</label>
            <input type="number" name="daily_goal_calories" class="form-control" value="<?php echo $settings['daily_goal_calories'] ?? 2000; ?>" min="1000" max="5000">
        </div>
        
        <div class="form-group" style="display: flex; align-items: center; gap: 12px;">
            <input type="checkbox" name="notifications" id="notifications" value="1" <?php echo ($settings['notifications'] ?? 1) ? 'checked' : ''; ?> style="width: 18px; height: 18px;">
            <label for="notifications" style="margin: 0; cursor: pointer;">Enable Email Notifications</label>
        </div>
        
        <button type="submit" name="update_settings" class="btn-primary">
            <i class="fas fa-save"></i> Save Settings
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
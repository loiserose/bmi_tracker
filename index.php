<?php
// =============================================
// 🌿 HOME PAGE - BMI CALCULATOR (FIXED)
// =============================================

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require database
require_once 'config/database.php';

// If user is not logged in, redirect to login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Require header
require_once 'includes/header.php';

// Get user data
$user = getCurrentUser();
if (!$user) {
    session_unset();
    session_destroy();
    redirect('login.php');
}

// Get latest BMI record if exists
$latest = getLatestBMI($user['id']);

// Get nutrition tips for the latest category if exists
$tips = [];
if ($latest) {
    $tips = getNutritionTips($latest['category']);
}

// Check if there's a result to display (from calculation)
$showResult = isset($_SESSION['bmi_result']) && !empty($_SESSION['bmi_result']);
$result = $showResult ? $_SESSION['bmi_result'] : null;

// Clear session result after displaying
if ($showResult) {
    unset($_SESSION['bmi_result']);
}

// Handle form submission DIRECTLY (no AJAX)
$calc_error = '';
$calc_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculate'])) {
    $height = floatval($_POST['height'] ?? 0);
    $weight = floatval($_POST['weight'] ?? 0);
    
    if ($height <= 0) {
        $calc_error = 'Please enter a valid height';
    } elseif ($weight <= 0) {
        $calc_error = 'Please enter a valid weight';
    } elseif ($height > 300) {
        $calc_error = 'Height seems too high (max 300cm)';
    } elseif ($weight > 500) {
        $calc_error = 'Weight seems too high (max 500kg)';
    } else {
        $bmiData = calculateFullBMI($height, $weight);
        if ($bmiData) {
            $calc_result = $bmiData;
            // Store in session for display after redirect
            $_SESSION['bmi_result'] = $bmiData;
            // Redirect to show results
            redirect('index.php?calculated=1');
        } else {
            $calc_error = 'BMI calculation failed';
        }
    }
}
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h1 class="page-title">
        <i class="fas fa-calculator"></i> BMI Calculator
    </h1>
    <p class="page-subtitle">
        <?php if ($user): ?>
            Welcome back, <strong><?php echo htmlspecialchars($user['username']); ?></strong>! 
            Enter your height and weight to calculate your BMI.
        <?php else: ?>
            Enter your height and weight to calculate your BMI.
        <?php endif; ?>
    </p>
</div>

<?php if ($calc_error): ?>
<div style="background: #FFEBEE; color: #E53935; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; border-left: 4px solid #E53935;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($calc_error); ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['calculated']) && $result): ?>
<div style="background: #E8F5E9; color: #2E7D32; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; border-left: 4px solid #2E7D32;">
    <i class="fas fa-check-circle"></i> BMI Calculated Successfully!
</div>
<?php endif; ?>

<!-- ============================================
🌿 INPUT SECTION
============================================ -->
<div class="input-section" id="inputSection">
    <form method="POST" action="index.php">
        
        <!-- Height Input -->
        <div class="input-group">
            <label class="input-label" for="heightInput">
                <i class="fas fa-ruler-vertical"></i> Height
            </label>
            <div class="input-wrapper">
                <input 
                    type="number" 
                    id="heightInput" 
                    name="height"
                    placeholder="Enter your height" 
                    value="<?php echo $_POST['height'] ?? $user['height_cm'] ?? ''; ?>"
                    step="0.1"
                    min="50"
                    max="300"
                    required
                />
                <span class="input-unit">cm</span>
            </div>
        </div>
        
        <!-- Weight Input -->
        <div class="input-group">
            <label class="input-label" for="weightInput">
                <i class="fas fa-weight"></i> Weight
            </label>
            <div class="input-wrapper">
                <input 
                    type="number" 
                    id="weightInput" 
                    name="weight"
                    placeholder="Enter your weight" 
                    value="<?php echo $_POST['weight'] ?? $user['weight_kg'] ?? ''; ?>"
                    step="0.1"
                    min="10"
                    max="500"
                    required
                />
                <span class="input-unit">kg</span>
            </div>
        </div>
        
        <!-- Calculate Button -->
        <button type="submit" name="calculate" class="calculate-btn" id="calculateBtn">
            <i class="fas fa-leaf"></i>
            Calculate BMI
        </button>
    </form>
</div>

<!-- ============================================
🌿 RESULTS SECTION
============================================ -->
<div class="results-section <?php echo ($result || $latest) ? 'visible' : ''; ?>" id="resultsSection">
    
    <?php if ($result): ?>
        <!-- Show calculated result -->
        <div class="bmi-card" style="border-color: <?php echo $result['color']; ?>;">
            <div class="bmi-value" style="color: <?php echo $result['color']; ?>;">
                <?php echo $result['bmi']; ?>
            </div>
            <div class="bmi-label">BMI</div>
            <div class="bmi-category" style="color: <?php echo $result['color']; ?>;">
                <?php echo $result['emoji']; ?> <?php echo $result['label']; ?>
            </div>
            
            <!-- BMI Range Bar -->
            <div class="bmi-range-bar">
                <div class="segment segment-underweight <?php echo $result['category'] === 'underweight' ? 'active' : ''; ?>"></div>
                <div class="segment segment-normal <?php echo $result['category'] === 'normal' ? 'active' : ''; ?>"></div>
                <div class="segment segment-overweight <?php echo $result['category'] === 'overweight' ? 'active' : ''; ?>"></div>
                <div class="segment segment-obese <?php echo $result['category'] === 'obese' ? 'active' : ''; ?>"></div>
            </div>
            <div class="range-labels">
                <span>Underweight</span>
                <span>Normal</span>
                <span>Overweight</span>
                <span>Obese</span>
            </div>
        </div>
        
        <!-- Nutrition Tips -->
        <div class="nutrition-section">
            <div class="nutrition-title">
                <i class="fas fa-utensils"></i>
                Nutrition Tips for <?php echo $result['label']; ?>
            </div>
            <ul class="nutrition-tips">
                <?php 
                $tips = getNutritionTips($result['category']);
                foreach ($tips as $tip): 
                ?>
                <li>
                    <span class="tip-icon"><?php echo $tip['icon'] ?? '🥗'; ?></span>
                    <span><?php echo htmlspecialchars($tip['tip']); ?></span>
                </li>
                <?php endforeach; ?>
                <?php if (empty($tips)): ?>
                <li>
                    <span class="tip-icon">📝</span>
                    <span>No nutrition tips available for this category yet.</span>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Save Button (uses PHP POST) -->
        <form method="POST" action="api/save.php" style="margin-top: 16px;">
            <input type="hidden" name="height" value="<?php echo $_POST['height'] ?? $user['height_cm'] ?? ''; ?>">
            <input type="hidden" name="weight" value="<?php echo $_POST['weight'] ?? $user['weight_kg'] ?? ''; ?>">
            <input type="hidden" name="bmi" value="<?php echo $result['bmi']; ?>">
            <input type="hidden" name="category" value="<?php echo $result['category']; ?>">
            <button type="submit" class="action-btn action-btn-save" style="width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; background: var(--primary-green); color: white; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-save"></i> Save to History
            </button>
        </form>
        
    <?php elseif ($latest && !$result): ?>
        <!-- Show latest saved result -->
        <div class="bmi-card" style="border-color: <?php echo $latest['bmi_color']; ?>;">
            <div class="bmi-value" style="color: <?php echo $latest['bmi_color']; ?>;">
                <?php echo number_format($latest['bmi'], 1); ?>
            </div>
            <div class="bmi-label">Latest BMI</div>
            <div class="bmi-category" style="color: <?php echo $latest['bmi_color']; ?>;">
                <?php echo getBMICategoryEmoji($latest['category']); ?> 
                <?php echo getBMICategoryLabel($latest['category']); ?>
            </div>
            
            <div class="bmi-range-bar">
                <div class="segment segment-underweight <?php echo $latest['category'] === 'underweight' ? 'active' : ''; ?>"></div>
                <div class="segment segment-normal <?php echo $latest['category'] === 'normal' ? 'active' : ''; ?>"></div>
                <div class="segment segment-overweight <?php echo $latest['category'] === 'overweight' ? 'active' : ''; ?>"></div>
                <div class="segment segment-obese <?php echo $latest['category'] === 'obese' ? 'active' : ''; ?>"></div>
            </div>
            <div class="range-labels">
                <span>Underweight</span>
                <span>Normal</span>
                <span>Overweight</span>
                <span>Obese</span>
            </div>
            
            <div style="margin-top: 12px; font-size: 13px; color: var(--text-muted);">
                <i class="far fa-calendar-alt"></i> 
                <?php echo date('F d, Y', strtotime($latest['created_at'])); ?>
            </div>
        </div>
        
        <!-- Nutrition Tips for Latest -->
        <?php if (!empty($tips)): ?>
        <div class="nutrition-section">
            <div class="nutrition-title">
                <i class="fas fa-utensils"></i>
                Tips for <?php echo getBMICategoryLabel($latest['category']); ?>
            </div>
            <ul class="nutrition-tips">
                <?php foreach ($tips as $tip): ?>
                <li>
                    <span class="tip-icon"><?php echo $tip['icon'] ?? '🥗'; ?></span>
                    <span><?php echo htmlspecialchars($tip['tip']); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Update Button -->
        <div class="action-buttons" style="display: flex; gap: 12px; margin-top: 16px;">
            <button class="action-btn action-btn-save" onclick="document.getElementById('heightInput').focus(); document.getElementById('inputSection').scrollIntoView({behavior: 'smooth'});" style="flex: 1; padding: 14px; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; background: var(--primary-green); color: white; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-sync-alt"></i> Update BMI
            </button>
            <a href="<?php echo APP_URL; ?>history.php" class="action-btn action-btn-share" style="flex: 1; padding: 14px; border: 2px solid var(--border-color); border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; background: var(--bg-light); color: var(--text-primary); display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; text-align: center;">
                <i class="fas fa-history"></i> View History
            </a>
        </div>
        
    <?php else: ?>
        <!-- No results yet -->
        <div class="bmi-card" style="border-color: var(--border-color); opacity: 0.7;">
            <div class="bmi-value" style="color: var(--text-muted); font-size: 32px;">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="bmi-label">Ready to Calculate</div>
            <div class="bmi-category" style="color: var(--text-muted); font-size: 16px; font-weight: 400;">
                Enter your height and weight above
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ============================================
🌿 QUICK STATS
============================================ -->
<?php 
$historyCount = count(getUserBMIHistory($user['id'], 1000));
if ($historyCount > 0): 
$stats = getBMIStatistics($user['id']);
?>
<div class="history-section" style="margin-top: 24px; padding-top: 20px; border-top: 2px solid var(--bg-light);">
    <div class="history-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-chart-simple" style="color: var(--primary-green);"></i> Quick Stats
        </h3>
        <a href="<?php echo APP_URL; ?>history.php" style="font-size: 13px; color: var(--primary-green); font-weight: 600; text-decoration: none;">
            View All <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
    <?php if ($stats && $stats['overall'] && $stats['overall']['total_records'] > 0): ?>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
        <div style="background: var(--bg-light); padding: 12px; border-radius: var(--radius-sm); text-align: center;">
            <div style="font-size: 22px; font-weight: 800; color: var(--primary-green);">
                <?php echo $stats['overall']['total_records']; ?>
            </div>
            <div style="font-size: 11px; color: var(--text-muted);">Total Checks</div>
        </div>
        <div style="background: var(--bg-light); padding: 12px; border-radius: var(--radius-sm); text-align: center;">
            <div style="font-size: 22px; font-weight: 800; color: var(--primary-green);">
                <?php echo $stats['overall']['avg_bmi'] ?? '—'; ?>
            </div>
            <div style="font-size: 11px; color: var(--text-muted);">Average BMI</div>
        </div>
        <div style="background: var(--bg-light); padding: 12px; border-radius: var(--radius-sm); text-align: center;">
            <div style="font-size: 22px; font-weight: 800; color: var(--primary-green);">
                <?php 
                $latestCategory = $stats['current']['label'] ?? '—';
                echo $latestCategory !== '—' ? getBMICategoryEmoji(strtolower($latestCategory)) . ' ' . $latestCategory : '—';
                ?>
            </div>
            <div style="font-size: 11px; color: var(--text-muted);">Current Status</div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
// Show success message if calculated
<?php if (isset($_GET['calculated']) && $result): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const results = document.getElementById('resultsSection');
        if (results) {
            results.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 500);
});
<?php endif; ?>
</script>

 <?php require_once 'includes/footer.php'; ?>
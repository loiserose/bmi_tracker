<?php
// =============================================
// 🌿 HISTORY PAGE - View All BMI Records
// =============================================

require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

require_once 'includes/header.php';

$user = getCurrentUser();
if (!$user) {
    redirect('login.php');
}

// Get all history for this user
$history = getUserBMIHistory($user['id']);
$stats = getBMIStatistics($user['id']);
$historyCount = count($history);
?>

<div class="page-header" style="margin-bottom: 20px;">
    <h1 class="page-title">
        <i class="fas fa-history"></i> BMI History
    </h1>
    <p class="page-subtitle">Track your BMI progress over time</p>
</div>

<!-- Stats Summary -->
<?php if ($stats && $stats['overall'] && $stats['overall']['total_records'] > 0): ?>
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 24px;">
    <div style="background: var(--bg-light); padding: 12px; border-radius: var(--radius-sm); text-align: center;">
        <div style="font-size: 20px; font-weight: 800; color: var(--primary-green);">
            <?php echo $stats['overall']['total_records']; ?>
        </div>
        <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Total Records</div>
    </div>
    <div style="background: var(--bg-light); padding: 12px; border-radius: var(--radius-sm); text-align: center;">
        <div style="font-size: 20px; font-weight: 800; color: var(--primary-green);">
            <?php echo $stats['overall']['avg_bmi'] ?? '—'; ?>
        </div>
        <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Average BMI</div>
    </div>
    <div style="background: var(--bg-light); padding: 12px; border-radius: var(--radius-sm); text-align: center;">
        <div style="font-size: 20px; font-weight: 800; color: var(--primary-green);">
            <?php echo $stats['overall']['min_bmi'] ?? '—'; ?>
        </div>
        <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Lowest BMI</div>
    </div>
    <div style="background: var(--bg-light); padding: 12px; border-radius: var(--radius-sm); text-align: center;">
        <div style="font-size: 20px; font-weight: 800; color: var(--primary-green);">
            <?php echo $stats['overall']['max_bmi'] ?? '—'; ?>
        </div>
        <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Highest BMI</div>
    </div>
</div>
<?php endif; ?>

<!-- BMI Chart (only show if more than 1 record) -->
<?php if ($historyCount > 1): ?>
<div class="chart-container" style="background: white; padding: 16px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 24px;">
    <div class="chart-title" style="font-size: 14px; font-weight: 600; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-chart-line" style="color: var(--primary-green);"></i> BMI Trend
    </div>
    <canvas id="bmiChart" height="200"></canvas>
</div>
<?php endif; ?>

<!-- History List -->
<div class="history-section">
    <div class="history-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-list" style="color: var(--primary-green);"></i> All Records
            <span style="font-size: 12px; font-weight: 400; color: var(--text-muted);">(<?php echo $historyCount; ?>)</span>
        </h3>
        <?php if ($historyCount > 0): ?>
        <button onclick="clearHistory()" style="background: none; border: none; color: #E53935; font-size: 12px; cursor: pointer; font-weight: 600; padding: 4px 10px; border-radius: 6px; transition: background 0.3s;" 
                onmouseover="this.style.background='#FFEBEE'" onmouseout="this.style.background='none'">
            <i class="fas fa-trash-alt"></i> Clear All
        </button>
        <?php endif; ?>
    </div>
    
    <div class="history-list" style="max-height: 400px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; padding-right: 4px;">
        
        <?php if ($historyCount === 0): ?>
            <!-- Empty State -->
            <div class="history-empty" style="text-align: center; padding: 40px 0; color: var(--text-muted);">
                <i class="fas fa-inbox" style="font-size: 32px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                <p>No BMI records found yet.<br>Start by calculating your BMI!</p>
                <a href="<?php echo APP_URL; ?>index.php" style="display: inline-block; margin-top: 12px; padding: 10px 24px; background: var(--primary-green); color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-calculator"></i> Calculate BMI
                </a>
            </div>
        <?php else: ?>
            <!-- List Records -->
            <?php foreach ($history as $record): ?>
            <div class="history-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: var(--bg-light); border-radius: var(--radius-sm); transition: all 0.3s; border-left: 4px solid <?php echo $record['bmi_color']; ?>;" 
                 onmouseover="this.style.background='white'; this.style.boxShadow='var(--shadow-sm)'" 
                 onmouseout="this.style.background='var(--bg-light)'; this.style.boxShadow='none'">
                
                <div class="h-left">
                    <div class="h-bmi" style="font-weight: 700; font-size: 16px; color: <?php echo $record['bmi_color']; ?>;">
                        BMI: <?php echo $record['bmi']; ?>
                    </div>
                    <div class="h-category" style="font-size: 12px; font-weight: 500; color: var(--text-muted);">
                        <?php echo getBMICategoryEmoji($record['category']); ?> 
                        <?php echo getBMICategoryLabel($record['category']); ?>
                        (<?php echo $record['height_cm']; ?>cm / <?php echo $record['weight_kg']; ?>kg)
                    </div>
                    <div class="h-date" style="font-size: 11px; color: var(--text-muted);">
                        <i class="far fa-calendar-alt"></i> 
                        <?php echo date('F d, Y h:i A', strtotime($record['created_at'])); ?>
                    </div>
                </div>
                
                <button onclick="deleteRecord(<?php echo $record['id']; ?>)" 
                        style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px 8px; border-radius: 6px; transition: all 0.3s;"
                        onmouseover="this.style.background='#FFEBEE'; this.style.color='#E53935'" 
                        onmouseout="this.style.background='none'; this.style.color='var(--text-muted)'">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
    </div>
</div>

<script>
// =============================================
// DELETE SINGLE RECORD
// =============================================
function deleteRecord(id) {
    if (confirm('Delete this record?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('record_id', id);
        
        fetch('api/history.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showSuccess('Record deleted successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showError('Failed to delete record');
            }
        })
        .catch(() => showError('Network error. Please try again.'));
    }
}

// =============================================
// CLEAR ALL HISTORY
// =============================================
function clearHistory() {
    if (confirm('Delete ALL BMI records? This cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'clear');
        
        fetch('api/history.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showSuccess('All records cleared');
                setTimeout(() => location.reload(), 1000);
            } else {
                showError('Failed to clear records');
            }
        })
        .catch(() => showError('Network error. Please try again.'));
    }
}

// =============================================
// BMI CHART
// =============================================
<?php if ($historyCount > 1): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('bmiChart');
    if (ctx) {
        // Get history data (oldest to newest for chart)
        const historyData = <?php echo json_encode(array_reverse($history)); ?>;
        const labels = historyData.map(r => r.full_date || r.date_only || '');
        const data = historyData.map(r => parseFloat(r.bmi));
        
        // Get category colors for points
        const colors = historyData.map(r => r.bmi_color || '#4CAF50');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'BMI',
                    data: data,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: colors,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'BMI: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: Math.max(10, Math.floor(Math.min(...data) - 2)),
                        max: Math.ceil(Math.max(...data) + 2),
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>
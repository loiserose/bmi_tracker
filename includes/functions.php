<?php
// =============================================
// 🌿 BMI FUNCTIONS - Complete Business Logic
// =============================================

require_once __DIR__ . '/../config/database.php';

// =============================================
// BMI CATEGORY FUNCTIONS
// =============================================

function getBMICategory($bmi) {
    if ($bmi < 18.5) return 'underweight';
    if ($bmi < 25.0) return 'normal';
    if ($bmi < 30.0) return 'overweight';
    return 'obese';
}

function getBMICategoryLabel($category) {
    $labels = [
        'underweight' => 'Underweight',
        'normal' => 'Normal',
        'overweight' => 'Overweight',
        'obese' => 'Obese'
    ];
    return $labels[$category] ?? 'Unknown';
}

function getBMICategoryColor($category) {
    $colors = [
        'underweight' => '#FDD835',
        'normal' => '#43A047',
        'overweight' => '#FB8C00',
        'obese' => '#E53935'
    ];
    return $colors[$category] ?? '#000000';
}

function getBMICategoryEmoji($category) {
    $emojis = [
        'underweight' => '⚡',
        'normal' => '🌟',
        'overweight' => '💪',
        'obese' => '❤️'
    ];
    return $emojis[$category] ?? '📊';
}

function getBMICategoryClass($category) {
    $classes = [
        'underweight' => 'category-underweight',
        'normal' => 'category-normal',
        'overweight' => 'category-overweight',
        'obese' => 'category-obese'
    ];
    return $classes[$category] ?? '';
}

// =============================================
// BMI CALCULATION
// =============================================

function calculateBMI($height_cm, $weight_kg) {
    if ($height_cm <= 0 || $weight_kg <= 0) {
        return null;
    }
    $height_m = $height_cm / 100;
    return round($weight_kg / ($height_m * $height_m), 2);
}

function calculateFullBMI($height_cm, $weight_kg) {
    $bmi = calculateBMI($height_cm, $weight_kg);
    if ($bmi === null) {
        return null;
    }
    
    $category = getBMICategory($bmi);
    
    return [
        'bmi' => $bmi,
        'category' => $category,
        'label' => getBMICategoryLabel($category),
        'color' => getBMICategoryColor($category),
        'emoji' => getBMICategoryEmoji($category),
        'class' => getBMICategoryClass($category)
    ];
}

// =============================================
// BMI RECORD FUNCTIONS
// =============================================

function saveBMIRecord($user_id, $height_cm, $weight_kg, $bmi, $category, $notes = null) {
    try {
        $db = getDB();
        $color = getBMICategoryColor($category);
        
        $stmt = $db->prepare("
            INSERT INTO bmi_records 
            (user_id, height_cm, weight_kg, bmi, category, bmi_color, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $user_id,
            $height_cm,
            $weight_kg,
            $bmi,
            $category,
            $color,
            $notes
        ]);
        
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("❌ Save BMI record error: " . $e->getMessage());
        return false;
    }
}

function getUserBMIHistory($user_id, $limit = null) {
    try {
        $db = getDB();
        $sql = "
            SELECT 
                id,
                height_cm,
                weight_kg,
                bmi,
                category,
                bmi_color,
                notes,
                DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as formatted_date,
                DATE_FORMAT(created_at, '%Y-%m-%d') as date_only,
                DATE_FORMAT(created_at, '%M %d, %Y') as full_date,
                created_at
            FROM bmi_records 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("❌ Get history error: " . $e->getMessage());
        return [];
    }
}

function getLatestBMI($user_id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM bmi_records 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

function getBMIStatistics($user_id) {
    try {
        $db = getDB();
        $stats = [];
        
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_records,
                ROUND(AVG(bmi), 2) as avg_bmi,
                MIN(bmi) as min_bmi,
                MAX(bmi) as max_bmi,
                ROUND(AVG(weight_kg), 2) as avg_weight,
                ROUND(AVG(height_cm), 2) as avg_height,
                MAX(created_at) as last_check
            FROM bmi_records
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $stats['overall'] = $stmt->fetch();
        
        $latest = getLatestBMI($user_id);
        $stats['current'] = $latest ? [
            'bmi' => $latest['bmi'],
            'category' => $latest['category'],
            'label' => getBMICategoryLabel($latest['category']),
            'color' => $latest['bmi_color']
        ] : null;
        
        return $stats;
    } catch (PDOException $e) {
        error_log("❌ Get statistics error: " . $e->getMessage());
        return null;
    }
}

function getNutritionTips($category) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM nutrition_tips 
            WHERE category = ? 
            ORDER BY display_order ASC
        ");
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("❌ Get tips error: " . $e->getMessage());
        return [];
    }
}

function getUserSettings($user_id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $settings = $stmt->fetch();
        
        if (!$settings) {
            $stmt = $db->prepare("
                INSERT INTO user_settings (user_id, theme, notifications, measurement_unit, daily_goal_calories) 
                VALUES (?, 'light', 1, 'metric', 2000)
            ");
            $stmt->execute([$user_id]);
            
            $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $settings = $stmt->fetch();
        }
        
        return $settings;
    } catch (PDOException $e) {
        error_log("❌ Get settings error: " . $e->getMessage());
        return null;
    }
}

function updateUserSettings($user_id, $data) {
    try {
        $db = getDB();
        
        $allowedFields = ['theme', 'notifications', 'measurement_unit', 'daily_goal_calories'];
        $updates = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $user_id;
        $sql = "UPDATE user_settings SET " . implode(', ', $updates) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("❌ Update settings error: " . $e->getMessage());
        return false;
    }
}

function deleteBMIRecord($record_id, $user_id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM bmi_records WHERE id = ? AND user_id = ?");
        return $stmt->execute([$record_id, $user_id]);
    } catch (PDOException $e) {
        error_log("❌ Delete record error: " . $e->getMessage());
        return false;
    }
}

function clearAllBMIRecords($user_id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM bmi_records WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log("❌ Clear records error: " . $e->getMessage());
        return false;
    }
}

function updateUserProfile($user_id, $data) {
    try {
        $db = getDB();
        
        $allowedFields = ['full_name', 'age', 'gender', 'height_cm', 'weight_kg', 'email'];
        $updates = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = ?";
                $params[] = sanitize($value);
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("❌ Update profile error: " . $e->getMessage());
        return false;
    }
}

function updateUserPassword($user_id, $new_password) {
    try {
        $db = getDB();
        $hashed = hashPassword($new_password);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed, $user_id]);
    } catch (PDOException $e) {
        error_log("❌ Update password error: " . $e->getMessage());
        return false;
    }
}

// =============================================
// VALIDATION FUNCTIONS
// =============================================

function validateHeight($height) {
    $height = floatval($height);
    if ($height <= 0) {
        return ['valid' => false, 'message' => 'Height must be greater than 0'];
    }
    if ($height > 300) {
        return ['valid' => false, 'message' => 'Height seems too high (max 300cm)'];
    }
    if ($height < 50) {
        return ['valid' => false, 'message' => 'Height seems too low (min 50cm)'];
    }
    return ['valid' => true];
}

function validateWeight($weight) {
    $weight = floatval($weight);
    if ($weight <= 0) {
        return ['valid' => false, 'message' => 'Weight must be greater than 0'];
    }
    if ($weight > 500) {
        return ['valid' => false, 'message' => 'Weight seems too high (max 500kg)'];
    }
    if ($weight < 10) {
        return ['valid' => false, 'message' => 'Weight seems too low (min 10kg)'];
    }
    return ['valid' => true];
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'Please enter a valid email address'];
    }
    return ['valid' => true];
}

function validateUsername($username) {
    $username = trim($username);
    if (strlen($username) < 3) {
        return ['valid' => false, 'message' => 'Username must be at least 3 characters'];
    }
    if (strlen($username) > 30) {
        return ['valid' => false, 'message' => 'Username must be less than 30 characters'];
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['valid' => false, 'message' => 'Username can only contain letters, numbers, and underscores'];
    }
    return ['valid' => true];
}

function validatePassword($password) {
    if (strlen($password) < 6) {
        return ['valid' => false, 'message' => 'Password must be at least 6 characters'];
    }
    return ['valid' => true];
}
?>
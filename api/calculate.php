<?php
// =============================================
// 🌿 BMI CALCULATION API
// =============================================
// This file handles BMI calculation via AJAX
// =============================================

// Require necessary files
require_once '../config/database.php';
require_once '../includes/functions.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get user data
$user = getCurrentUser();
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Get form data
$height = isset($_POST['height']) ? floatval($_POST['height']) : 0;
$weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;

// Validate inputs
if ($height <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid height']);
    exit();
}

if ($weight <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid weight']);
    exit();
}

if ($height > 300) {
    echo json_encode(['success' => false, 'message' => 'Height seems too high (max 300cm)']);
    exit();
}

if ($weight > 500) {
    echo json_encode(['success' => false, 'message' => 'Weight seems too high (max 500kg)']);
    exit();
}

// Calculate BMI
$bmiData = calculateFullBMI($height, $weight);

if (!$bmiData) {
    echo json_encode(['success' => false, 'message' => 'BMI calculation failed']);
    exit();
}

// Store result in session for display on index page
$_SESSION['bmi_result'] = $bmiData;

// Also update user's stored height/weight for convenience
try {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET height_cm = ?, weight_kg = ? WHERE id = ?");
    $stmt->execute([$height, $weight, $user['id']]);
} catch (PDOException $e) {
    // Not critical, just log it
    error_log("Failed to update user height/weight: " . $e->getMessage());
}

// Return success response
echo json_encode([
    'success' => true,
    'data' => $bmiData,
    'message' => 'BMI calculated successfully'
]);
?>
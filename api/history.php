<?php
// =============================================
// 🌿 BMI HISTORY API - SIMPLE VERSION
// =============================================

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require database config
require_once '../config/database.php';

// Set headers
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user = getCurrentUser();
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// =============================================
// GET THE ACTION FROM POST OR GET
// =============================================
$action = '';

// Check POST
if (isset($_POST['action']) && !empty($_POST['action'])) {
    $action = $_POST['action'];
}

// Check GET
if (empty($action) && isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
}

// If still empty, return error
if (empty($action)) {
    echo json_encode([
        'success' => false, 
        'message' => 'No action provided. Please specify delete, clear, save, or get'
    ]);
    exit();
}

// =============================================
// DELETE RECORD
// =============================================
if ($action === 'delete') {
    $recordId = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;
    
    if (!$recordId) {
        echo json_encode(['success' => false, 'message' => 'Record ID required']);
        exit();
    }
    
    $deleted = deleteBMIRecord($recordId, $user['id']);
    
    if ($deleted) {
        echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete record']);
    }
    exit();
}

// =============================================
// CLEAR ALL RECORDS
// =============================================
if ($action === 'clear') {
    $cleared = clearAllBMIRecords($user['id']);
    
    if ($cleared) {
        echo json_encode(['success' => true, 'message' => 'All records cleared']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to clear records']);
    }
    exit();
}

// =============================================
// SAVE RECORD
// =============================================
if ($action === 'save') {
    $height = isset($_POST['height']) ? floatval($_POST['height']) : 0;
    $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0;
    $bmi = isset($_POST['bmi']) ? floatval($_POST['bmi']) : 0;
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    
    if ($height <= 0 || $weight <= 0 || $bmi <= 0 || empty($category)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit();
    }
    
    $recordId = saveBMIRecord($user['id'], $height, $weight, $bmi, $category);
    
    if ($recordId) {
        echo json_encode(['success' => true, 'message' => 'BMI record saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save record']);
    }
    exit();
}

// =============================================
// GET HISTORY
// =============================================
if ($action === 'get') {
    $records = getUserBMIHistory($user['id']);
    
    echo json_encode([
        'success' => true,
        'data' => $records,
        'count' => count($records)
    ]);
    exit();
}

// =============================================
// UNKNOWN ACTION
// =============================================
echo json_encode([
    'success' => false, 
    'message' => 'Unknown action: "' . $action . '". Use delete, clear, save, or get'
]);
?>
<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';
require_once '../auth.php';
require_once '../models/User.php';

// Check if user is admin
if (!$auth->canAccessAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['user_id']) || !isset($input['role'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $userId = intval($input['user_id']);
    $role = trim($input['role']);
    $currentUser = $auth->getCurrentUser();
    
    // Prevent user from changing their own role
    if ($userId === $currentUser['id']) {
        echo json_encode(['success' => false, 'error' => 'Cannot change your own role']);
        exit;
    }
    
    // Update user role
    $userModel = new User($pdo);
    $result = $userModel->updateRole($userId, $role);
    
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => 'User role updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
    
} catch (Exception $e) {
    error_log("Change user role error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>
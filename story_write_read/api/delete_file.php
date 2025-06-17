<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';
require_once '../auth.php';
require_once '../models/FileUpload.php';
require_once '../models/Post.php';

// Require login
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['file_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing file ID']);
        exit;
    }
    
    $fileId = intval($input['file_id']);
    
    // Get file info
    $fileUpload = new FileUpload($pdo);
    $file = fetchOne($pdo, "SELECT pf.*, p.author_id FROM post_files pf JOIN posts p ON pf.post_id = p.id WHERE pf.id = ?", [$fileId]);
    
    if (!$file) {
        echo json_encode(['success' => false, 'error' => 'File not found']);
        exit;
    }
    
    // Check if user can delete this file
    $postModel = new Post($pdo);
    if (!$postModel->canEdit($file['post_id'], $currentUser['id'], $currentUser['role'])) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }
    
    // Delete file
    $result = $fileUpload->delete($fileId);
    
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
    
} catch (Exception $e) {
    error_log("Delete file error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>
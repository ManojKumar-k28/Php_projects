<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../db.php';
require_once '../auth.php';
require_once '../models/Post.php';

// Check if user has admin access
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
    
    if (!$input || !isset($input['post_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing post ID']);
        exit;
    }
    
    $postId = intval($input['post_id']);
    $currentUser = $auth->getCurrentUser();
    
    // Delete post
    $postModel = new Post($pdo);
    
    // Check if post exists and user can delete it
    $post = $postModel->getById($postId);
    if (!$post) {
        echo json_encode(['success' => false, 'error' => 'Post not found']);
        exit;
    }
    
    // Check permissions
    if (!$postModel->canEdit($postId, $currentUser['id'], $currentUser['role'])) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }
    
    $result = $postModel->delete($postId);
    
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
    
} catch (Exception $e) {
    error_log("Delete post error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>
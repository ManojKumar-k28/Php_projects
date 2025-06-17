<?php
require_once 'config.php';
require_once 'db.php';

class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all users with pagination
     */
    public function getUsers($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $totalUsers = fetchCount($this->pdo, "SELECT COUNT(*) FROM users");
        
        // Get users
        $sql = "SELECT id, username, email, role, created_at, last_login,
                       (SELECT COUNT(*) FROM posts WHERE author_id = users.id) as post_count
                FROM users 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $users = fetchAll($this->pdo, $sql, [$limit, $offset]);
        
        return [
            'users' => $users,
            'total' => $totalUsers,
            'pages' => ceil($totalUsers / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        $sql = "SELECT id, username, email, role, created_at, last_login FROM users WHERE id = ?";
        return fetchOne($this->pdo, $sql, [$id]);
    }
    
    /**
     * Update user role
     */
    public function updateRole($id, $role) {
        $allowedRoles = ['user', 'editor', 'admin'];
        if (!in_array($role, $allowedRoles)) {
            return ['success' => false, 'error' => 'Invalid role'];
        }
        
        try {
            $sql = "UPDATE users SET role = ? WHERE id = ?";
            executeQuery($this->pdo, $sql, [$role, $id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update user role'];
        }
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        try {
            // First, transfer or delete posts
            $sql = "UPDATE posts SET author_id = 1 WHERE author_id = ?"; // Transfer to admin
            executeQuery($this->pdo, $sql, [$id]);
            
            // Delete user
            $sql = "DELETE FROM users WHERE id = ?";
            executeQuery($this->pdo, $sql, [$id]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete user'];
        }
    }
    
    /**
     * Get user statistics
     */
    public function getStats() {
        $stats = [];
        
        $stats['total_users'] = fetchCount($this->pdo, "SELECT COUNT(*) FROM users");
        $stats['admin_count'] = fetchCount($this->pdo, "SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stats['editor_count'] = fetchCount($this->pdo, "SELECT COUNT(*) FROM users WHERE role = 'editor'");
        $stats['user_count'] = fetchCount($this->pdo, "SELECT COUNT(*) FROM users WHERE role = 'user'");
        
        return $stats;
    }
}
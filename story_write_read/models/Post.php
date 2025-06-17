<?php
require_once 'config.php';
require_once 'db.php';

class Post {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new post
     */
    public function create($title, $content, $excerpt, $authorId, $featuredImage = null) {
        // Validate input
        $errors = $this->validatePost($title, $content);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Sanitize content
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $excerpt = htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8');
        $content = $this->sanitizeContent($content);
        
        try {
            $sql = "INSERT INTO posts (title, content, excerpt, author_id, featured_image, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            executeQuery($this->pdo, $sql, [$title, $content, $excerpt, $authorId, $featuredImage]);
            
            $postId = $this->pdo->lastInsertId();
            return ['success' => true, 'post_id' => $postId];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Failed to create post. Please try again.']];
        }
    }
    
    /**
     * Update a post
     */
    public function update($id, $title, $content, $excerpt, $featuredImage = null) {
        // Validate input
        $errors = $this->validatePost($title, $content);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Sanitize content
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $excerpt = htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8');
        $content = $this->sanitizeContent($content);
        
        try {
            $sql = "UPDATE posts SET title = ?, content = ?, excerpt = ?, featured_image = ?, updated_at = NOW() WHERE id = ?";
            executeQuery($this->pdo, $sql, [$title, $content, $excerpt, $featuredImage, $id]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Failed to update post. Please try again.']];
        }
    }
    
    /**
     * Delete a post
     */
    public function delete($id) {
        try {
            // Delete related files first
            $sql = "DELETE FROM post_files WHERE post_id = ?";
            executeQuery($this->pdo, $sql, [$id]);
            
            // Delete the post
            $sql = "DELETE FROM posts WHERE id = ?";
            executeQuery($this->pdo, $sql, [$id]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete post'];
        }
    }
    
    /**
     * Get post by ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, u.username as author_name 
                FROM posts p 
                JOIN users u ON p.author_id = u.id 
                WHERE p.id = ?";
        return fetchOne($this->pdo, $sql, [$id]);
    }
    
    /**
     * Get posts with pagination and search
     */
    public function getPosts($search = '', $page = 1, $limit = POSTS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        // Build search condition
        $searchWhere = '';
        $searchParams = [];
        
        if (!empty($search)) {
            $searchWhere = "WHERE (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
            $searchTerm = "%$search%";
            $searchParams = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM posts p JOIN users u ON p.author_id = u.id $searchWhere";
        $totalPosts = fetchCount($this->pdo, $countSql, $searchParams);
        
        // Get posts
        $sql = "SELECT p.*, u.username as author_name,
                       (SELECT COUNT(*) FROM post_files pf WHERE pf.post_id = p.id) as file_count
                FROM posts p 
                JOIN users u ON p.author_id = u.id 
                $searchWhere
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $posts = fetchAll($this->pdo, $sql, array_merge($searchParams, [$limit, $offset]));
        
        return [
            'posts' => $posts,
            'total' => $totalPosts,
            'pages' => ceil($totalPosts / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Increment post views
     */
    public function incrementViews($id) {
        $sql = "UPDATE posts SET views = views + 1 WHERE id = ?";
        executeQuery($this->pdo, $sql, [$id]);
    }
    
    /**
     * Check if user can edit post
     */
    public function canEdit($postId, $userId, $userRole) {
        if ($userRole === 'admin') {
            return true;
        }
        
        if ($userRole === 'editor') {
            return true;
        }
        
        // Users can only edit their own posts
        $post = $this->getById($postId);
        return $post && $post['author_id'] == $userId;
    }
    
    /**
     * Validate post data
     */
    private function validatePost($title, $content) {
        $errors = [];
        
        if (empty(trim($title))) {
            $errors[] = 'Title is required';
        } elseif (strlen($title) > 255) {
            $errors[] = 'Title must be less than 255 characters';
        }
        
        if (empty(trim($content))) {
            $errors[] = 'Content is required';
        }
        
        return $errors;
    }
    
    /**
     * Sanitize HTML content
     */
    private function sanitizeContent($content) {
        // Allow basic HTML tags
        $allowedTags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote>';
        return strip_tags($content, $allowedTags);
    }
}
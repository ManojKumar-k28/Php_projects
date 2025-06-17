<?php
require_once 'config.php';
require_once 'db.php';

class FileUpload {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Upload file
     */
    public function upload($file, $postId = null) {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = UPLOAD_DIR . $filename;
        
        // Create upload directory if it doesn't exist
        if (!file_exists(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'error' => 'Failed to upload file'];
        }
        
        // Save file info to database
        try {
            $sql = "INSERT INTO post_files (post_id, filename, original_name, file_path, file_size, file_type, uploaded_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            executeQuery($this->pdo, $sql, [
                $postId,
                $filename,
                $file['name'],
                $filepath,
                $file['size'],
                $file['type']
            ]);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => SITE_URL . '/' . $filepath
            ];
        } catch (Exception $e) {
            // Delete uploaded file if database insert fails
            unlink($filepath);
            return ['success' => false, 'error' => 'Failed to save file information'];
        }
    }
    
    /**
     * Get files for a post
     */
    public function getPostFiles($postId) {
        $sql = "SELECT * FROM post_files WHERE post_id = ? ORDER BY uploaded_at DESC";
        return fetchAll($this->pdo, $sql, [$postId]);
    }
    
    /**
     * Delete file
     */
    public function delete($fileId) {
        // Get file info
        $file = fetchOne($this->pdo, "SELECT * FROM post_files WHERE id = ?", [$fileId]);
        if (!$file) {
            return ['success' => false, 'error' => 'File not found'];
        }
        
        try {
            // Delete from database
            executeQuery($this->pdo, "DELETE FROM post_files WHERE id = ?", [$fileId]);
            
            // Delete physical file
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete file'];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload error: ' . $this->getUploadError($file['error'])];
        }
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'error' => 'File size exceeds maximum allowed size of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_FILE_TYPES)) {
            return ['success' => false, 'error' => 'File type not allowed. Allowed types: ' . implode(', ', ALLOWED_FILE_TYPES)];
        }
        
        // Additional security checks
        if (!$this->isSecureFile($file)) {
            return ['success' => false, 'error' => 'File failed security validation'];
        }
        
        return ['success' => true];
    }
    
    /**
     * Security check for uploaded files
     */
    private function isSecureFile($file) {
        // Check MIME type
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain'
        ];
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $expectedMime = $allowedMimes[$extension] ?? null;
        
        if ($expectedMime && $file['type'] !== $expectedMime) {
            // For images, also check with getimagesize
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $imageInfo = getimagesize($file['tmp_name']);
                return $imageInfo !== false;
            }
            return false;
        }
        
        return true;
    }
    
    /**
     * Get upload error message
     */
    private function getUploadError($code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        return $errors[$code] ?? 'Unknown error';
    }
}
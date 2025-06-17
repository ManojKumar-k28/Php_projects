<?php
require_once 'config.php';
require_once 'db.php';

class FileHandler {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function uploadFile($file, $postId) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('No file uploaded or upload error occurred.');
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('File size exceeds maximum allowed size of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.');
        }
        
        $originalName = $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mimeType = $file['type'];
        
        // Determine file type and validate
        if (in_array($extension, ALLOWED_IMAGE_TYPES)) {
            $fileType = 'image';
            $uploadDir = IMAGE_DIR;
        } elseif (in_array($extension, ALLOWED_DOCUMENT_TYPES)) {
            $fileType = 'document';
            $uploadDir = DOCUMENT_DIR;
        } else {
            throw new Exception('File type not allowed. Allowed types: ' . 
                implode(', ', array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOCUMENT_TYPES)));
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file.');
        }
        
        // Save file info to database
        $stmt = $this->pdo->prepare("
            INSERT INTO post_files (post_id, filename, original_name, file_type, file_size, mime_type, file_path)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $postId,
            $filename,
            $originalName,
            $fileType,
            $file['size'],
            $mimeType,
            $filePath
        ]);
        
        return [
            'id' => $this->pdo->lastInsertId(),
            'filename' => $filename,
            'original_name' => $originalName,
            'file_type' => $fileType,
            'file_path' => $filePath
        ];
    }
    
    public function deleteFile($fileId) {
        $stmt = $this->pdo->prepare("SELECT file_path FROM post_files WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();
        
        if ($file && file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM post_files WHERE id = ?");
        return $stmt->execute([$fileId]);
    }
    
    public function getPostFiles($postId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM post_files 
            WHERE post_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }
    
    public function getFileById($fileId) {
        $stmt = $this->pdo->prepare("SELECT * FROM post_files WHERE id = ?");
        $stmt->execute([$fileId]);
        return $stmt->fetch();
    }
    
    public static function formatFileSize($size) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
    
    public static function getFileIcon($fileType, $extension) {
        if ($fileType === 'image') {
            return '🖼️';
        }
        
        switch ($extension) {
            case 'pdf':
                return '📄';
            case 'doc':
            case 'docx':
                return '📝';
            case 'txt':
                return '📋';
            default:
                return '📁';
        }
    }
}
?>
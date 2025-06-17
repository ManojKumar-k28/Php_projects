<?php
require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';

// Get file ID
$fileId = intval($_GET['id'] ?? 0);
if (!$fileId) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found');
}

// Get file info
$sql = "SELECT pf.*, p.title as post_title, p.id as post_id 
        FROM post_files pf 
        JOIN posts p ON pf.post_id = p.id 
        WHERE pf.id = ?";
$file = fetchOne($pdo, $sql, [$fileId]);

if (!$file) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found');
}

// Check if file exists
if (!file_exists($file['file_path'])) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found on server');
}

// Get file extension
$extension = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));

// Handle different file types
if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
    // Display image
    $currentUser = $auth->getCurrentUser();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($file['original_name']); ?> - <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="assets/css/ui.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>
            .file-viewer {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
            }
            .file-header {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 1rem;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: var(--shadow-lg);
            }
            .file-content {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 1rem;
                padding: 2rem;
                box-shadow: var(--shadow-lg);
                text-align: center;
            }
            .file-image {
                max-width: 100%;
                height: auto;
                border-radius: 0.5rem;
                box-shadow: var(--shadow-md);
            }
            .file-meta {
                display: flex;
                gap: 2rem;
                margin-top: 1rem;
                flex-wrap: wrap;
                justify-content: center;
                color: var(--gray-600);
            }
            .file-actions {
                margin-top: 2rem;
                display: flex;
                gap: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }
        </style>
    </head>
    <body>
        <?php include 'templates/header.php'; ?>
        
        <main class="main">
            <div class="container">
                <div class="file-viewer">
                    <div class="file-header">
                        <h1><?php echo htmlspecialchars($file['original_name']); ?></h1>
                        <p>From post: <a href="view_post.php?id=<?php echo $file['post_id']; ?>" class="post-link"><?php echo htmlspecialchars($file['post_title']); ?></a></p>
                        
                        <div class="file-meta">
                            <span>📁 <?php echo formatFileSize($file['file_size']); ?></span>
                            <span>📅 <?php echo date('M j, Y', strtotime($file['uploaded_at'])); ?></span>
                            <span>🖼️ Image</span>
                        </div>
                    </div>
                    
                    <div class="file-content">
                        <img src="<?php echo htmlspecialchars($file['file_path']); ?>" 
                             alt="<?php echo htmlspecialchars($file['original_name']); ?>" 
                             class="file-image">
                        
                        <div class="file-actions">
                            <a href="<?php echo htmlspecialchars($file['file_path']); ?>" 
                               download="<?php echo htmlspecialchars($file['original_name']); ?>" 
                               class="btn btn-primary">📥 Download</a>
                            <a href="view_post.php?id=<?php echo $file['post_id']; ?>" 
                               class="btn btn-secondary">← Back to Post</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include 'templates/footer.php'; ?>
    </body>
    </html>
    <?php
} elseif ($extension === 'pdf') {
    // Display PDF viewer
    $currentUser = $auth->getCurrentUser();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($file['original_name']); ?> - <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="assets/css/ui.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>
            .file-viewer {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
            }
            .file-header {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 1rem;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: var(--shadow-lg);
            }
            .pdf-container {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 1rem;
                padding: 2rem;
                box-shadow: var(--shadow-lg);
            }
            .pdf-viewer {
                width: 100%;
                height: 80vh;
                border: none;
                border-radius: 0.5rem;
                box-shadow: var(--shadow-md);
            }
            .file-meta {
                display: flex;
                gap: 2rem;
                margin-top: 1rem;
                flex-wrap: wrap;
                color: var(--gray-600);
            }
            .file-actions {
                margin-top: 2rem;
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
            }
            .pdf-fallback {
                text-align: center;
                padding: 3rem;
                background: var(--gray-50);
                border-radius: 0.5rem;
                margin-top: 2rem;
            }
        </style>
    </head>
    <body>
        <?php include 'templates/header.php'; ?>
        
        <main class="main">
            <div class="container">
                <div class="file-viewer">
                    <div class="file-header">
                        <h1>📄 <?php echo htmlspecialchars($file['original_name']); ?></h1>
                        <p>From post: <a href="view_post.php?id=<?php echo $file['post_id']; ?>" class="post-link"><?php echo htmlspecialchars($file['post_title']); ?></a></p>
                        
                        <div class="file-meta">
                            <span>📁 <?php echo formatFileSize($file['file_size']); ?></span>
                            <span>📅 <?php echo date('M j, Y', strtotime($file['uploaded_at'])); ?></span>
                            <span>📄 PDF Document</span>
                        </div>
                        
                        <div class="file-actions">
                            <a href="<?php echo htmlspecialchars($file['file_path']); ?>" 
                               target="_blank" 
                               class="btn btn-primary">🔍 Open in New Tab</a>
                            <a href="<?php echo htmlspecialchars($file['file_path']); ?>" 
                               download="<?php echo htmlspecialchars($file['original_name']); ?>" 
                               class="btn btn-secondary">📥 Download</a>
                            <a href="view_post.php?id=<?php echo $file['post_id']; ?>" 
                               class="btn btn-outline">← Back to Post</a>
                        </div>
                    </div>
                    
                    <div class="pdf-container">
                        <iframe src="<?php echo htmlspecialchars($file['file_path']); ?>" 
                                class="pdf-viewer"
                                title="<?php echo htmlspecialchars($file['original_name']); ?>">
                        </iframe>
                        
                        <div class="pdf-fallback">
                            <h3>Can't view the PDF?</h3>
                            <p>Your browser doesn't support PDF viewing. Please download the file or open it in a new tab.</p>
                            <div style="margin-top: 1rem;">
                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" 
                                   target="_blank" 
                                   class="btn btn-primary">Open PDF</a>
                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" 
                                   download="<?php echo htmlspecialchars($file['original_name']); ?>" 
                                   class="btn btn-secondary">Download PDF</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include 'templates/footer.php'; ?>
        
        <script>
            // Check if PDF loaded successfully
            const iframe = document.querySelector('.pdf-viewer');
            const fallback = document.querySelector('.pdf-fallback');
            
            iframe.addEventListener('load', function() {
                try {
                    // Try to access iframe content to see if PDF loaded
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    if (iframeDoc.body && iframeDoc.body.innerHTML.trim() === '') {
                        fallback.style.display = 'block';
                    }
                } catch (e) {
                    // Cross-origin or other error, assume PDF loaded
                    fallback.style.display = 'none';
                }
            });
            
            iframe.addEventListener('error', function() {
                fallback.style.display = 'block';
            });
        </script>
    </body>
    </html>
    <?php
} elseif (in_array($extension, ['txt', 'md'])) {
    // Display text file
    $currentUser = $auth->getCurrentUser();
    $content = file_get_contents($file['file_path']);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($file['original_name']); ?> - <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="assets/css/ui.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>
            .file-viewer {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
            }
            .file-header {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 1rem;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: var(--shadow-lg);
            }
            .text-content {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 1rem;
                padding: 2rem;
                box-shadow: var(--shadow-lg);
            }
            .text-display {
                background: var(--gray-50);
                border: 1px solid var(--gray-200);
                border-radius: 0.5rem;
                padding: 2rem;
                font-family: 'Courier New', monospace;
                white-space: pre-wrap;
                word-wrap: break-word;
                max-height: 70vh;
                overflow-y: auto;
                line-height: 1.6;
            }
            .file-meta {
                display: flex;
                gap: 2rem;
                margin-top: 1rem;
                flex-wrap: wrap;
                color: var(--gray-600);
            }
            .file-actions {
                margin-top: 2rem;
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
            }
        </style>
    </head>
    <body>
        <?php include 'templates/header.php'; ?>
        
        <main class="main">
            <div class="container">
                <div class="file-viewer">
                    <div class="file-header">
                        <h1>📝 <?php echo htmlspecialchars($file['original_name']); ?></h1>
                        <p>From post: <a href="view_post.php?id=<?php echo $file['post_id']; ?>" class="post-link"><?php echo htmlspecialchars($file['post_title']); ?></a></p>
                        
                        <div class="file-meta">
                            <span>📁 <?php echo formatFileSize($file['file_size']); ?></span>
                            <span>📅 <?php echo date('M j, Y', strtotime($file['uploaded_at'])); ?></span>
                            <span>📝 Text File</span>
                        </div>
                        
                        <div class="file-actions">
                            <a href="<?php echo htmlspecialchars($file['file_path']); ?>" 
                               download="<?php echo htmlspecialchars($file['original_name']); ?>" 
                               class="btn btn-primary">📥 Download</a>
                            <a href="view_post.php?id=<?php echo $file['post_id']; ?>" 
                               class="btn btn-secondary">← Back to Post</a>
                        </div>
                    </div>
                    
                    <div class="text-content">
                        <div class="text-display"><?php echo htmlspecialchars($content); ?></div>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include 'templates/footer.php'; ?>
    </body>
    </html>
    <?php
} else {
    // For other file types, force download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    header('Content-Length: ' . $file['file_size']);
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    readfile($file['file_path']);
    exit;
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 1) . ' ' . $units[$pow];
}
?>
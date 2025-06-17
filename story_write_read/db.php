<?php
require_once 'config.php';

try {
    // First, connect without specifying database to create it if needed
    $pdo_init = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Create database if it doesn't exist
    $pdo_init->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo_init = null;
    
    // Now connect to the specific database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Create tables if they don't exist
    createTables($pdo);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Create database tables and insert sample data
 */
function createTables($pdo) {
    // Users table with role-based access control
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'editor', 'user') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Posts table with full-text search capability
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            excerpt TEXT,
            author_id INT NOT NULL,
            featured_image VARCHAR(255),
            views INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_author (author_id),
            INDEX idx_created_at (created_at),
            INDEX idx_title (title),
            FULLTEXT idx_search (title, content, excerpt)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Post files table for file attachments
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS post_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size INT NOT NULL,
            file_type VARCHAR(100) NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            INDEX idx_post_id (post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Check if we need to insert sample data
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount == 0) {
        insertSampleData($pdo);
    }
}

/**
 * Insert comprehensive sample data
 */
function insertSampleData($pdo) {
    // Create uploads directory if it doesn't exist
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    // Sample users (password: password123)
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    $users = [
        ['admin', 'admin@example.com', $hashedPassword, 'admin'],
        ['editor', 'editor@example.com', $hashedPassword, 'editor'],
        ['john_doe', 'john@example.com', $hashedPassword, 'user'],
        ['jane_smith', 'jane@example.com', $hashedPassword, 'user'],
        ['writer_bob', 'bob@example.com', $hashedPassword, 'user'],
        ['sarah_creative', 'sarah@example.com', $hashedPassword, 'user'],
        ['mike_storyteller', 'mike@example.com', $hashedPassword, 'user'],
        ['emma_writer', 'emma@example.com', $hashedPassword, 'user']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute($user);
    }
    
    // Sample posts with rich content
    $posts = [
        [
            'Welcome to StoryCraft - Your Creative Writing Platform',
            '<p>Welcome to <strong>StoryCraft</strong>, the premier destination for writers and storytellers of all levels! Whether you\'re a seasoned author or just beginning your creative journey, our platform provides everything you need to craft, share, and discover amazing stories.</p>
             
             <h3>🌟 What Makes StoryCraft Special?</h3>
             <p>StoryCraft isn\'t just another blogging platform - it\'s a comprehensive creative writing ecosystem designed with writers in mind:</p>
             
             <ul>
               <li><strong>🎨 Beautiful, Distraction-Free Interface</strong> - Focus on your writing with our clean, modern design</li>
               <li><strong>📝 Rich Text Editor</strong> - Format your posts with ease using our intuitive editor</li>
               <li><strong>📎 File Attachments</strong> - Share images, documents, and other files with your stories</li>
               <li><strong>🔍 Powerful Search</strong> - Discover amazing content with our advanced search functionality</li>
               <li><strong>👥 Community Features</strong> - Connect with other writers and build your audience</li>
               <li><strong>🛡️ Secure Platform</strong> - Your content is protected with enterprise-level security</li>
             </ul>
             
             <h3>✨ Getting Started</h3>
             <p>Ready to begin your storytelling journey? Here\'s how to get started:</p>
             <ol>
               <li><strong>Create Your Account</strong> - Sign up for free and join our community</li>
               <li><strong>Write Your First Story</strong> - Use our intuitive editor to craft your narrative</li>
               <li><strong>Share and Engage</strong> - Publish your work and connect with readers</li>
               <li><strong>Discover Amazing Content</strong> - Explore stories from writers around the world</li>
             </ol>
             
             <blockquote>
               "Every great writer was once a beginner. Every expert was once a novice. Every icon was once an unknown." - Robin Sharma
             </blockquote>
             
             <p>Join thousands of writers who have already discovered the joy of sharing their stories on StoryCraft. Your unique voice matters, and we can\'t wait to hear what you have to say!</p>
             
             <p><em>Happy writing! 🖋️</em></p>',
            'Welcome to StoryCraft - the premier platform for writers and storytellers! Discover a beautiful, feature-rich environment designed to help you craft, share, and discover amazing stories.',
            1,
            287
        ],
        [
            'The Art of Digital Storytelling in the Modern Age',
            '<p>In our interconnected digital world, storytelling has evolved far beyond traditional mediums. Today\'s writers have unprecedented tools and platforms at their disposal, opening new frontiers for creative expression.</p>
             
             <h3>📱 The Digital Revolution</h3>
             <p>The rise of digital platforms has fundamentally changed how we tell and consume stories:</p>
             
             <ul>
               <li><strong>Instant Global Reach</strong> - Your stories can reach readers anywhere in the world instantly</li>
               <li><strong>Multimedia Integration</strong> - Combine text, images, audio, and video for rich storytelling</li>
               <li><strong>Interactive Elements</strong> - Engage readers with comments, polls, and social features</li>
               <li><strong>Real-time Feedback</strong> - Get immediate responses from your audience</li>
               <li><strong>Community Building</strong> - Connect with like-minded writers and readers</li>
             </ul>
             
             <h3>🎯 Key Elements of Effective Digital Storytelling</h3>
             
             <h4>1. Compelling Headlines</h4>
             <p>Your title is the first thing readers see. Make it count by being specific, intriguing, and honest about what your story delivers.</p>
             
             <h4>2. Strong Opening Hooks</h4>
             <p>Digital readers have short attention spans. Grab them immediately with a powerful opening that promises value.</p>
             
             <h4>3. Scannable Format</h4>
             <p>Use headings, bullet points, and short paragraphs to make your content easy to scan and digest.</p>
             
             <p>Digital storytelling isn\'t just about adapting old techniques to new mediums - it\'s about embracing entirely new ways of engaging with audiences and creating meaningful connections through narrative.</p>',
            'Explore how digital platforms have revolutionized storytelling and learn essential techniques for crafting compelling narratives in the digital age.',
            2,
            156
        ],
        [
            'Building Your Writing Habit: A Beginner\'s Complete Guide',
            '<p>Writing consistently is one of the most challenging aspects of being a writer. Whether you\'re working on your first novel, starting a blog, or exploring creative writing, developing a sustainable writing habit is crucial for long-term success.</p>
             
             <h3>🎯 Why Writing Habits Matter</h3>
             <p>Successful writers aren\'t those who write only when inspired - they\'re those who write regularly, regardless of how they feel. Here\'s why habits are so powerful:</p>
             
             <ul>
               <li><strong>Consistency Builds Momentum</strong> - Regular writing keeps your creative muscles strong</li>
               <li><strong>Reduces Resistance</strong> - When writing becomes routine, it requires less mental energy to start</li>
               <li><strong>Improves Quality</strong> - The more you write, the better you become</li>
               <li><strong>Increases Output</strong> - Small daily efforts compound into significant results</li>
               <li><strong>Develops Discipline</strong> - Writing habits strengthen your overall self-discipline</li>
             </ul>
             
             <h3>🌅 Starting Small: The 15-Minute Rule</h3>
             <p>Don\'t try to write for hours on your first day. Instead, commit to just 15 minutes of writing daily. This might seem insignificant, but it\'s incredibly powerful because:</p>
             
             <ul>
               <li>It\'s achievable for anyone, regardless of schedule</li>
               <li>It removes the pressure of producing large amounts of content</li>
               <li>It often leads to writing longer once you get started</li>
               <li>It builds the neural pathways associated with daily writing</li>
             </ul>
             
             <p>Remember, every published author started with a single word, then a sentence, then a paragraph. Your writing habit is the foundation upon which your entire writing career will be built. Start small, be consistent, and watch your skills and confidence grow with each passing day.</p>',
            'Learn how to build a sustainable writing habit that will transform your creative practice. From the 15-minute rule to overcoming common pitfalls, this guide covers everything you need to know.',
            3,
            203
        ],
        [
            'The Power of Community in Creative Writing',
            '<p>Writing can often feel like a solitary pursuit, but the most successful writers understand that community is essential for growth, motivation, and success. Whether you\'re just starting out or looking to take your writing to the next level, connecting with other writers can transform your creative journey.</p>
             
             <h3>🤝 Why Writer Communities Matter</h3>
             <p>Being part of a writing community offers numerous benefits that go far beyond just having people to talk to about your work:</p>
             
             <ul>
               <li><strong>Accountability Partners</strong> - Other writers help keep you committed to your goals</li>
               <li><strong>Constructive Feedback</strong> - Get honest, helpful critiques from people who understand the craft</li>
               <li><strong>Motivation and Support</strong> - Overcome writer\'s block and self-doubt with encouragement from peers</li>
               <li><strong>Learning Opportunities</strong> - Discover new techniques and approaches through shared knowledge</li>
               <li><strong>Networking</strong> - Build relationships that can lead to publishing opportunities</li>
               <li><strong>Diverse Perspectives</strong> - Exposure to different writing styles and genres</li>
             </ul>
             
             <p>Remember, the writing community is built on mutual support and shared passion for the craft. Whether you\'re seeking feedback on your latest chapter, looking for motivation to keep going, or wanting to celebrate a recent success, your writing community will be there for you.</p>',
            'Discover the transformative power of writing communities and learn how to find, join, and contribute to groups that will elevate your creative writing journey.',
            4,
            178
        ],
        [
            'Mastering the Art of Character Development',
            '<p>Great stories are driven by compelling characters that readers can connect with, understand, and care about. Whether you\'re writing fiction, creative non-fiction, or even blog posts, strong character development can make the difference between forgettable content and stories that stick with readers long after they\'ve finished reading.</p>
             
             <h3>🎭 What Makes a Character Compelling?</h3>
             <p>Compelling characters share several key qualities that make them feel real and relatable:</p>
             
             <ul>
               <li><strong>Clear Motivations</strong> - They want something specific and have reasons for wanting it</li>
               <li><strong>Internal Conflicts</strong> - They struggle with competing desires or beliefs</li>
               <li><strong>Flaws and Vulnerabilities</strong> - They\'re imperfect and human</li>
               <li><strong>Growth Potential</strong> - They can change and learn throughout the story</li>
               <li><strong>Unique Voice</strong> - They speak and think in distinctive ways</li>
               <li><strong>Backstory</strong> - They have a history that influences their present actions</li>
             </ul>
             
             <p>Remember, great characters feel like real people with complex inner lives, contradictions, and the capacity for growth. They drive your story forward and give readers someone to invest in emotionally.</p>',
            'Learn the essential techniques for creating compelling, three-dimensional characters that readers will connect with and remember long after finishing your story.',
            5,
            142
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, excerpt, author_id, views) VALUES (?, ?, ?, ?, ?)");
    foreach ($posts as $post) {
        $stmt->execute($post);
    }
    
    // Create sample files for demonstration
    createSampleFiles($pdo);
}

/**
 * Create sample files for demonstration
 */
function createSampleFiles($pdo) {
    // Create sample PDF content
    $pdfContent = "%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
/Resources <<
/Font <<
/F1 5 0 R
>>
>>
>>
endobj

4 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
72 720 Td
(StoryCraft Welcome Guide) Tj
ET
endstream
endobj

5 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj

xref
0 6
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000274 00000 n 
0000000369 00000 n 
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
466
%%EOF";
    
    // Create sample image (1x1 PNG)
    $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    
    // Save sample files
    $pdfPath = UPLOAD_DIR . 'welcome_guide.pdf';
    $imagePath = UPLOAD_DIR . 'sample_image.png';
    
    file_put_contents($pdfPath, $pdfContent);
    file_put_contents($imagePath, $imageContent);
    
    // Insert file records
    $files = [
        [1, 'welcome_guide.pdf', 'StoryCraft Welcome Guide.pdf', $pdfPath, strlen($pdfContent), 'application/pdf'],
        [2, 'sample_image.png', 'Sample Image.png', $imagePath, strlen($imageContent), 'image/png'],
        [3, 'writing_tips.txt', 'Writing Tips.txt', UPLOAD_DIR . 'writing_tips.txt', 1024, 'text/plain']
    ];
    
    // Create text file
    $textContent = "Writing Tips for Beginners\n\n1. Write every day, even if it's just for 15 minutes\n2. Read widely in your genre\n3. Don't edit while you write your first draft\n4. Join a writing community\n5. Set realistic goals\n6. Celebrate small victories\n7. Learn from feedback\n8. Keep a notebook for ideas\n9. Find your unique voice\n10. Never give up on your dreams";
    file_put_contents(UPLOAD_DIR . 'writing_tips.txt', $textContent);
    
    $stmt = $pdo->prepare("INSERT INTO post_files (post_id, filename, original_name, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($files as $file) {
        $stmt->execute($file);
    }
}

/**
 * Execute a prepared statement with parameters
 */
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Database operation failed");
    }
}

/**
 * Get a single record
 */
function fetchOne($pdo, $sql, $params = []) {
    $stmt = executeQuery($pdo, $sql, $params);
    return $stmt->fetch();
}

/**
 * Get multiple records
 */
function fetchAll($pdo, $sql, $params = []) {
    $stmt = executeQuery($pdo, $sql, $params);
    return $stmt->fetchAll();
}

/**
 * Get count of records
 */
function fetchCount($pdo, $sql, $params = []) {
    $stmt = executeQuery($pdo, $sql, $params);
    return $stmt->fetchColumn();
}
?>
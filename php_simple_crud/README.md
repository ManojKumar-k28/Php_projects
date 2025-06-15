# Article Hub - PHP CRUD Application

A complete PHP-based article management system with user authentication, file uploads, and modern responsive design.

## Features

### 🔐 User Authentication
- Secure user registration and login
- Password hashing with PHP's `password_hash()`
- Session management for user states
- User-specific content access

### 📝 CRUD Operations
- **Create**: Add new articles with title, content, and file attachments
- **Read**: View articles with beautiful formatting and media display
- **Update**: Edit existing articles with file replacement options
- **Delete**: Remove articles with automatic file cleanup

### 📁 File Upload System
- Image upload support (JPG, PNG, GIF)
- PDF attachment functionality
- Automatic file validation and security
- File size limits (5MB max)
- Unique filename generation to prevent conflicts

### 🎨 Modern Design
- Responsive design for all devices
- Beautiful gradients and hover effects
- Card-based layout for articles
- Professional typography and spacing
- Clean, intuitive user interface

## File Structure

```
/
├── config.php          # Database connection and core functions
├── index.php           # Homepage with article listing
├── login.php           # User login form
├── register.php        # User registration form
├── dashboard.php       # User dashboard with article management
├── add_post.php        # Create new article form
├── edit_post.php       # Edit existing article form
├── view_post.php       # Display single article
├── delete_post.php     # Delete article handler
├── logout.php          # Logout handler
├── style.css           # Complete styling system
├── .htaccess          # Security and PHP configuration
├── uploads/           # Directory for uploaded files
└── README.md          # This file
```

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Posts Table
```sql
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    pdf_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Installation

1. **Server Requirements**
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Apache with mod_rewrite enabled

2. **Setup Database**
   - Create a MySQL database named `crud_app`
   - Update database credentials in `config.php`

3. **Configure Permissions**
   - Make sure the `uploads/` directory is writable
   - Set appropriate file permissions (755 for directories, 644 for files)

4. **Upload Files**
   - Upload all files to your web server
   - Access the application through your web browser

## Security Features

- **Password Security**: Uses PHP's `password_hash()` and `password_verify()`
- **SQL Injection Prevention**: PDO prepared statements throughout
- **File Upload Security**: File type validation and size limits
- **Session Management**: Secure session handling for user authentication
- **XSS Prevention**: All output is properly escaped with `htmlspecialchars()`
- **Access Control**: User-specific content access and modification rights

## Usage

1. **Registration**: Create a new account with username, email, and password
2. **Login**: Access your dashboard with your credentials
3. **Create Articles**: Write new articles with optional image and PDF attachments
4. **Manage Content**: Edit, view, or delete your articles from the dashboard
5. **Public Access**: All articles are publicly viewable on the homepage

## Customization

### Styling
- Modify `style.css` to change colors, fonts, and layout
- The design uses CSS Grid and Flexbox for responsive layouts
- Color scheme is based on modern gradient designs

### Database
- Update `config.php` to change database connection settings
- Modify table schemas as needed for additional features

### File Uploads
- Adjust file size limits in `config.php`
- Add support for additional file types in the `uploadFile()` function
- Modify upload directory location if needed

## Technical Highlights

- **Modern PHP**: Uses PDO for database operations
- **Responsive Design**: Mobile-first approach with CSS Grid
- **Security-First**: Implements best practices for web security
- **File Management**: Automatic cleanup of deleted files
- **User Experience**: Smooth animations and intuitive interface
- **Performance**: Optimized queries and caching headers

This application demonstrates a complete PHP CRUD system with modern design principles and robust security measures, suitable for production use.
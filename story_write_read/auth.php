<?php
require_once 'config.php';
require_once 'db.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Register a new user
     */
    public function register($username, $email, $password, $confirmPassword) {
        // Validate input
        $errors = $this->validateRegistration($username, $email, $password, $confirmPassword);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if user already exists
        if ($this->userExists($username, $email)) {
            return ['success' => false, 'errors' => ['Username or email already exists']];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $sql = "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())";
            executeQuery($this->pdo, $sql, [$username, $email, $hashedPassword]);
            
            return ['success' => true, 'message' => 'Account created successfully! You can now login.'];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
        }
    }
    
    /**
     * Login user
     */
    public function login($usernameOrEmail, $password) {
        // Validate input
        if (empty($usernameOrEmail) || empty($password)) {
            return ['success' => false, 'error' => 'Please fill in all fields'];
        }
        
        // Get user
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $user = fetchOne($this->pdo, $sql, [$usernameOrEmail, $usernameOrEmail]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid username/email or password'];
        }
        
        // Update last login
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        executeQuery($this->pdo, $sql, [$user['id']]);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return ['success' => true];
    }
    
    /**
     * Get current logged-in user
     */
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id']) || !$this->isSessionValid()) {
            return false;
        }
        
        $sql = "SELECT id, username, email, role, created_at, last_login FROM users WHERE id = ?";
        return fetchOne($this->pdo, $sql, [$_SESSION['user_id']]);
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && $this->isSessionValid();
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    /**
     * Check if user can access admin features
     */
    public function canAccessAdmin() {
        return $this->hasRole('admin') || $this->hasRole('editor');
    }
    
    /**
     * Require login
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: index.php?error=Please login first');
            exit;
        }
    }
    
    /**
     * Require admin access
     */
    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->canAccessAdmin()) {
            header('Location: index.php?error=Access denied');
            exit;
        }
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistration($username, $email, $password, $confirmPassword) {
        $errors = [];
        
        // Username validation
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }
        
        // Email validation
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        // Password validation
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        }
        
        // Confirm password
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        return $errors;
    }
    
    /**
     * Check if user exists
     */
    private function userExists($username, $email) {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
        return fetchCount($this->pdo, $sql, [$username, $email]) > 0;
    }
    
    /**
     * Check if session is valid
     */
    private function isSessionValid() {
        if (!isset($_SESSION['login_time'])) {
            return false;
        }
        
        return (time() - $_SESSION['login_time']) < SESSION_TIMEOUT;
    }
}

// Initialize auth
$auth = new Auth($pdo);

// Handle authentication actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
                $result = $auth->register(
                    $_POST['username'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['password'] ?? '',
                    $_POST['confirm_password'] ?? ''
                );
                
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = implode('<br>', $result['errors']);
                }
                break;
                
            case 'login':
                $result = $auth->login(
                    $_POST['username'] ?? '',
                    $_POST['password'] ?? ''
                );
                
                if ($result['success']) {
                    header('Location: index.php');
                    exit;
                } else {
                    $error = $result['error'];
                }
                break;
                
            case 'logout':
                $auth->logout();
                header('Location: index.php');
                exit;
                break;
        }
    }
}
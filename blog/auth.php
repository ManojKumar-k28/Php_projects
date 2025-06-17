<?php
require_once 'config.php';
require_once 'db.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['login_time'] = time();
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['login_time'])) {
            // Check session timeout
            if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email']
            ];
        }
        return null;
    }
    
    public function register($username, $email, $password) {
        // Check if user already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            throw new Exception('Username or email already exists.');
        }
        
        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password) 
            VALUES (?, ?, ?)
        ");
        
        return $stmt->execute([$username, $email, $hashedPassword]);
    }
}

$auth = new Auth($pdo);

// Handle login/logout/register requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($auth->login($username, $password)) {
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } elseif ($_POST['action'] === 'register') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'All fields are required.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                try {
                    if ($auth->register($username, $email, $password)) {
                        $success = 'Account created successfully! You can now login.';
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        } elseif ($_POST['action'] === 'logout') {
            $auth->logout();
            header('Location: index.php');
            exit;
        }
    }
}
?>
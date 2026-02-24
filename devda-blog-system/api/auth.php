<?php
/**
 * devDA Blog System - Authentication API
 * API xử lý đăng ký, đăng nhập, đăng xuất
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Trả về JSON response
 */
function respond($status, $message, $data = []) {
    echo json_encode(array_merge([
        'status' => $status,
        'message' => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function isValidPassword($password) {
    return strlen($password) >= 6;
}

/**
 * Generate unique slug from string
 */
function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    // Replace special characters
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    // Remove leading/trailing dashes
    $text = trim($text, '-');
    return $text;
}

/**
 * Log action to logs.json
 */
function logAction($user_id, $action, $resource_id = null, $resource_type = null, $description = null) {
    $log = [
        'id' => uniqid('log_'),
        'user_id' => $user_id,
        'action' => $action,
        'resource_id' => $resource_id,
        'resource_type' => $resource_type,
        'description' => $description,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    addItem('logs', 'logs', $log);
}

// ============================================
// ACTION DISPATCHER
// ============================================

$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';

switch ($action) {
    case 'register':
        handleRegister();
        break;
    
    case 'login':
        handleLogin();
        break;
    
    case 'logout':
        handleLogout();
        break;
    
    case 'check':
        handleCheckAuth();
        break;
    
    case 'get-user':
        handleGetUser();
        break;
    
    default:
        respond('error', 'Action không hợp lệ');
}

// ============================================
// REGISTER HANDLER
// ============================================

function handleRegister() {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate inputs
    if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
        respond('error', 'Vui lòng điền đầy đủ thông tin');
    }
    
    $username = sanitize($input['username']);
    $email = trim($input['email']);
    $password = $input['password'];
    $full_name = sanitize($input['full_name'] ?? $username);
    
    // Validate email format
    if (!isValidEmail($email)) {
        respond('error', 'Email không hợp lệ');
    }
    
    // Validate password
    if (!isValidPassword($password)) {
        respond('error', 'Mật khẩu phải có ít nhất 6 ký tự');
    }
    
    // Validate username length
    if (strlen($username) < 3 || strlen($username) > 30) {
        respond('error', 'Tên đăng nhập phải từ 3-30 ký tự');
    }
    
    // Check if email already exists
    $users = getItems('users', 'users');
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            respond('error', 'Email này đã được đăng ký');
        }
        if ($user['username'] === $username) {
            respond('error', 'Tên đăng nhập này đã tồn tại');
        }
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_COST]);
    
    // Create new user
    $user = [
        'id' => uniqid('user_'),
        'username' => $username,
        'email' => $email,
        'password' => $password_hash,
        'full_name' => $full_name,
        'avatar' => '/blog/assets/images/default-avatar.jpg',
        'role' => 'user',
        'status' => 'active',
        'bio' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'last_login' => null,
        'email_verified' => false
    ];
    
    // Save user
    if (!addItem('users', 'users', $user)) {
        respond('error', 'Lỗi khi lưu tài khoản');
    }
    
    // Log action
    logAction($user['id'], 'register', $user['id'], 'user', 'Đăng ký tài khoản mới');
    
    respond('success', 'Đăng ký thành công! Vui lòng đăng nhập', [
        'user_id' => $user['id'],
        'redirect' => '/blog/login.php'
    ]);
}

// ============================================
// LOGIN HANDLER
// ============================================

function handleLogin() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate inputs
    if (empty($input['email']) || empty($input['password'])) {
        respond('error', 'Vui lòng nhập email và mật khẩu');
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    $remember = isset($input['remember']) && $input['remember'];
    
    // Find user by email
    $users = getItems('users', 'users');
    $user = null;
    
    foreach ($users as $u) {
        if ($u['email'] === $email) {
            $user = $u;
            break;
        }
    }
    
    if (!$user) {
        respond('error', 'Email không tồn tại');
    }
    
    // Check if account is banned
    if ($user['status'] === 'banned') {
        respond('error', 'Tài khoản của bạn đã bị khóa');
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        respond('error', 'Mật khẩu không chính xác');
    }
    
    // Update last login
    updateItem('users', 'users', $user['id'], [
        'last_login' => date('Y-m-d H:i:s')
    ]);
    
    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    
    // Set cookie if "Remember me" is checked
    if ($remember) {
        $cookie_value = base64_encode(json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'hash' => hash('sha256', $user['id'] . $user['password'])
        ]));
        
        setcookie(
            'auth_token',
            $cookie_value,
            time() + (30 * 24 * 60 * 60),  // 30 days
            '/blog/',
            SITE_DOMAIN,
            true,  // Secure
            true   // HttpOnly
        );
    }
    
    // Log action
    logAction($user['id'], 'login', $user['id'], 'user', 'Đăng nhập thành công');
    
    respond('success', 'Đăng nhập thành công', [
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar']
        ],
        'redirect' => '/blog/'
    ]);
}

// ============================================
// LOGOUT HANDLER
// ============================================

function handleLogout() {
    $user_id = $_SESSION['user_id'] ?? null;
    
    if ($user_id) {
        logAction($user_id, 'logout', $user_id, 'user', 'Đăng xuất');
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    // Clear cookie
    setcookie('auth_token', '', time() - 3600, '/blog/');
    
    respond('success', 'Đã đăng xuất', [
        'redirect' => '/blog/'
    ]);
}

// ============================================
// CHECK AUTH HANDLER
// ============================================

function handleCheckAuth() {
    if (!isset($_SESSION['user_id'])) {
        respond('error', 'Chưa đăng nhập');
    }
    
    $user = getItem('users', 'users', $_SESSION['user_id']);
    
    if (!$user) {
        session_unset();
        session_destroy();
        respond('error', 'Tài khoản không tồn tại');
    }
    
    respond('success', 'Đã xác thực', [
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar']
        ]
    ]);
}

// ============================================
// GET USER HANDLER
// ============================================

function handleGetUser() {
    $user_id = isset($_GET['id']) ? sanitize($_GET['id']) : null;
    
    if (!$user_id) {
        respond('error', 'ID người dùng không hợp lệ');
    }
    
    $user = getItem('users', 'users', $user_id);
    
    if (!$user) {
        respond('error', 'Người dùng không tồn tại');
    }
    
    // Don't expose password hash
    unset($user['password']);
    
    respond('success', 'Lấy thông tin người dùng thành công', [
        'user' => $user
    ]);
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

?>

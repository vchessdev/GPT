<?php
/**
 * DevDA Blog System - Auth API
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        handleCheck();
        break;
    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

/**
 * Đăng nhập
 */
function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Phương thức không hợp lệ'], 400);
    }
    
    $username = validateInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse(['error' => 'Vui lòng nhập username và password'], 400);
    }
    
    $users = $db->getAll('users');
    
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            // Kiểm tra password
            if (password_verify($password, $user['password'])) {
                // Lưu session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                
                // Lưu cookie (optional, 30 ngày)
                setcookie('user_id', $user['id'], time() + (30 * 24 * 60 * 60), '/blog/');
                setcookie('username', $user['username'], time() + (30 * 24 * 60 * 60), '/blog/');
                
                // Log
                $db->create('logs', [
                    'action' => 'login',
                    'user_id' => $user['id'],
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'details' => 'Đăng nhập thành công'
                ]);
                
                jsonResponse([
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role'] ?? 'user'
                    ]
                ]);
            }
            
            // Password sai
            jsonResponse(['error' => 'Username hoặc password sai'], 401);
        }
    }
    
    jsonResponse(['error' => 'Người dùng không tồn tại'], 404);
}

/**
 * Đăng ký
 */
function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Phương thức không hợp lệ'], 400);
    }
    
    $username = validateInput($_POST['username'] ?? '');
    $email = validateInput($_POST['email'] ?? '', 'email');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        jsonResponse(['error' => 'Vui lòng điền đầy đủ thông tin'], 400);
    }
    
    if ($password !== $password_confirm) {
        jsonResponse(['error' => 'Mật khẩu không khớp'], 400);
    }
    
    if (strlen($password) < 6) {
        jsonResponse(['error' => 'Mật khẩu phải ít nhất 6 ký tự'], 400);
    }
    
    // Kiểm tra username đã tồn tại
    if ($db->find('users', 'username', $username)) {
        jsonResponse(['error' => 'Username đã tồn tại'], 409);
    }
    
    // Kiểm tra email đã tồn tại
    if ($db->find('users', 'email', $email)) {
        jsonResponse(['error' => 'Email đã tồn tại'], 409);
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    
    // Tạo user mới
    $newUser = $db->create('users', [
        'username' => $username,
        'email' => $email,
        'password' => $passwordHash,
        'role' => 'user',
        'status' => 'active'
    ]);
    
    if ($newUser) {
        // Log
        $db->create('logs', [
            'action' => 'register',
            'user_id' => $newUser['id'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'details' => 'Đăng ký tài khoản mới'
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Đăng ký thành công, vui lòng đăng nhập',
            'user_id' => $newUser['id']
        ], 201);
    }
    
    jsonResponse(['error' => 'Lỗi khi tạo tài khoản'], 500);
}

/**
 * Đăng xuất
 */
function handleLogout() {
    if (isset($_SESSION['user_id'])) {
        $db->create('logs', [
            'action' => 'logout',
            'user_id' => $_SESSION['user_id'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'details' => 'Đăng xuất'
        ]);
    }
    
    session_destroy();
    setcookie('user_id', '', time() - 3600, '/blog/');
    setcookie('username', '', time() - 3600, '/blog/');
    
    jsonResponse(['success' => true, 'message' => 'Đăng xuất thành công']);
}

/**
 * Kiểm tra trạng thái đăng nhập
 */
function handleCheck() {
    if (isLoggedIn()) {
        jsonResponse([
            'loggedIn' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role'] ?? 'user'
            ]
        ]);
    }
    
    jsonResponse(['loggedIn' => false]);
}
?>

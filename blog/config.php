<?php
/**
 * DevDA Blog System - Config
 * x10hosting Free Version
 */

// Xác định base URL (tự động detect từ server)
$baseURL = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/blog';
define('BASE_URL', $baseURL);

// Xác định đường dẫn thư mục
define('BASE_DIR', __DIR__);
define('DATA_DIR', BASE_DIR . '/data');
define('UPLOADS_DIR', BASE_DIR . '/uploads');
define('API_DIR', BASE_DIR . '/api');

// Bảo mật
define('DB_ENCRYPTION_KEY', 'devda_blog_system_2024_secret_key_x10hosting');

// Session timeout (30 phút)
define('SESSION_TIMEOUT', 1800);

// Khởi động session nếu chưa start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Xử lý tất cả lỗi ngoài ý muốn
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cho phép JSON uploads
ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');

// Hàm utility
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function validateInput($input, $type = 'text') {
    $input = trim($input);
    
    if ($type === 'email') {
        return filter_var($input, FILTER_VALIDATE_EMAIL);
    }
    
    if ($type === 'url') {
        return filter_var($input, FILTER_VALIDATE_URL);
    }
    
    // XSS protection
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function sanitizeFileName($filename) {
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return $filename;
}

// Tạo folders nếu chưa có
@mkdir(DATA_DIR, 0755, true);
@mkdir(UPLOADS_DIR, 0755, true);
@mkdir(UPLOADS_DIR . '/images', 0755, true);
@mkdir(UPLOADS_DIR . '/pdf', 0755, true);
@mkdir(UPLOADS_DIR . '/docs', 0755, true);

// Khởi động database
require_once API_DIR . '/database.php';

// Tạo global $db instance
$db = Database::getInstance();
?>

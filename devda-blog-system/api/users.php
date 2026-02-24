<?php
/**
 * devDA Blog System - Users Management API (Admin)
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function respond($status, $message, $data = []) {
    echo json_encode(array_merge([
        'status' => $status,
        'message' => $message
    ], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function checkAdminAuth() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        respond('error', 'Bạn không có quyền admin');
    }
}

function sanitize($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

checkAdminAuth();

$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';

switch ($action) {
    case 'ban':
        handleBanUser();
        break;
    
    case 'unban':
        handleUnbanUser();
        break;
    
    case 'promote':
        handlePromoteUser();
        break;
    
    case 'demote':
        handleDemoteUser();
        break;
    
    case 'delete':
        handleDeleteUser();
        break;
    
    default:
        respond('error', 'Action không hợp lệ');
}

function handleBanUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['user_id'])) {
        respond('error', 'ID user không hợp lệ');
    }
    
    $user = getItem('users', 'users', $input['user_id']);
    
    if (!$user) {
        respond('error', 'User không tồn tại');
    }
    
    // Prevent banning yourself
    if ($user['id'] === $_SESSION['user_id']) {
        respond('error', 'Không thể khóa tài khoản của chính mình');
    }
    
    if (!updateItem('users', 'users', $user['id'], ['status' => 'banned'])) {
        respond('error', 'Lỗi khi cập nhật');
    }
    
    $log = [
        'id' => uniqid('log_'),
        'user_id' => $_SESSION['user_id'],
        'action' => 'ban_user',
        'resource_id' => $user['id'],
        'resource_type' => 'user',
        'description' => 'Khóa tài khoản user: ' . $user['username'],
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    addItem('logs', 'logs', $log);
    
    respond('success', 'Đã khóa tài khoản user');
}

function handleUnbanUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['user_id'])) {
        respond('error', 'ID user không hợp lệ');
    }
    
    $user = getItem('users', 'users', $input['user_id']);
    
    if (!$user) {
        respond('error', 'User không tồn tại');
    }
    
    if (!updateItem('users', 'users', $user['id'], ['status' => 'active'])) {
        respond('error', 'Lỗi khi cập nhật');
    }
    
    $log = [
        'id' => uniqid('log_'),
        'user_id' => $_SESSION['user_id'],
        'action' => 'unban_user',
        'resource_id' => $user['id'],
        'resource_type' => 'user',
        'description' => 'Mở khóa tài khoản user: ' . $user['username'],
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    addItem('logs', 'logs', $log);
    
    respond('success', 'Đã mở khóa tài khoản user');
}

function handlePromoteUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['user_id'])) {
        respond('error', 'ID user không hợp lệ');
    }
    
    $user = getItem('users', 'users', $input['user_id']);
    
    if (!$user) {
        respond('error', 'User không tồn tại');
    }
    
    if ($user['role'] === 'admin') {
        respond('error', 'User này đã là admin');
    }
    
    if (!updateItem('users', 'users', $user['id'], ['role' => 'admin'])) {
        respond('error', 'Lỗi khi cập nhật');
    }
    
    $log = [
        'id' => uniqid('log_'),
        'user_id' => $_SESSION['user_id'],
        'action' => 'promote_user',
        'resource_id' => $user['id'],
        'resource_type' => 'user',
        'description' => 'Nâng quyền user: ' . $user['username'] . ' → Admin',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    addItem('logs', 'logs', $log);
    
    respond('success', 'Đã nâng quyền user lên admin');
}

function handleDemoteUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['user_id'])) {
        respond('error', 'ID user không hợp lệ');
    }
    
    $user = getItem('users', 'users', $input['user_id']);
    
    if (!$user) {
        respond('error', 'User không tồn tại');
    }
    
    // Prevent demoting yourself
    if ($user['id'] === $_SESSION['user_id']) {
        respond('error', 'Không thể hạ quyền chính mình');
    }
    
    if ($user['role'] === 'user') {
        respond('error', 'User này không phải admin');
    }
    
    if (!updateItem('users', 'users', $user['id'], ['role' => 'user'])) {
        respond('error', 'Lỗi khi cập nhật');
    }
    
    $log = [
        'id' => uniqid('log_'),
        'user_id' => $_SESSION['user_id'],
        'action' => 'demote_user',
        'resource_id' => $user['id'],
        'resource_type' => 'user',
        'description' => 'Hạ quyền user: ' . $user['username'] . ' → User',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    addItem('logs', 'logs', $log);
    
    respond('success', 'Đã hạ quyền user');
}

function handleDeleteUser() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['user_id'])) {
        respond('error', 'ID user không hợp lệ');
    }
    
    $user = getItem('users', 'users', $input['user_id']);
    
    if (!$user) {
        respond('error', 'User không tồn tại');
    }
    
    // Prevent deleting yourself
    if ($user['id'] === $_SESSION['user_id']) {
        respond('error', 'Không thể xóa chính mình');
    }
    
    // Delete user
    if (!deleteItem('users', 'users', $user['id'])) {
        respond('error', 'Lỗi khi xóa user');
    }
    
    // Delete user's posts (optional: move to archive instead)
    $posts = getItems('posts', 'posts');
    $posts = array_filter($posts, fn($p) => $p['author_id'] !== $user['id']);
    $data = readJSON('posts');
    $data['posts'] = array_values($posts);
    writeJSON('posts', $data);
    
    // Delete user's comments
    $comments = getItems('comments', 'comments');
    $comments = array_filter($comments, fn($c) => $c['user_id'] !== $user['id']);
    $data = readJSON('comments');
    $data['comments'] = array_values($comments);
    writeJSON('comments', $data);
    
    // Delete user's votes
    $votes = getItems('votes', 'votes');
    $votes = array_filter($votes, fn($v) => $v['user_id'] !== $user['id']);
    $data = readJSON('votes');
    $data['votes'] = array_values($votes);
    writeJSON('votes', $data);
    
    $log = [
        'id' => uniqid('log_'),
        'user_id' => $_SESSION['user_id'],
        'action' => 'delete_user',
        'resource_id' => $user['id'],
        'resource_type' => 'user',
        'description' => 'Xóa user: ' . $user['username'],
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    addItem('logs', 'logs', $log);
    
    respond('success', 'Đã xóa user và dữ liệu liên quan');
}

?>

<?php
/**
 * DevDA Blog System - Users API (Admin only)
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Chỉ admin mới được truy cập
if (!isAdmin()) {
    jsonResponse(['error' => 'Không có quyền'], 403);
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch($action) {
    case 'list':
        handleList();
        break;
    case 'update':
        handleUpdate();
        break;
    case 'delete':
        handleDelete();
        break;
    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

/**
 * Liệt kê tất cả users (admin only)
 */
function handleList() {
    global $db;
    $users = $db->getAll('users');
    
    // Bỏ password trước khi trả về
    foreach ($users as &$user) {
        unset($user['password']);
    }
    
    jsonResponse(['users' => $users]);
}

/**
 * Cập nhật user (admin only)
 */
function handleUpdate() {
    global $db;
    
    $userId = $_POST['id'] ?? null;
    
    if (!$userId) {
        jsonResponse(['error' => 'ID user không hợp lệ'], 400);
    }
    
    $user = $db->find('users', 'id', $userId);
    
    if (!$user) {
        jsonResponse(['error' => 'User không tồn tại'], 404);
    }
    
    $updateData = [];
    
    if (isset($_POST['email'])) {
        $updateData['email'] = validateInput($_POST['email'], 'email');
    }
    
    if (isset($_POST['role'])) {
        $updateData['role'] = in_array($_POST['role'], ['admin', 'user']) ? $_POST['role'] : 'user';
    }
    
    if (isset($_POST['status'])) {
        $updateData['status'] = in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'active';
    }
    
    $updated = $db->update('users', $userId, $updateData);
    
    if ($updated) {
        unset($updated['password']);
        
        $db->create('logs', [
            'action' => 'update_user',
            'user_id' => $_SESSION['user_id'],
            'details' => 'Cập nhật user: ' . $user['username']
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'User đã được cập nhật',
            'user' => $updated
        ]);
    }
    
    jsonResponse(['error' => 'Lỗi khi cập nhật user'], 500);
}

/**
 * Xoá user (admin only)
 */
function handleDelete() {
    global $db;
    
    $userId = $_POST['id'] ?? null;
    
    if (!$userId) {
        jsonResponse(['error' => 'ID user không hợp lệ'], 400);
    }
    
    // Không cho xoá admin
    $user = $db->find('users', 'id', $userId);
    
    if (!$user) {
        jsonResponse(['error' => 'User không tồn tại'], 404);
    }
    
    if ($user['role'] === 'admin') {
        jsonResponse(['error' => 'Không thể xoá admin user'], 403);
    }
    
    if ($db->delete('users', $userId)) {
        // Xoá bài viết của user
        $posts = $db->filter('posts', function($p) use ($userId) {
            return $p['author_id'] === $userId;
        });
        
        foreach ($posts as $post) {
            $db->delete('posts', $post['id']);
        }
        
        $db->create('logs', [
            'action' => 'delete_user',
            'user_id' => $_SESSION['user_id'],
            'details' => 'Xoá user: ' . $user['username']
        ]);
        
        jsonResponse(['success' => true, 'message' => 'User đã được xoá']);
    }
    
    jsonResponse(['error' => 'Lỗi khi xoá user'], 500);
}
?>

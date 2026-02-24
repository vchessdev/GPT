<?php
/**
 * devDA Blog System - Comments API
 * API xử lý bình luận
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

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        respond('error', 'Chưa đăng nhập');
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function sanitize($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

function logAction($action, $resource_id, $description) {
    $log = [
        'id' => uniqid('log_'),
        'user_id' => $_SESSION['user_id'] ?? 'guest',
        'action' => $action,
        'resource_id' => $resource_id,
        'resource_type' => 'comment',
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
    case 'create':
        checkAuth();
        handleCreateComment();
        break;
    
    case 'list':
        handleListComments();
        break;
    
    case 'delete':
        checkAuth();
        handleDeleteComment();
        break;
    
    case 'hide':
        checkAuth();
        handleHideComment();
        break;
    
    case 'approve':
        checkAuth();
        handleApproveComment();
        break;
    
    default:
        respond('error', 'Action không hợp lệ');
}

// ============================================
// CREATE COMMENT
// ============================================

function handleCreateComment() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate inputs
    if (empty($input['post_id']) || empty($input['content'])) {
        respond('error', 'Nội dung bình luận không được để trống');
    }
    
    $post_id = sanitize($input['post_id']);
    $content = sanitize($input['content']);
    $parent_id = isset($input['parent_id']) ? sanitize($input['parent_id']) : null;
    
    // Validate content length
    if (strlen($content) < 2 || strlen($content) > 5000) {
        respond('error', 'Nội dung bình luận phải từ 2-5000 ký tự');
    }
    
    // Check if post exists
    $post = getItem('posts', 'posts', $post_id);
    if (!$post || $post['status'] !== 'published') {
        respond('error', 'Bài viết không tồn tại');
    }
    
    // Create comment
    $comment = [
        'id' => uniqid('comment_'),
        'post_id' => $post_id,
        'user_id' => $_SESSION['user_id'],
        'content' => $content,
        'parent_id' => $parent_id,
        'status' => 'approved',  // Auto-approve (có thể thay đổi)
        'votes' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Save comment
    if (!addItem('comments', 'comments', $comment)) {
        respond('error', 'Lỗi khi lưu bình luận');
    }
    
    // Increment comment count in post
    updateItem('posts', 'posts', $post_id, [
        'views' => $post['views'] + 1  // Hoặc tạo field comment_count riêng
    ]);
    
    logAction('create_comment', $comment['id'], 'Bình luận bài viết: ' . $post_id);
    
    // Get user info
    $user = getItem('users', 'users', $_SESSION['user_id']);
    
    respond('success', 'Bình luận thành công', [
        'comment_id' => $comment['id'],
        'comment' => array_merge($comment, [
            'author' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'avatar' => $user['avatar']
            ]
        ])
    ]);
}

// ============================================
// LIST COMMENTS
// ============================================

function handleListComments() {
    $post_id = isset($_GET['post_id']) ? sanitize($_GET['post_id']) : '';
    $parent_id = isset($_GET['parent_id']) ? sanitize($_GET['parent_id']) : null;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    if (!$post_id) {
        respond('error', 'ID bài viết không hợp lệ');
    }
    
    $comments = getItems('comments', 'comments');
    
    // Filter by post and approved status
    $comments = array_filter($comments, function($c) use ($post_id, $parent_id) {
        if ($parent_id === null) {
            return $c['post_id'] === $post_id && 
                   $c['status'] === 'approved' && 
                   ($c['parent_id'] === null || $c['parent_id'] === '');
        } else {
            return $c['post_id'] === $post_id && 
                   $c['status'] === 'approved' && 
                   $c['parent_id'] === $parent_id;
        }
    });
    
    // Sort by date
    $comments = sortItems($comments, 'created_at', 'asc');
    
    // Add user info
    $users = getItems('users', 'users');
    $users_map = [];
    foreach ($users as $user) {
        $users_map[$user['id']] = $user;
    }
    
    foreach ($comments as &$comment) {
        if (isset($users_map[$comment['user_id']])) {
            $comment['author'] = [
                'id' => $users_map[$comment['user_id']]['id'],
                'username' => $users_map[$comment['user_id']]['username'],
                'avatar' => $users_map[$comment['user_id']]['avatar']
            ];
        }
        
        // Get replies
        $all_comments = getItems('comments', 'comments');
        $replies = array_filter($all_comments, function($c) use ($comment) {
            return $c['post_id'] === $comment['post_id'] && 
                   $c['parent_id'] === $comment['id'] &&
                   $c['status'] === 'approved';
        });
        $comment['replies_count'] = count($replies);
    }
    
    // Paginate
    $result = paginate($comments, $page, COMMENTS_PER_PAGE);
    
    respond('success', 'Lấy danh sách bình luận thành công', $result);
}

// ============================================
// DELETE COMMENT
// ============================================

function handleDeleteComment() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['comment_id'])) {
        respond('error', 'ID bình luận không hợp lệ');
    }
    
    $comment = getItem('comments', 'comments', $input['comment_id']);
    
    if (!$comment) {
        respond('error', 'Bình luận không tồn tại');
    }
    
    // Check permission (author or admin)
    if ($comment['user_id'] !== $_SESSION['user_id'] && !isAdmin()) {
        respond('error', 'Bạn không có quyền xóa bình luận này');
    }
    
    // Delete comment
    if (!deleteItem('comments', 'comments', $comment['id'])) {
        respond('error', 'Lỗi khi xóa bình luận');
    }
    
    // Delete related replies
    $comments = getItems('comments', 'comments');
    $comments = array_filter($comments, function($c) use ($comment) {
        return $c['parent_id'] !== $comment['id'];
    });
    
    $data = readJSON('comments');
    $data['comments'] = array_values($comments);
    writeJSON('comments', $data);
    
    logAction('delete_comment', $comment['id'], 'Xóa bình luận');
    
    respond('success', 'Xóa bình luận thành công');
}

// ============================================
// HIDE COMMENT
// ============================================

function handleHideComment() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['comment_id'])) {
        respond('error', 'ID bình luận không hợp lệ');
    }
    
    // Only admin can hide comments
    if (!isAdmin()) {
        respond('error', 'Bạn không có quyền ẩn bình luận');
    }
    
    $comment = getItem('comments', 'comments', $input['comment_id']);
    
    if (!$comment) {
        respond('error', 'Bình luận không tồn tại');
    }
    
    // Update status
    if (!updateItem('comments', 'comments', $comment['id'], [
        'status' => 'hidden'
    ])) {
        respond('error', 'Lỗi khi ẩn bình luận');
    }
    
    logAction('hide_comment', $comment['id'], 'Ẩn bình luận');
    
    respond('success', 'Ẩn bình luận thành công');
}

// ============================================
// APPROVE COMMENT
// ============================================

function handleApproveComment() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['comment_id'])) {
        respond('error', 'ID bình luận không hợp lệ');
    }
    
    // Only admin can approve comments
    if (!isAdmin()) {
        respond('error', 'Bạn không có quyền phê duyệt bình luận');
    }
    
    $comment = getItem('comments', 'comments', $input['comment_id']);
    
    if (!$comment) {
        respond('error', 'Bình luận không tồn tại');
    }
    
    // Update status
    if (!updateItem('comments', 'comments', $comment['id'], [
        'status' => 'approved'
    ])) {
        respond('error', 'Lỗi khi phê duyệt bình luận');
    }
    
    logAction('approve_comment', $comment['id'], 'Phê duyệt bình luận');
    
    respond('success', 'Phê duyệt bình luận thành công');
}

?>

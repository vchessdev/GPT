<?php
/**
 * DevDA Blog System - Comments API
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch($action) {
    case 'create':
        handleCreate();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'list':
        handleList();
        break;
    case 'hide':
        handleHide();
        break;
    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

/**
 * Tạo bình luận mới
 */
function handleCreate() {
    global $db;
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Bạn cần đăng nhập để bình luận'], 401);
    }
    
    $postId = $_POST['post_id'] ?? null;
    $content = validateInput($_POST['content'] ?? '');
    $parentId = $_POST['parent_id'] ?? null;
    
    if (!$postId || empty($content)) {
        jsonResponse(['error' => 'Nội dung bình luận không được bỏ trống'], 400);
    }
    
    // Kiểm tra bài viết tồn tại
    $post = $db->find('posts', 'id', $postId);
    if (!$post) {
        jsonResponse(['error' => 'Bài viết không tồn tại'], 404);
    }
    
    $comment = $db->create('comments', [
        'post_id' => $postId,
        'user_id' => $_SESSION['user_id'],
        'content' => $content,
        'parent_id' => $parentId,
        'status' => 'approved'
    ]);
    
    if ($comment) {
        $db->create('logs', [
            'action' => 'create_comment',
            'user_id' => $_SESSION['user_id'],
            'details' => 'Bình luận bài: ' . $post['title']
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Bình luận đã được đăng',
            'comment' => $comment
        ], 201);
    }
    
    jsonResponse(['error' => 'Lỗi khi đăng bình luận'], 500);
}

/**
 * Xoá bình luận
 */
function handleDelete() {
    global $db;
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Bạn cần đăng nhập'], 401);
    }
    
    $commentId = $_POST['id'] ?? $_GET['id'] ?? null;
    if (!$commentId) {
        jsonResponse(['error' => 'ID bình luận không hợp lệ'], 400);
    }
    
    $comment = $db->find('comments', 'id', $commentId);
    if (!$comment) {
        jsonResponse(['error' => 'Bình luận không tồn tại'], 404);
    }
    
    // Kiểm tra quyền
    if ($comment['user_id'] !== $_SESSION['user_id'] && !isAdmin()) {
        jsonResponse(['error' => 'Bạn không có quyền xoá bình luận này'], 403);
    }
    
    if ($db->delete('comments', $commentId)) {
        $db->create('logs', [
            'action' => 'delete_comment',
            'user_id' => $_SESSION['user_id'],
            'details' => 'Xoá bình luận'
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Bình luận đã được xoá']);
    }
    
    jsonResponse(['error' => 'Lỗi khi xoá bình luận'], 500);
}

/**
 * Danh sách bình luận
 */
function handleList() {
    global $db;
    $postId = $_GET['post_id'] ?? null;
    
    if (!$postId) {
        jsonResponse(['error' => 'ID bài viết không hợp lệ'], 400);
    }
    
    $comments = $db->filter('comments', function($c) use ($postId) {
        return $c['post_id'] === $postId && $c['status'] !== 'hidden';
    });
    
    // Thêm thông tin tác giả
    foreach ($comments as &$comment) {
        $author = $db->find('users', 'id', $comment['user_id']);
        $comment['author'] = [
            'id' => $author['id'],
            'username' => $author['username']
        ];
    }
    
    // Sắp xếp theo ngày cũ nhất
    usort($comments, function($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });
    
    jsonResponse(['comments' => $comments]);
}

/**
 * Ẩn bình luận (admin only)
 */
function handleHide() {
    global $db;
    if (!isAdmin()) {
        jsonResponse(['error' => 'Bạn không có quyền thực hiện hành động này'], 403);
    }
    
    $commentId = $_POST['id'] ?? null;
    if (!$commentId) {
        jsonResponse(['error' => 'ID bình luận không hợp lệ'], 400);
    }
    
    $comment = $db->find('comments', 'id', $commentId);
    if (!$comment) {
        jsonResponse(['error' => 'Bình luận không tồn tại'], 404);
    }
    
    $updated = $db->update('comments', $commentId, ['status' => 'hidden']);
    
    if ($updated) {
        $db->create('logs', [
            'action' => 'hide_comment',
            'user_id' => $_SESSION['user_id'],
            'details' => 'Ẩn bình luận'
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Bình luận đã được ẩn']);
    }
    
    jsonResponse(['error' => 'Lỗi khi ẩn bình luận'], 500);
}
?>

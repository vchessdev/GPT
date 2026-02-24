<?php
/**
 * DevDA Blog System - Votes API
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch($action) {
    case 'vote':
        handleVote();
        break;
    case 'unvote':
        handleUnvote();
        break;
    case 'getVotes':
        handleGetVotes();
        break;
    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

/**
 * Vote (like/dislike)
 */
function handleVote() {
    global $db;
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Bạn cần đăng nhập để vote'], 401);
    }
    
    $postId = $_POST['post_id'] ?? null;
    $type = $_POST['type'] ?? 'like'; // like hoặc dislike
    
    if (!$postId || !in_array($type, ['like', 'dislike'])) {
        jsonResponse(['error' => 'Dữ liệu không hợp lệ'], 400);
    }
    
    // Kiểm tra bài viết tồn tại
    $post = $db->find('posts', 'id', $postId);
    if (!$post) {
        jsonResponse(['error' => 'Bài viết không tồn tại'], 404);
    }
    
    $userId = $_SESSION['user_id'];
    
    // Kiểm tra đã vote chưa
    $votes = $db->filter('votes', function($v) use ($postId, $userId) {
        return $v['post_id'] === $postId && $v['user_id'] === $userId;
    });
    
    if (count($votes) > 0) {
        // Nếu vote cùng type, bỏ vote
        if ($votes[0]['type'] === $type) {
            $db->delete('votes', $votes[0]['id']);
            jsonResponse([
                'success' => true,
                'message' => 'Đã bỏ vote',
                'action' => 'removed'
            ]);
        } else {
            // Nếu khác type, thay đổi vote
            $updated = $db->update('votes', $votes[0]['id'], ['type' => $type]);
            jsonResponse([
                'success' => true,
                'message' => 'Vote đã được thay đổi',
                'action' => 'changed',
                'vote' => $updated
            ]);
        }
    } else {
        // Tạo vote mới
        $vote = $db->create('votes', [
            'post_id' => $postId,
            'user_id' => $userId,
            'type' => $type
        ]);
        
        if ($vote) {
            jsonResponse([
                'success' => true,
                'message' => 'Vote thành công',
                'action' => 'added',
                'vote' => $vote
            ], 201);
        }
    }
    
    jsonResponse(['error' => 'Lỗi khi vote'], 500);
}

/**
 * Bỏ vote
 */
function handleUnvote() {
    global $db;
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Bạn cần đăng nhập'], 401);
    }
    
    $postId = $_POST['post_id'] ?? null;
    
    if (!$postId) {
        jsonResponse(['error' => 'ID bài viết không hợp lệ'], 400);
    }
    
    $userId = $_SESSION['user_id'];
    
    $votes = $db->filter('votes', function($v) use ($postId, $userId) {
        return $v['post_id'] === $postId && $v['user_id'] === $userId;
    });
    
    if (count($votes) > 0) {
        if ($db->delete('votes', $votes[0]['id'])) {
            jsonResponse(['success' => true, 'message' => 'Đã bỏ vote']);
        }
    }
    
    jsonResponse(['error' => 'Lỗi khi bỏ vote'], 500);
}

/**
 * Lấy thông tin vote
 */
function handleGetVotes() {
    global $db;
    $postId = $_GET['post_id'] ?? null;
    
    if (!$postId) {
        jsonResponse(['error' => 'ID bài viết không hợp lệ'], 400);
    }
    
    $likes = $db->filter('votes', function($v) use ($postId) {
        return $v['post_id'] === $postId && $v['type'] === 'like';
    });
    
    $dislikes = $db->filter('votes', function($v) use ($postId) {
        return $v['post_id'] === $postId && $v['type'] === 'dislike';
    });
    
    $userVote = null;
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $vote = $db->find('votes', 'post_id', $postId);
        
        $allVotes = $db->filter('votes', function($v) use ($postId, $userId) {
            return $v['post_id'] === $postId && $v['user_id'] === $userId;
        });
        
        if (count($allVotes) > 0) {
            $userVote = $allVotes[0]['type'];
        }
    }
    
    jsonResponse([
        'likes' => count($likes),
        'dislikes' => count($dislikes),
        'userVote' => $userVote
    ]);
}
?>

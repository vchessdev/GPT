<?php
/**
 * devDA Blog System - Votes API (Like/Dislike)
 * API xử lý vote bài viết
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

function sanitize($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

function logAction($action, $resource_id, $description) {
    $log = [
        'id' => uniqid('log_'),
        'user_id' => $_SESSION['user_id'],
        'action' => $action,
        'resource_id' => $resource_id,
        'resource_type' => 'vote',
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
    case 'vote':
        checkAuth();
        handleVote();
        break;
    
    case 'check':
        handleCheckVote();
        break;
    
    case 'stats':
        handleGetVoteStats();
        break;
    
    default:
        respond('error', 'Action không hợp lệ');
}

// ============================================
// VOTE HANDLER
// ============================================

function handleVote() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate inputs
    if (empty($input['post_id']) || empty($input['vote_type'])) {
        respond('error', 'Thông tin vote không hợp lệ');
    }
    
    $post_id = sanitize($input['post_id']);
    $vote_type = sanitize($input['vote_type']);  // 'like' hoặc 'dislike'
    $user_id = $_SESSION['user_id'];
    
    // Validate vote type
    if (!in_array($vote_type, ['like', 'dislike'])) {
        respond('error', 'Loại vote không hợp lệ');
    }
    
    // Check if post exists
    $post = getItem('posts', 'posts', $post_id);
    if (!$post) {
        respond('error', 'Bài viết không tồn tại');
    }
    
    // Get all votes
    $votes = getItems('votes', 'votes');
    
    // Check if user already voted on this post
    $existing_vote = null;
    $vote_index = null;
    
    foreach ($votes as $index => $v) {
        if ($v['post_id'] === $post_id && $v['user_id'] === $user_id) {
            $existing_vote = $v;
            $vote_index = $index;
            break;
        }
    }
    
    // Update vote counts
    $likes = $post['likes'] ?? 0;
    $dislikes = $post['dislikes'] ?? 0;
    $user_vote = null;
    
    if ($existing_vote) {
        // User already voted
        if ($existing_vote['vote_type'] === $vote_type) {
            // Same vote: remove it (toggle)
            unset($votes[$vote_index]);
            $votes = array_values($votes);
            
            if ($vote_type === 'like') {
                $likes--;
            } else {
                $dislikes--;
            }
        } else {
            // Different vote: switch it
            if ($existing_vote['vote_type'] === 'like') {
                $likes--;
                $dislikes++;
            } else {
                $dislikes--;
                $likes++;
            }
            
            // Update existing vote
            $votes[$vote_index]['vote_type'] = $vote_type;
            $votes[$vote_index]['created_at'] = date('Y-m-d H:i:s');
            $user_vote = $vote_type;
        }
    } else {
        // New vote
        $vote = [
            'id' => uniqid('vote_'),
            'user_id' => $user_id,
            'post_id' => $post_id,
            'vote_type' => $vote_type,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $votes[] = $vote;
        
        if ($vote_type === 'like') {
            $likes++;
        } else {
            $dislikes++;
        }
        
        $user_vote = $vote_type;
    }
    
    // Update votes.json
    $data = readJSON('votes');
    $data['votes'] = $votes;
    
    if (!writeJSON('votes', $data)) {
        respond('error', 'Lỗi khi lưu vote');
    }
    
    // Update post vote counts
    if (!updateItem('posts', 'posts', $post_id, [
        'likes' => max(0, $likes),
        'dislikes' => max(0, $dislikes)
    ])) {
        respond('error', 'Lỗi khi cập nhật bài viết');
    }
    
    logAction('vote', $post_id, 'Vote bài viết: ' . $vote_type);
    
    respond('success', 'Vote thành công', [
        'total_likes' => max(0, $likes),
        'total_dislikes' => max(0, $dislikes),
        'user_vote' => $user_vote
    ]);
}

// ============================================
// CHECK VOTE HANDLER
// ============================================

function handleCheckVote() {
    $post_id = isset($_GET['post_id']) ? sanitize($_GET['post_id']) : '';
    
    if (!$post_id) {
        respond('error', 'ID bài viết không hợp lệ');
    }
    
    // Check if post exists
    $post = getItem('posts', 'posts', $post_id);
    if (!$post) {
        respond('error', 'Bài viết không tồn tại');
    }
    
    $user_vote = null;
    
    // If user is logged in, check their vote
    if (isset($_SESSION['user_id'])) {
        $votes = getItems('votes', 'votes');
        
        foreach ($votes as $v) {
            if ($v['post_id'] === $post_id && $v['user_id'] === $_SESSION['user_id']) {
                $user_vote = $v['vote_type'];
                break;
            }
        }
    }
    
    respond('success', 'Kiểm tra vote thành công', [
        'post_id' => $post_id,
        'total_likes' => $post['likes'] ?? 0,
        'total_dislikes' => $post['dislikes'] ?? 0,
        'user_vote' => $user_vote
    ]);
}

// ============================================
// GET VOTE STATS
// ============================================

function handleGetVoteStats() {
    $post_id = isset($_GET['post_id']) ? sanitize($_GET['post_id']) : '';
    
    if (!$post_id) {
        respond('error', 'ID bài viết không hợp lệ');
    }
    
    $post = getItem('posts', 'posts', $post_id);
    if (!$post) {
        respond('error', 'Bài viết không tồn tại');
    }
    
    $votes = getItems('votes', 'votes');
    
    // Count votes for this post
    $post_votes = array_filter($votes, function($v) use ($post_id) {
        return $v['post_id'] === $post_id;
    });
    
    $likes = 0;
    $dislikes = 0;
    
    foreach ($post_votes as $v) {
        if ($v['vote_type'] === 'like') {
            $likes++;
        } else {
            $dislikes++;
        }
    }
    
    respond('success', 'Lấy thống kê vote thành công', [
        'post_id' => $post_id,
        'total_likes' => $likes,
        'total_dislikes' => $dislikes,
        'total_votes' => count($post_votes),
        'like_ratio' => count($post_votes) > 0 ? ($likes / count($post_votes)) * 100 : 0
    ]);
}

?>

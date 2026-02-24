<?php
/**
 * DevDA Blog System - Posts API
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch($action) {
    case 'create':
        handleCreate();
        break;
    case 'update':
        handleUpdate();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'get':
        handleGet();
        break;
    case 'list':
        handleList();
        break;
    case 'search':
        handleSearch();
        break;
    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

/**
 * Tạo bài viết mới
 */
function handleCreate() {
    global $db;
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Bạn cần đăng nhập'], 401);
    }
    
    $title = validateInput($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $category = validateInput($_POST['category'] ?? 'khác');
    $status = $_POST['status'] ?? 'draft'; // draft hoặc publish
    $tags = $_POST['tags'] ?? '';
    
    if (empty($title) || empty($content)) {
        jsonResponse(['error' => 'Tiêu đề và nội dung không được bỏ trống'], 400);
    }
    
    // Tạo slug từ tiêu đề
    $slug = createSlug($title);
    
    // Kiểm tra slug đã tồn tại
    $existing = $db->find('posts', 'slug', $slug);
    if ($existing) {
        $slug .= '-' . time();
    }
    
    // Xử lý tags
    $tagsArray = array_filter(array_map('trim', explode(',', $tags)));
    
    $post = $db->create('posts', [
        'title' => $title,
        'content' => $content,
        'slug' => $slug,
        'category' => $category,
        'tags' => $tagsArray,
        'author_id' => $_SESSION['user_id'],
        'status' => $status,
        'views' => 0,
        'image' => $_POST['image'] ?? null
    ]);
    
    if ($post) {
        $db->create('logs', [
            'action' => 'create_post',
            'user_id' => $_SESSION['user_id'],
            'details' => 'Tạo bài viết: ' . $title
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Bài viết đã được tạo',
            'post' => $post
        ], 201);
    }
    
    jsonResponse(['error' => 'Lỗi khi tạo bài viết'], 500);
}

/**
 * Cập nhật bài viết
 */
function handleUpdate() {
    global $db;
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Bạn cần đăng nhập'], 401);
    }
    
    $postId = $_POST['id'] ?? null;
    if (!$postId) {
        jsonResponse(['error' => 'ID bài viết không hợp lệ'], 400);
    }
    
    $post = $db->find('posts', 'id', $postId);
    if (!$post) {
        jsonResponse(['error' => 'Bài viết không tồn tại'], 404);
    }
    
    // Kiểm tra quyền
    if ($post['author_id'] !== $_SESSION['user_id'] && !isAdmin()) {
        jsonResponse(['error' => 'Bạn không có quyền sửa bài viết này'], 403);
    }
    
    $updateData = [];
    
    if (isset($_POST['title'])) {
        $updateData['title'] = validateInput($_POST['title']);
    }
    if (isset($_POST['content'])) {
        $updateData['content'] = $_POST['content'];
    }
    if (isset($_POST['category'])) {
        $updateData['category'] = validateInput($_POST['category']);
    }
    if (isset($_POST['status'])) {
        $updateData['status'] = $_POST['status'];
    }
    if (isset($_POST['tags'])) {
        $tagsArray = array_filter(array_map('trim', explode(',', $_POST['tags'])));
        $updateData['tags'] = $tagsArray;
    }
    
    $updated = $db->update('posts', $postId, $updateData);
    
    if ($updated) {
        $db->create('logs', [
            'action' => 'update_post',
            'user_id' => $_SESSION['user_id'],
            'details' => 'Sửa bài viết: ' . $updated['title']
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Bài viết đã được cập nhật',
            'post' => $updated
        ]);
    }
    
    jsonResponse(['error' => 'Lỗi khi cập nhật bài viết'], 500);
}

/**
 * Xoá bài viết
 */
function handleDelete() {
    global $db;
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Bạn cần đăng nhập'], 401);
    }
    
    $postId = $_GET['id'] ?? $_POST['id'] ?? null;
    if (!$postId) {
        jsonResponse(['error' => 'ID bài viết không hợp lệ'], 400);
    }
    
    $post = $db->find('posts', 'id', $postId);
    if (!$post) {
        jsonResponse(['error' => 'Bài viết không tồn tại'], 404);
    }
    
    // Kiểm tra quyền
    if ($post['author_id'] !== $_SESSION['user_id'] && !isAdmin()) {
        jsonResponse(['error' => 'Bạn không có quyền xoá bài viết này'], 403);
    }
    
    if ($db->delete('posts', $postId)) {
        // Xoá comments liên quan
        $comments = $db->filter('comments', function($c) use ($postId) {
            return $c['post_id'] === $postId;
        });
        
        foreach ($comments as $comment) {
            $db->delete('comments', $comment['id']);
        }
        
        // Xoá votes liên quan
        $votes = $db->filter('votes', function($v) use ($postId) {
            return $v['post_id'] === $postId;
        });
        
        foreach ($votes as $vote) {
            $db->delete('votes', $vote['id']);
        }
        
        $db->create('logs', [
            'action' => 'delete_post',
            'user_id' => $_SESSION['user_id'],
            'details' => 'Xoá bài viết: ' . $post['title']
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Bài viết đã được xoá']);
    }
    
    jsonResponse(['error' => 'Lỗi khi xoá bài viết'], 500);
}

/**
 * Lấy bài viết đơn
 */
function handleGet() {
    global $db;
    $postId = $_GET['id'] ?? null;
    $slug = $_GET['slug'] ?? null;
    
    $post = null;
    if ($postId) {
        $post = $db->find('posts', 'id', $postId);
    } elseif ($slug) {
        $post = $db->find('posts', 'slug', $slug);
    }
    
    if (!$post) {
        jsonResponse(['error' => 'Bài viết không tồn tại'], 404);
    }
    
    // Tăng lượt xem
    $db->update('posts', $post['id'], ['views' => ($post['views'] ?? 0) + 1]);
    
    // Lấy tác giả
    $author = $db->find('users', 'id', $post['author_id']);
    
    // Lấy comments
    $comments = $db->filter('comments', function($c) use ($post) {
        return $c['post_id'] === $post['id'] && $c['status'] !== 'hidden';
    });
    
    // Lấy votes
    $likes = $db->filter('votes', function($v) use ($post) {
        return $v['post_id'] === $post['id'] && $v['type'] === 'like';
    });
    
    $dislikes = $db->filter('votes', function($v) use ($post) {
        return $v['post_id'] === $post['id'] && $v['type'] === 'dislike';
    });
    
    jsonResponse([
        'post' => $post,
        'author' => [
            'id' => $author['id'],
            'username' => $author['username']
        ],
        'commentCount' => count($comments),
        'likes' => count($likes),
        'dislikes' => count($dislikes)
    ]);
}

/**
 * Danh sách bài viết
 */
function handleList() {
    global $db;
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $category = $_GET['category'] ?? null;
    $tag = $_GET['tag'] ?? null;
    
    $posts = $db->filter('posts', function($p) use ($category, $tag) {
        if ($p['status'] !== 'publish') return false;
        if ($category && $p['category'] !== $category) return false;
        if ($tag && !in_array($tag, $p['tags'] ?? [])) return false;
        return true;
    });
    
    // Sắp xếp theo ngày mới nhất
    usort($posts, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Phân trang
    $total = count($posts);
    $totalPages = ceil($total / $limit);
    $offset = ($page - 1) * $limit;
    $posts = array_slice($posts, $offset, $limit);
    
    jsonResponse([
        'posts' => $posts,
        'pagination' => [
            'current' => $page,
            'total' => $totalPages,
            'perPage' => $limit,
            'total' => $total
        ]
    ]);
}

/**
 * Tìm kiếm bài viết
 */
function handleSearch() {
    global $db;
    $q = validateInput($_GET['q'] ?? '');
    
    if (strlen($q) < 2) {
        jsonResponse(['error' => 'Từ khóa tìm kiếm ít nhất 2 ký tự'], 400);
    }
    
    $results = $db->filter('posts', function($p) use ($q) {
        if ($p['status'] !== 'publish') return false;
        return stripos($p['title'], $q) !== false || stripos($p['content'], $q) !== false;
    });
    
    jsonResponse(['results' => array_slice($results, 0, 20)]);
}

/**
 * Tạo slug từ tiêu đề
 */
function createSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
?>

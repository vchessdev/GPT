<?php
/**
 * devDA Blog System - Posts API
 * API xử lý CRUD bài viết
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// ============================================
// UTILITY FUNCTIONS
// ============================================

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

function sanitizeHTML($html) {
    // Allow basic HTML tags
    $allowed = '<h1><h2><h3><h4><h5><h6><p><br><strong><b><em><i><u><ul><li><ol><a><img><blockquote><code><pre>';
    return strip_tags($html, $allowed);
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

function logAction($action, $resource_id, $description) {
    $log = [
        'id' => uniqid('log_'),
        'user_id' => $_SESSION['user_id'],
        'action' => $action,
        'resource_id' => $resource_id,
        'resource_type' => 'post',
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
        handleCreatePost();
        break;
    
    case 'list':
        handleListPosts();
        break;
    
    case 'get':
        handleGetPost();
        break;
    
    case 'update':
        checkAuth();
        handleUpdatePost();
        break;
    
    case 'delete':
        checkAuth();
        handleDeletePost();
        break;
    
    case 'publish':
        checkAuth();
        handlePublishPost();
        break;
    
    case 'search':
        handleSearchPosts();
        break;
    
    case 'by-category':
        handleGetByCategory();
        break;
    
    case 'by-tag':
        handleGetByTag();
        break;
    
    case 'increment-views':
        handleIncrementViews();
        break;
    
    default:
        respond('error', 'Action không hợp lệ');
}

// ============================================
// CREATE POST
// ============================================

function handleCreatePost() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate inputs
    if (empty($input['title']) || empty($input['content'])) {
        respond('error', 'Tiêu đề và nội dung không được để trống');
    }
    
    $title = sanitize($input['title']);
    $content = sanitizeHTML($input['content']);
    $excerpt = sanitize($input['excerpt'] ?? substr(strip_tags($content), 0, 200));
    $category = sanitize($input['category'] ?? 'Khác');
    $tags = isset($input['tags']) ? array_map('sanitize', (array)$input['tags']) : [];
    $status = isset($input['status']) && $input['status'] === 'published' ? 'published' : 'draft';
    $featured_image = $input['featured_image'] ?? '';
    
    // Validate inputs length
    if (strlen($title) < 5 || strlen($title) > 255) {
        respond('error', 'Tiêu đề phải từ 5-255 ký tự');
    }
    
    if (strlen($content) < 20) {
        respond('error', 'Nội dung phải có ít nhất 20 ký tự');
    }
    
    // Create post
    $post = [
        'id' => uniqid('post_'),
        'author_id' => $_SESSION['user_id'],
        'title' => $title,
        'slug' => generateSlug($title),
        'content' => $content,
        'excerpt' => $excerpt,
        'featured_image' => $featured_image,
        'category' => $category,
        'tags' => $tags,
        'status' => $status,
        'views' => 0,
        'likes' => 0,
        'dislikes' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null
    ];
    
    // Save post
    if (!addItem('posts', 'posts', $post)) {
        respond('error', 'Lỗi khi lưu bài viết');
    }
    
    // Log action
    logAction('create_post', $post['id'], 'Tạo bài viết mới: ' . $title);
    
    respond('success', 'Tạo bài viết thành công', [
        'post_id' => $post['id'],
        'slug' => $post['slug'],
        'redirect' => '/blog/post.php?slug=' . $post['slug']
    ]);
}

// ============================================
// LIST POSTS
// ============================================

function handleListPosts() {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $status = isset($_GET['status']) ? sanitize($_GET['status']) : 'published';
    $sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'created_at';
    
    $posts = getItems('posts', 'posts');
    
    // Filter by status
    $posts = array_filter($posts, function($p) use ($status) {
        return $p['status'] === $status;
    });
    
    // Add author info
    $users = getItems('users', 'users');
    $users_map = [];
    foreach ($users as $user) {
        $users_map[$user['id']] = $user;
    }
    
    foreach ($posts as &$post) {
        if (isset($users_map[$post['author_id']])) {
            $post['author'] = [
                'id' => $users_map[$post['author_id']]['id'],
                'username' => $users_map[$post['author_id']]['username'],
                'avatar' => $users_map[$post['author_id']]['avatar']
            ];
        }
    }
    
    // Sort posts
    $posts = sortItems($posts, $sort, 'desc');
    
    // Paginate
    $result = paginate($posts, $page, POSTS_PER_PAGE);
    
    respond('success', 'Lấy danh sách bài viết thành công', $result);
}

// ============================================
// GET SINGLE POST
// ============================================

function handleGetPost() {
    $slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
    
    if (!$slug) {
        respond('error', 'Slug không hợp lệ');
    }
    
    $posts = getItems('posts', 'posts');
    $post = null;
    
    foreach ($posts as $p) {
        if ($p['slug'] === $slug) {
            $post = $p;
            break;
        }
    }
    
    if (!$post || $post['status'] !== 'published') {
        respond('error', 'Bài viết không tồn tại');
    }
    
    // Get author info
    $author = getItem('users', 'users', $post['author_id']);
    if ($author) {
        $post['author'] = [
            'id' => $author['id'],
            'username' => $author['username'],
            'avatar' => $author['avatar'],
            'bio' => $author['bio']
        ];
    }
    
    // Get comments count
    $comments = getItems('comments', 'comments');
    $post_comments = array_filter($comments, function($c) use ($post) {
        return $c['post_id'] === $post['id'] && $c['status'] === 'approved';
    });
    $post['comments_count'] = count($post_comments);
    
    respond('success', 'Lấy thông tin bài viết thành công', [
        'post' => $post
    ]);
}

// ============================================
// UPDATE POST
// ============================================

function handleUpdatePost() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['post_id'])) {
        respond('error', 'ID bài viết không hợp lệ');
    }
    
    $post = getItem('posts', 'posts', $input['post_id']);
    
    if (!$post) {
        respond('error', 'Bài viết không tồn tại');
    }
    
    // Check permission (owner or admin)
    if ($post['author_id'] !== $_SESSION['user_id'] && !isAdmin()) {
        respond('error', 'Bạn không có quyền sửa bài viết này');
    }
    
    $updates = [];
    
    if (isset($input['title'])) {
        $updates['title'] = sanitize($input['title']);
        $updates['slug'] = generateSlug($updates['title']);
    }
    
    if (isset($input['content'])) {
        $updates['content'] = sanitizeHTML($input['content']);
    }
    
    if (isset($input['excerpt'])) {
        $updates['excerpt'] = sanitize($input['excerpt']);
    }
    
    if (isset($input['category'])) {
        $updates['category'] = sanitize($input['category']);
    }
    
    if (isset($input['tags'])) {
        $updates['tags'] = array_map('sanitize', (array)$input['tags']);
    }
    
    if (isset($input['featured_image'])) {
        $updates['featured_image'] = $input['featured_image'];
    }
    
    // Update
    if (!updateItem('posts', 'posts', $post['id'], $updates)) {
        respond('error', 'Lỗi khi cập nhật bài viết');
    }
    
    logAction('update_post', $post['id'], 'Sửa bài viết: ' . ($updates['title'] ?? $post['title']));
    
    respond('success', 'Cập nhật bài viết thành công');
}

// ============================================
// DELETE POST
// ============================================

function handleDeletePost() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['post_id'])) {
        respond('error', 'ID bài viết không hợp lệ');
    }
    
    $post = getItem('posts', 'posts', $input['post_id']);
    
    if (!$post) {
        respond('error', 'Bài viết không tồn tại');
    }
    
    // Check permission
    if ($post['author_id'] !== $_SESSION['user_id'] && !isAdmin()) {
        respond('error', 'Bạn không có quyền xóa bài viết này');
    }
    
    // Delete post
    if (!deleteItem('posts', 'posts', $post['id'])) {
        respond('error', 'Lỗi khi xóa bài viết');
    }
    
    // Delete related comments
    $comments = getItems('comments', 'comments');
    $comments = array_filter($comments, function($c) use ($post) {
        return $c['post_id'] !== $post['id'];
    });
    
    $data = readJSON('comments');
    $data['comments'] = array_values($comments);
    writeJSON('comments', $data);
    
    // Delete related votes
    $votes = getItems('votes', 'votes');
    $votes = array_filter($votes, function($v) use ($post) {
        return $v['post_id'] !== $post['id'];
    });
    
    $data = readJSON('votes');
    $data['votes'] = array_values($votes);
    writeJSON('votes', $data);
    
    logAction('delete_post', $post['id'], 'Xóa bài viết: ' . $post['title']);
    
    respond('success', 'Xóa bài viết thành công');
}

// ============================================
// PUBLISH POST
// ============================================

function handlePublishPost() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['post_id'])) {
        respond('error', 'ID bài viết không hợp lệ');
    }
    
    $post = getItem('posts', 'posts', $input['post_id']);
    
    if (!$post) {
        respond('error', 'Bài viết không tồn tại');
    }
    
    // Check permission
    if ($post['author_id'] !== $_SESSION['user_id'] && !isAdmin()) {
        respond('error', 'Bạn không có quyền xuất bản bài viết này');
    }
    
    // Update status
    if (!updateItem('posts', 'posts', $post['id'], [
        'status' => 'published',
        'published_at' => date('Y-m-d H:i:s')
    ])) {
        respond('error', 'Lỗi khi xuất bản bài viết');
    }
    
    logAction('publish_post', $post['id'], 'Xuất bản bài viết: ' . $post['title']);
    
    respond('success', 'Xuất bản bài viết thành công');
}

// ============================================
// SEARCH POSTS
// ============================================

function handleSearchPosts() {
    $query = isset($_GET['q']) ? sanitize($_GET['q']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    if (strlen($query) < 2) {
        respond('error', 'Từ khoá tìm kiếm quá ngắn (tối thiểu 2 ký tự)');
    }
    
    $posts = getItems('posts', 'posts');
    
    // Filter by published status
    $posts = array_filter($posts, function($p) {
        return $p['status'] === 'published';
    });
    
    // Search
    $posts = searchItems($posts, ['title', 'content', 'excerpt', 'tags'], $query);
    
    // Sort
    $posts = sortItems($posts, 'created_at', 'desc');
    
    // Paginate
    $result = paginate($posts, $page, POSTS_PER_PAGE);
    
    respond('success', 'Tìm kiếm thành công', $result);
}

// ============================================
// GET BY CATEGORY
// ============================================

function handleGetByCategory() {
    $category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    if (!$category) {
        respond('error', 'Chuyên mục không hợp lệ');
    }
    
    $posts = getItems('posts', 'posts');
    
    // Filter
    $posts = array_filter($posts, function($p) use ($category) {
        return $p['status'] === 'published' && $p['category'] === $category;
    });
    
    // Sort
    $posts = sortItems($posts, 'created_at', 'desc');
    
    // Paginate
    $result = paginate($posts, $page, POSTS_PER_PAGE);
    
    respond('success', 'Lấy bài viết theo chuyên mục thành công', $result);
}

// ============================================
// GET BY TAG
// ============================================

function handleGetByTag() {
    $tag = isset($_GET['tag']) ? sanitize($_GET['tag']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    if (!$tag) {
        respond('error', 'Thẻ không hợp lệ');
    }
    
    $posts = getItems('posts', 'posts');
    
    // Filter by tag
    $posts = array_filter($posts, function($p) use ($tag) {
        return $p['status'] === 'published' && in_array($tag, $p['tags'] ?? []);
    });
    
    // Sort
    $posts = sortItems($posts, 'created_at', 'desc');
    
    // Paginate
    $result = paginate($posts, $page, POSTS_PER_PAGE);
    
    respond('success', 'Lấy bài viết theo thẻ thành công', $result);
}

// ============================================
// INCREMENT VIEWS
// ============================================

function handleIncrementViews() {
    $post_id = isset($_GET['post_id']) ? sanitize($_GET['post_id']) : '';
    
    if (!$post_id) {
        respond('error', 'ID bài viết không hợp lệ');
    }
    
    $post = getItem('posts', 'posts', $post_id);
    
    if (!$post) {
        respond('error', 'Bài viết không tồn tại');
    }
    
    // Increment views
    updateItem('posts', 'posts', $post_id, [
        'views' => ($post['views'] ?? 0) + 1
    ]);
    
    respond('success', 'Cập nhật lượt xem thành công', [
        'views' => ($post['views'] ?? 0) + 1
    ]);
}

?>

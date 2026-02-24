<?php
/**
 * devDA Blog System - Setup Script
 * Chạy script này một lần để tạo tài khoản admin đầu tiên
 * 
 * Cách sử dụng:
 * 1. Truy cập http://localhost/blog/setup.php
 * 2. Click "Tạo Admin"
 * 3. Xóa file setup.php sau khi xong
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_admin') {
        try {
            // Kiểm tra xem đã có admin chưa
            $users = getItems('users', 'users') ?? [];
            $has_admin = false;
            
            foreach ($users as $user) {
                if ($user['role'] === 'admin') {
                    $has_admin = true;
                    break;
                }
            }
            
            if ($has_admin) {
                $error = "❌ Đã tồn tại tài khoản admin. Không thể tạo thêm.";
            } else {
                // Tạo admin user
                $admin = [
                    'id' => 'user_admin_001',
                    'username' => 'admin',
                    'email' => 'admin@devda.undo.it',
                    'password' => password_hash('admin123', PASSWORD_BCRYPT),
                    'full_name' => 'Administrator',
                    'avatar' => '/blog/assets/images/default-avatar.jpg',
                    'role' => 'admin',
                    'status' => 'active',
                    'bio' => 'Quản trị viên hệ thống',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'last_login' => null,
                    'email_verified' => true
                ];
                
                if (addItem('users', 'users', $admin)) {
                    $result = "✅ Tạo tài khoản admin thành công!";
                } else {
                    $error = "❌ Lỗi khi tạo tài khoản admin";
                }
            }
        } catch (Exception $e) {
            $error = "❌ Lỗi: " . $e->getMessage();
        }
    }
    
    if ($action === 'create_sample') {
        try {
            // Tạo sample data
            
            // Sample posts
            $posts = [
                [
                    'id' => 'post_001',
                    'author_id' => 'user_admin_001',
                    'title' => 'Hướng dẫn học Toán hiệu quả',
                    'slug' => 'huong-dan-hoc-toan-hieu-qua',
                    'content' => '<h2>Cách học Toán hiệu quả</h2><p>Để học Toán tốt, bạn cần:</p><ul><li>Hiểu kỹ lý thuyết cơ bản</li><li>Làm nhiều bài tập</li><li>Tìm tài liệu tham khảo tốt</li></ul>',
                    'excerpt' => 'Bài viết về cách học Toán hiệu quả cho học sinh',
                    'featured_image' => '',
                    'category' => 'Kỹ Năng & Mẹo Vặt',
                    'tags' => ['toán', 'học tập', 'mẹo học'],
                    'status' => 'published',
                    'views' => 125,
                    'likes' => 8,
                    'dislikes' => 1,
                    'created_at' => date('Y-m-d H:i:s', time() - 86400 * 5),
                    'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 5),
                    'published_at' => date('Y-m-d H:i:s', time() - 86400 * 5)
                ],
                [
                    'id' => 'post_002',
                    'author_id' => 'user_admin_001',
                    'title' => '10 bí quyết ôn thi vào lớp 10',
                    'slug' => '10-bi-quyet-on-thi-vao-lop-10',
                    'content' => '<h2>Ôn thi hiệu quả</h2><p>Các bí quyết để đạt kết quả cao trong kỳ thi vào lớp 10...</p>',
                    'excerpt' => 'Những bí quyết giúp bạn ôn thi vào lớp 10 hiệu quả',
                    'featured_image' => '',
                    'category' => 'Đề Thi & Ôn Luyện',
                    'tags' => ['ôn thi', 'lớp 10', 'thi cử'],
                    'status' => 'published',
                    'views' => 256,
                    'likes' => 15,
                    'dislikes' => 2,
                    'created_at' => date('Y-m-d H:i:s', time() - 86400 * 3),
                    'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 3),
                    'published_at' => date('Y-m-d H:i:s', time() - 86400 * 3)
                ]
            ];
            
            $data = readJSON('posts') ?? [];
            if (!isset($data['posts'])) {
                $data['posts'] = [];
            }
            $data['posts'] = array_merge($data['posts'], $posts);
            writeJSON('posts', $data);
            
            $result = "✅ Tạo dữ liệu mẫu thành công!";
        } catch (Exception $e) {
            $error = "❌ Lỗi: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - devDA Blog System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .info-box {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.6;
        }
        .info-box strong {
            display: block;
            color: #333;
            margin-bottom: 8px;
        }
        .info-box code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #667eea;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #ccc;
            color: #333;
        }
        .btn-secondary:hover {
            background: #bbb;
        }
        .status-check {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .status-ok { color: #3c3; }
        .status-error { color: #c33; }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Setup devDA Blog</h1>

        <?php if ($result): ?>
            <div class="alert alert-success">
                <?php echo $result; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="status-check">
            <strong style="display: block; margin-bottom: 10px;">📋 Kiểm tra hệ thống:</strong>
            <div class="status-item">
                <span>PHP version</span>
                <span class="status-ok">✅ <?php echo phpversion(); ?></span>
            </div>
            <div class="status-item">
                <span>Data directory writable</span>
                <span class="<?php echo is_writable(DATA_DIR) ? 'status-ok' : 'status-error'; ?>">
                    <?php echo is_writable(DATA_DIR) ? '✅ Yes' : '❌ No'; ?>
                </span>
            </div>
            <div class="status-item">
                <span>Uploads directory writable</span>
                <span class="<?php echo is_writable(UPLOADS_DIR) ? 'status-ok' : 'status-error'; ?>">
                    <?php echo is_writable(UPLOADS_DIR) ? '✅ Yes' : '❌ No'; ?>
                </span>
            </div>
        </div>

        <div class="info-box">
            <strong>Thông tin đăng nhập mặc định:</strong>
            Email: <code>admin@devda.undo.it</code><br>
            Password: <code>admin123</code><br><br>
            <em style="color: #666;">⚠️ Hãy đổi mật khẩu sau khi đăng nhập</em>
        </div>

        <form method="POST">
            <button type="submit" name="action" value="create_admin" class="btn">
                ✅ Tạo Tài Khoản Admin
            </button>
        </form>

        <form method="POST">
            <button type="submit" name="action" value="create_sample" class="btn btn-secondary">
                📚 Tạo Dữ Liệu Mẫu
            </button>
        </form>

        <div class="warning">
            ⚠️ <strong>Quan trọng:</strong><br>
            Hãy xóa file setup.php sau khi hoàn thành cài đặt<br>
            <code>rm setup.php</code>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <a href="/blog/" style="color: #667eea; text-decoration: none; font-size: 14px;">← Quay lại trang chủ</a>
        </div>
    </div>
</body>
</html>

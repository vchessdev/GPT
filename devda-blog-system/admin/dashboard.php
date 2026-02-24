<?php
/**
 * devDA Blog System - Admin Dashboard
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /blog/admin/login.php');
    exit;
}

// Get statistics
$users = getItems('users', 'users');
$posts = getItems('posts', 'posts');
$comments = getItems('comments', 'comments');
$votes = getItems('votes', 'votes');
$logs = getItems('logs', 'logs');

// Count published posts
$published_posts = array_filter($posts, fn($p) => $p['status'] === 'published');
$draft_posts = array_filter($posts, fn($p) => $p['status'] === 'draft');

// Count by status
$active_users = array_filter($users, fn($u) => $u['status'] === 'active');
$banned_users = array_filter($users, fn($u) => $u['status'] === 'banned');

// Calculate statistics
$total_views = array_sum(array_map(fn($p) => $p['views'] ?? 0, $posts));
$total_likes = array_sum(array_map(fn($p) => $p['likes'] ?? 0, $posts));
$total_comments = count($comments);

// Recent logs (last 10)
$recent_logs = array_slice($logs, -10);
$recent_logs = array_reverse($recent_logs);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - devDA Blog System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: #1e3c72;
            color: white;
            padding: 20px;
            position: fixed;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-menu {
            list-style: none;
        }

        .nav-menu li {
            margin-bottom: 10px;
        }

        .nav-menu a {
            display: block;
            padding: 10px 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.2s;
        }

        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-menu a.active {
            background: #2a5298;
            color: white;
        }

        .user-profile {
            position: absolute;
            bottom: 20px;
            width: calc(100% - 40px);
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 13px;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        /* Dashboard grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
        }

        .stat-card.users {
            border-left-color: #667eea;
        }

        .stat-card.posts {
            border-left-color: #764ba2;
        }

        .stat-card.comments {
            border-left-color: #f093fb;
        }

        .stat-card.views {
            border-left-color: #4facfe;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .stat-detail {
            font-size: 12px;
            color: #666;
        }

        /* Table */
        .table-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9f9f9;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #666;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        tr:hover {
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #efe;
            color: #3c3;
        }

        .badge-warning {
            background: #ffeaa7;
            color: #d63031;
        }

        .badge-danger {
            background: #fee;
            color: #c33;
        }

        .badge-info {
            background: #e3f2fd;
            color: #1976d2;
        }

        .action-links {
            font-size: 13px;
        }

        .action-links a {
            color: #667eea;
            text-decoration: none;
            margin-right: 15px;
        }

        .action-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                padding: 15px;
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .user-profile {
                position: static;
                border: none;
                padding: 0;
                margin-top: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                🎓 devDA Admin
            </div>

            <ul class="nav-menu">
                <li><a href="/blog/admin/dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="/blog/admin/users.php">👥 Quản lý User</a></li>
                <li><a href="/blog/admin/posts.php">📝 Quản lý Bài Viết</a></li>
                <li><a href="/blog/admin/comments.php">💬 Quản lý Bình Luận</a></li>
                <li><a href="/blog/admin/files.php">📁 Quản lý File</a></li>
                <li><a href="/blog/admin/votes.php">⭐ Quản lý Vote</a></li>
                <li><a href="/blog/admin/logs.php">📋 Nhật ký hệ thống</a></li>
            </ul>

            <div class="user-profile">
                <div>Đăng nhập: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></div>
                <button class="logout-btn" onclick="logout()">Đăng Xuất</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>📊 Dashboard</h1>
                <p>Tổng quan thống kê hệ thống</p>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card users">
                    <div class="stat-label">Tổng Người Dùng</div>
                    <div class="stat-value"><?php echo count($users); ?></div>
                    <div class="stat-detail"><?php echo count($active_users); ?> hoạt động, <?php echo count($banned_users); ?> bị khóa</div>
                </div>

                <div class="stat-card posts">
                    <div class="stat-label">Bài Viết</div>
                    <div class="stat-value"><?php echo count($published_posts); ?></div>
                    <div class="stat-detail"><?php echo count($draft_posts); ?> nháp</div>
                </div>

                <div class="stat-card comments">
                    <div class="stat-label">Bình Luận</div>
                    <div class="stat-value"><?php echo $total_comments; ?></div>
                    <div class="stat-detail">Tất cả bài viết</div>
                </div>

                <div class="stat-card views">
                    <div class="stat-label">Lượt Xem</div>
                    <div class="stat-value"><?php echo number_format($total_views); ?></div>
                    <div class="stat-detail">Tổng cộng</div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="table-section">
                <h2 class="section-title">Hoạt động gần đây</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Hành động</th>
                            <th>Người dùng</th>
                            <th>Loại tài nguyên</th>
                            <th>Mô tả</th>
                            <th>IP Address</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logs as $log): ?>
                        <tr>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $log_user = getItem('users', 'users', $log['user_id']);
                                echo $log_user ? htmlspecialchars($log_user['username']) : 'Guest';
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($log['resource_type'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($log['description'] ?? ''); ?></td>
                            <td><code><?php echo htmlspecialchars($log['ip_address']); ?></code></td>
                            <td><?php echo date('d/m H:i', strtotime($log['timestamp'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function logout() {
            if (confirm('Bạn có chắc muốn đăng xuất?')) {
                fetch('/blog/api/auth.php?action=logout', {
                    method: 'POST'
                }).then(() => {
                    window.location.href = '/blog/';
                });
            }
        }
    </script>
</body>
</html>

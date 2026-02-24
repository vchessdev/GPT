<?php
/**
 * devDA Blog System - Admin Users Management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /blog/admin/login.php');
    exit;
}

$users = getItems('users', 'users');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$result = paginate($users, $page, 20);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý User - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        .sidebar {
            background: #1e3c72;
            color: white;
            padding: 20px;
            position: fixed;
            width: 250px;
            height: 100vh;
            overflow-y: auto;
        }
        .logo { font-size: 20px; font-weight: 600; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .nav-menu { list-style: none; }
        .nav-menu li { margin-bottom: 10px; }
        .nav-menu a {
            display: block;
            padding: 10px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .nav-menu a:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-menu a.active { background: #2a5298; color: white; }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f9f9f9;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        tr:hover { background: #fafafa; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-admin { background: #ffc107; color: white; }
        .badge-user { background: #17a2b8; color: white; }
        .badge-active { background: #28a745; color: white; }
        .badge-banned { background: #dc3545; color: white; }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
            transition: all 0.2s;
        }
        .btn-edit { background: #007bff; color: white; }
        .btn-edit:hover { background: #0056b3; }
        .btn-ban { background: #dc3545; color: white; }
        .btn-ban:hover { background: #c82333; }
        .btn-promote { background: #28a745; color: white; }
        .btn-promote:hover { background: #218838; }
        .pagination {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #667eea;
        }
        .pagination a:hover { background: #f0f0f0; }
        .pagination .active { background: #667eea; color: white; border-color: #667eea; }
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
            .sidebar { width: 100%; height: auto; position: static; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">🎓 devDA Admin</div>
            <ul class="nav-menu">
                <li><a href="/blog/admin/dashboard.php">📊 Dashboard</a></li>
                <li><a href="/blog/admin/users.php" class="active">👥 Quản lý User</a></li>
                <li><a href="/blog/admin/posts.php">📝 Quản lý Bài Viết</a></li>
                <li><a href="/blog/admin/comments.php">💬 Quản lý Bình Luận</a></li>
                <li><a href="/blog/admin/files.php">📁 Quản lý File</a></li>
                <li><a href="/blog/admin/logs.php">📋 Nhật ký</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1>👥 Quản lý Người dùng</h1>
                <p>Tổng cộng: <?php echo $result['total']; ?> người dùng</p>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th>Họ tên</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Đăng nhập lần cuối</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['items'] as $user): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user['role']; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $user['status']; ?>">
                                    <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Khóa'; ?>
                                </span>
                            </td>
                            <td><?php echo $user['last_login'] ? date('d/m H:i', strtotime($user['last_login'])) : 'Chưa login'; ?></td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                <button class="btn btn-promote" onclick="promoteUser('<?php echo $user['id']; ?>')">Promote</button>
                                <?php endif; ?>
                                
                                <?php if ($user['status'] === 'active'): ?>
                                <button class="btn btn-ban" onclick="banUser('<?php echo $user['id']; ?>')">Ban</button>
                                <?php else: ?>
                                <button class="btn btn-edit" onclick="unbanUser('<?php echo $user['id']; ?>')">Unban</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($result['pages'] > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" <?php echo $i == $result['page'] ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        async function banUser(userId) {
            if (!confirm('Bạn có chắc muốn khóa tài khoản này?')) return;
            
            const response = await fetch('/blog/api/users.php?action=ban', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            
            const data = await response.json();
            if (data.status === 'success') {
                alert('Đã khóa tài khoản');
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        }

        async function unbanUser(userId) {
            if (!confirm('Bạn có chắc muốn mở khóa tài khoản này?')) return;
            
            const response = await fetch('/blog/api/users.php?action=unban', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            
            const data = await response.json();
            if (data.status === 'success') {
                alert('Đã mở khóa tài khoản');
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        }

        async function promoteUser(userId) {
            if (!confirm('Bạn có chắc muốn nâng quyền user này lên Admin?')) return;
            
            const response = await fetch('/blog/api/users.php?action=promote', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            
            const data = await response.json();
            if (data.status === 'success') {
                alert('Đã nâng quyền user');
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        }
    </script>
</body>
</html>

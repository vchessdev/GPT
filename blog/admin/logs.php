<?php
require_once __DIR__ . '/../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs Hệ Thống - DevDA Blog Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
</head>
<body class="admin">
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">DevDA Admin</div>
            <nav class="admin-menu">
                <a href="<?php echo BASE_URL; ?>/admin/" class="menu-item">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="menu-item">Quản Lý User</a>
                <a href="<?php echo BASE_URL; ?>/admin/posts.php" class="menu-item">Quản Lý Bài Viết</a>
                <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="menu-item">Quản Lý Bình Luận</a>
                <a href="<?php echo BASE_URL; ?>/admin/files.php" class="menu-item">Quản Lý File</a>
                <a href="<?php echo BASE_URL; ?>/admin/votes.php" class="menu-item">Thống Kê Vote</a>
                <a href="<?php echo BASE_URL; ?>/admin/logs.php" class="menu-item active">Logs Hệ Thống</a>
                <hr>
                <a href="<?php echo BASE_URL; ?>" class="menu-item">Quay Về Blog</a>
                <a href="#" id="logoutAdminBtn" class="menu-item logout">Đăng Xuất</a>
            </nav>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Logs Hệ Thống</h1>
            </header>

            <section class="admin-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hành Động</th>
                            <th>Người Dùng</th>
                            <th>IP Address</th>
                            <th>Chi Tiết</th>
                            <th>Thời Gian</th>
                        </tr>
                    </thead>
                    <tbody id="logsTable">
                        <tr><td colspan="6">Đang tải...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        async function loadLogs() {
            try {
                const logsRes = await fetch('<?php echo BASE_URL; ?>/api/logs.php?action=list');
                const logsData = await logsRes.json();

                const logs = logsData.logs || [];

                let html = '';
                logs.forEach(log => {
                    html += `
                        <tr>
                            <td>${log.id}</td>
                            <td>${log.action}</td>
                            <td>${log.user_id || 'System'}</td>
                            <td>${log.ip || '-'}</td>
                            <td>${log.details}</td>
                            <td>${log.created_at}</td>
                        </tr>
                    `;
                });

                if (html === '') {
                    html = '<tr><td colspan="6">Chưa có log nào</td></tr>';
                }
                document.getElementById('logsTable').innerHTML = html;
            } catch (error) {
                console.error('Error loading logs:', error);
                document.getElementById('logsTable').innerHTML = '<tr><td colspan="6">Lỗi tải dữ liệu</td></tr>';
            }
        }

        // Logout
        document.getElementById('logoutAdminBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.href = '<?php echo BASE_URL; ?>/admin/login.php';
        });

        loadLogs();
        setInterval(loadLogs, 30000);
    </script>
</body>
</html>

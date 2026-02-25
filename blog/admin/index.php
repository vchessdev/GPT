<?php
require_once __DIR__ . '/../config.php';

// Kiểm tra admin login
if (!isLoggedIn() || !isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DevDA Blog</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
</head>
<body class="admin">
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">⚙️ Admin</div>
            <nav class="admin-menu">
                <a href="<?php echo BASE_URL; ?>/admin/" class="menu-item active">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="menu-item">👥 Quản Lý User</a>
                <a href="<?php echo BASE_URL; ?>/admin/posts.php" class="menu-item">📝 Quản Lý Bài</a>
                <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="menu-item">💬 Bình Luận</a>
                <a href="<?php echo BASE_URL; ?>/admin/files.php" class="menu-item">📁 File</a>
                <a href="<?php echo BASE_URL; ?>/admin/votes.php" class="menu-item">👍 Vote</a>
                <a href="<?php echo BASE_URL; ?>/admin/logs.php" class="menu-item">📋 Logs</a>
                <hr>
                <a href="<?php echo BASE_URL; ?>" class="menu-item">← Quay Về</a>
                <a href="#" id="logoutAdminBtn" class="menu-item logout">🚪 Đăng Xuất</a>
            </nav>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <div>
                    <h1>Dashboard</h1>
                    <p>Chào mừng, <span id="adminName"><?php echo htmlspecialchars($_SESSION['username']); ?></span></p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>/profile.php" class="btn btn-primary">👤 Hồ Sơ</a>
                </div>
            </header>

            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Người Dùng</h3>
                    <div class="stat-number" id="userCount">0</div>
                </div>

                <div class="stat-card">
                    <h3>Bài Viết</h3>
                    <div class="stat-number" id="postCount">0</div>
                </div>

                <div class="stat-card">
                    <h3>Bình Luận</h3>
                    <div class="stat-number" id="commentCount">0</div>
                </div>

                <div class="stat-card">
                    <h3>File</h3>
                    <div class="stat-number" id="fileCount">0</div>
                </div>

                <div class="stat-card">
                    <h3>Tổng Vote</h3>
                    <div class="stat-number" id="voteCount">0</div>
                </div>

                <div class="stat-card">
                    <h3>Tổng Lượt Xem</h3>
                    <div class="stat-number" id="viewCount">0</div>
                </div>
            </div>

            <section class="recent-logs">
                <h2>Hoạt Động Gần Đây</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Hành Động</th>
                            <th>Người Dùng</th>
                            <th>Chi Tiết</th>
                            <th>Thời Gian</th>
                        </tr>
                    </thead>
                    <tbody id="logsBody">
                        <tr><td colspan="4">Đang tải...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        // Tải thống kê
        async function loadStats() {
            try {
                const [usersRes, postsRes, commentsRes, filesRes, votesRes, logsRes] = await Promise.all([
                    fetch('<?php echo BASE_URL; ?>/api/users.php?action=list'),
                    fetch('<?php echo BASE_URL; ?>/api/posts.php?action=list'),
                    fetch('<?php echo BASE_URL; ?>/api/comments.php?action=list'),
                    fetch('<?php echo BASE_URL; ?>/api/files.php?action=list'),
                    fetch('<?php echo BASE_URL; ?>/api/votes.php?action=getVotes'),
                    fetch('<?php echo BASE_URL; ?>/api/logs.php?action=list')
                ]);

                const users = await usersRes.json();
                const posts = await postsRes.json();
                const comments = await commentsRes.json();
                const files = await filesRes.json();
                const votes = await votesRes.json();
                const logs = await logsRes.json();

                document.getElementById('userCount').textContent = users.users?.length || 0;
                document.getElementById('postCount').textContent = posts.posts?.length || 0;
                document.getElementById('commentCount').textContent = comments.comments?.length || 0;
                document.getElementById('fileCount').textContent = files.files?.length || 0;
                document.getElementById('voteCount').textContent = votes.votes?.length || 0;

                // Tính tổng views
                let totalViews = 0;
                if (posts.posts) {
                    totalViews = posts.posts.reduce((sum, p) => sum + (p.views || 0), 0);
                }
                document.getElementById('viewCount').textContent = totalViews;

                // Tải logs
                if (logs.logs && logs.logs.length > 0) {
                    let html = logs.logs.slice(0, 10).map(log => `
                        <tr>
                            <td>${log.action}</td>
                            <td>${log.user_id || 'System'}</td>
                            <td>${log.details}</td>
                            <td>${log.created_at}</td>
                        </tr>
                    `).join('');
                    document.getElementById('logsBody').innerHTML = html;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Logout
        document.getElementById('logoutAdminBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.href = '<?php echo BASE_URL; ?>/admin/login.php';
        });

        loadStats();
        // Refresh stats every 30 seconds
        setInterval(loadStats, 30000);
    </script>
</body>
</html>

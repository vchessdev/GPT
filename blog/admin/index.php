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
                <a href="<?php echo BASE_URL; ?>/admin/" class="menu-item active">
                    <span>📊</span>
                    <span class="text">Dashboard</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="menu-item">
                    <span>👥</span>
                    <span class="text">Users</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/posts.php" class="menu-item">
                    <span>📝</span>
                    <span class="text">Posts</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="menu-item">
                    <span>💬</span>
                    <span class="text">Comments</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/files.php" class="menu-item">
                    <span>📁</span>
                    <span class="text">Files</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/votes.php" class="menu-item">
                    <span>👍</span>
                    <span class="text">Votes</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/logs.php" class="menu-item">
                    <span>📋</span>
                    <span class="text">Logs</span>
                </a>
                <hr>
                <a href="<?php echo BASE_URL; ?>" class="menu-item">
                    <span>←</span>
                    <span class="text">Home</span>
                </a>
                <a href="#" id="logoutAdminBtn" class="menu-item logout">
                    <span>🚪</span>
                    <span class="text">Logout</span>
                </a>
            </nav>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <div>
                    <h1>⚡ Dashboard</h1>
                    <p>Xin chào, <strong id="adminName"><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                </div>
                <a href="<?php echo BASE_URL; ?>/profile.php" class="btn btn-primary">👤 Hồ Sơ</a>
            </header>

            <div class="stats-wrapper">
                <div class="stat-card card-blue">
                    <div class="card-icon">👥</div>
                    <div class="card-content">
                        <div class="card-label">Users</div>
                        <div class="card-number" id="userCount">0</div>
                    </div>
                </div>

                <div class="stat-card card-green">
                    <div class="card-icon">📝</div>
                    <div class="card-content">
                        <div class="card-label">Posts</div>
                        <div class="card-number" id="postCount">0</div>
                    </div>
                </div>

                <div class="stat-card card-purple">
                    <div class="card-icon">💬</div>
                    <div class="card-content">
                        <div class="card-label">Comments</div>
                        <div class="card-number" id="commentCount">0</div>
                    </div>
                </div>

                <div class="stat-card card-orange">
                    <div class="card-icon">📁</div>
                    <div class="card-content">
                        <div class="card-label">Files</div>
                        <div class="card-number" id="fileCount">0</div>
                    </div>
                </div>

                <div class="stat-card card-pink">
                    <div class="card-icon">👍</div>
                    <div class="card-content">
                        <div class="card-label">Votes</div>
                        <div class="card-number" id="voteCount">0</div>
                    </div>
                </div>

                <div class="stat-card card-cyan">
                    <div class="card-icon">👀</div>
                    <div class="card-content">
                        <div class="card-label">Views</div>
                        <div class="card-number" id="viewCount">0</div>
                    </div>
                </div>
            </div>

            <section class="recent-activity">
                <div class="section-header">
                    <h2>📊 Recent Activity</h2>
                    <span class="refresh-btn" onclick="loadStats()">🔄</span>
                </div>
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>User</th>
                            <th>Details</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="logsBody">
                        <tr><td colspan="4" style="text-align: center; padding: 20px;">Loading...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
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

                let totalViews = 0;
                if (posts.posts) {
                    totalViews = posts.posts.reduce((sum, p) => sum + (p.views || 0), 0);
                }
                document.getElementById('viewCount').textContent = totalViews;

                if (logs.logs && logs.logs.length > 0) {
                    let html = logs.logs.slice(0, 8).map(log => `
                        <tr>
                            <td><span class="badge">${log.action}</span></td>
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

        document.getElementById('logoutAdminBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.href = '<?php echo BASE_URL; ?>/admin/login.php';
        });

        loadStats();
        setInterval(loadStats, 30000);
    </script>
</body>
</html>

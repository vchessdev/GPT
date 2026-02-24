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
    <title>Thống Kê Vote - DevDA Blog Admin</title>
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
                <a href="<?php echo BASE_URL; ?>/admin/votes.php" class="menu-item active">Thống Kê Vote</a>
                <a href="<?php echo BASE_URL; ?>/admin/logs.php" class="menu-item">Logs Hệ Thống</a>
                <hr>
                <a href="<?php echo BASE_URL; ?>" class="menu-item">Quay Về Blog</a>
                <a href="#" id="logoutAdminBtn" class="menu-item logout">Đăng Xuất</a>
            </nav>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Thống Kê Vote</h1>
            </header>

            <section class="admin-section">
                <div class="stat-summary">
                    <div class="stat-box">
                        <h3>Tổng Vote</h3>
                        <div class="stat-value" id="totalVotes">0</div>
                    </div>
                    <div class="stat-box">
                        <h3>Like</h3>
                        <div class="stat-value" id="totalLikes">0</div>
                    </div>
                    <div class="stat-box">
                        <h3>Dislike</h3>
                        <div class="stat-value" id="totalDislikes">0</div>
                    </div>
                </div>

                <h3 style="margin-top: 30px;">Vote Theo Bài Viết</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Bài Viết</th>
                            <th>Tiêu Đề</th>
                            <th>Like</th>
                            <th>Dislike</th>
                            <th>Tổng Vote</th>
                        </tr>
                    </thead>
                    <tbody id="votesTable">
                        <tr><td colspan="5">Đang tải...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        async function loadVotes() {
            try {
                const [votesRes, postsRes] = await Promise.all([
                    fetch('<?php echo BASE_URL; ?>/api/votes.php?action=getVotes'),
                    fetch('<?php echo BASE_URL; ?>/api/posts.php?action=list')
                ]);

                const votesData = await votesRes.json();
                const postsData = await postsRes.json();

                const votes = votesData.votes || [];
                const posts = postsData.posts || [];

                // Calculate statistics
                let totalLikes = 0;
                let totalDislikes = 0;

                const votesByPost = {};
                votes.forEach(vote => {
                    if (!votesByPost[vote.post_id]) {
                        votesByPost[vote.post_id] = { likes: 0, dislikes: 0 };
                    }
                    if (vote.type === 'like') {
                        votesByPost[vote.post_id].likes++;
                        totalLikes++;
                    } else {
                        votesByPost[vote.post_id].dislikes++;
                        totalDislikes++;
                    }
                });

                // Update summary
                document.getElementById('totalVotes').textContent = votes.length;
                document.getElementById('totalLikes').textContent = totalLikes;
                document.getElementById('totalDislikes').textContent = totalDislikes;

                // Display votes by post
                let html = '';
                posts.forEach(post => {
                    const postVotes = votesByPost[post.id] || { likes: 0, dislikes: 0 };
                    html += `
                        <tr>
                            <td>${post.id}</td>
                            <td>${post.title}</td>
                            <td>${postVotes.likes}</td>
                            <td>${postVotes.dislikes}</td>
                            <td>${postVotes.likes + postVotes.dislikes}</td>
                        </tr>
                    `;
                });

                if (html === '') {
                    html = '<tr><td colspan="5">Chưa có vote nào</td></tr>';
                }
                document.getElementById('votesTable').innerHTML = html;
            } catch (error) {
                console.error('Error loading votes:', error);
                document.getElementById('votesTable').innerHTML = '<tr><td colspan="5">Lỗi tải dữ liệu</td></tr>';
            }
        }

        // Logout
        document.getElementById('logoutAdminBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.href = '<?php echo BASE_URL; ?>/admin/login.php';
        });

        loadVotes();
        setInterval(loadVotes, 30000);
    </script>
</body>
</html>

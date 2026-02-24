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
    <title>Quản Lý Bình Luận - DevDA Blog Admin</title>
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
                <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="menu-item active">Quản Lý Bình Luận</a>
                <a href="<?php echo BASE_URL; ?>/admin/files.php" class="menu-item">Quản Lý File</a>
                <hr>
                <a href="<?php echo BASE_URL; ?>" class="menu-item">Quay Về Blog</a>
                <a href="#" id="logoutAdminBtn" class="menu-item logout">Đăng Xuất</a>
            </nav>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Quản Lý Bình Luận</h1>
            </header>

            <section class="admin-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nội Dung</th>
                            <th>Người Dùng</th>
                            <th>Bài Viết</th>
                            <th>Status</th>
                            <th>Ngày Tạo</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="commentsTable">
                        <tr><td colspan="7">Đang tải...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        let allComments = [];
        let allUsers = [];
        let allPosts = [];

        async function loadComments() {
            try {
                const [commentsRes, usersRes, postsRes] = await Promise.all([
                    fetch('<?php echo BASE_URL; ?>/api/posts.php?action=list&limit=1000'),
                    fetch('<?php echo BASE_URL; ?>/api/users.php?action=list'),
                    fetch('<?php echo BASE_URL; ?>/api/posts.php?action=list&limit=1000')
                ]);

                const postsData = await postsRes.json();
                const usersData = await usersRes.json();

                allPosts = postsData.posts || [];
                allUsers = usersData.users || [];

                // Gather all comments from posts
                allComments = [];
                if (allPosts.length > 0) {
                    // Need to load comments from database directly via new endpoint
                    // For now, fetch from each post
                    for (const post of allPosts.slice(0, 10)) {
                        const res = await fetch('<?php echo BASE_URL; ?>/api/comments.php?action=list&post_id=' + post.id);
                        const data = await res.json();
                        if (data.comments) {
                            allComments = allComments.concat(data.comments.map(c => ({...c, post_id: post.id})));
                        }
                    }
                }

                displayComments(allComments);
            } catch (error) {
                console.error('Error loading comments:', error);
            }
        }

        function displayComments(comments) {
            let html = '';
            comments.forEach(comment => {
                const user = allUsers.find(u => u.id === comment.user_id);
                const post = allPosts.find(p => p.id === comment.post_id);
                html += `
                    <tr>
                        <td>${comment.id}</td>
                        <td>${comment.content.substring(0, 50)}...</td>
                        <td>${user?.username || 'Unknown'}</td>
                        <td>${post?.title || 'Unknown'}</td>
                        <td><span class="badge badge-${comment.status}">${comment.status}</span></td>
                        <td>${comment.created_at}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deleteComment('${comment.id}')">Xoá</button>
                        </td>
                    </tr>
                `;
            });
            document.getElementById('commentsTable').innerHTML = html || '<tr><td colspan="7">Không có bình luận nào</td></tr>';
        }

        async function deleteComment(commentId) {
            if (!confirm('Xoá bình luận này?')) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', commentId);

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/comments.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    loadComments();
                    alert('Bình luận đã được xoá');
                }
            } catch (error) {
                alert('Lỗi kết nối');
            }
        }

        document.getElementById('logoutAdminBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.href = '<?php echo BASE_URL; ?>/admin/login.php';
        });

        loadComments();
    </script>
</body>
</html>

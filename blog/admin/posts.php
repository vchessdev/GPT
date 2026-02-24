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
    <title>Quản Lý Bài Viết - DevDA Blog Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
</head>
<body class="admin">
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">DevDA Admin</div>
            <nav class="admin-menu">
                <a href="<?php echo BASE_URL; ?>/admin/" class="menu-item">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="menu-item">Quản Lý User</a>
                <a href="<?php echo BASE_URL; ?>/admin/posts.php" class="menu-item active">Quản Lý Bài Viết</a>
                <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="menu-item">Quản Lý Bình Luận</a>
                <a href="<?php echo BASE_URL; ?>/admin/files.php" class="menu-item">Quản Lý File</a>
                <hr>
                <a href="<?php echo BASE_URL; ?>" class="menu-item">Quay Về Blog</a>
                <a href="#" id="logoutAdminBtn" class="menu-item logout">Đăng Xuất</a>
            </nav>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Quản Lý Bài Viết</h1>
            </header>

            <section class="admin-section">
                <div class="section-toolbar">
                    <input type="text" id="searchInput" placeholder="Tìm bài..." class="search-box">
                    <button id="refreshBtn" class="btn btn-secondary">Refresh</button>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tiêu Đề</th>
                            <th>Tác Giả</th>
                            <th>Status</th>
                            <th>Lượt Xem</th>
                            <th>Ngày Tạo</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="postsTable">
                        <tr><td colspan="7">Đang tải...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        let allPosts = [];
        let allUsers = [];

        async function loadPosts() {
            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/posts.php?action=list&limit=100');
                const data = await res.json();
                allPosts = data.posts || [];

                const usersRes = await fetch('<?php echo BASE_URL; ?>/api/users.php?action=list');
                const usersData = await usersRes.json();
                allUsers = usersData.users || [];

                displayPosts(allPosts);
            } catch (error) {
                console.error('Error loading posts:', error);
            }
        }

        function displayPosts(posts) {
            let html = '';
            posts.forEach(post => {
                const author = allUsers.find(u => u.id === post.author_id);
                html += `
                    <tr>
                        <td>${post.id}</td>
                        <td>${post.title}</td>
                        <td>${author?.username || 'Unknown'}</td>
                        <td><span class="badge badge-${post.status}">${post.status}</span></td>
                        <td>${post.views || 0}</td>
                        <td>${post.created_at}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editPost('${post.id}')">Sửa</button>
                            <button class="btn btn-sm btn-danger" onclick="deletePost('${post.id}')">Xoá</button>
                        </td>
                    </tr>
                `;
            });
            document.getElementById('postsTable').innerHTML = html || '<tr><td colspan="7">Không có bài viết nào</td></tr>';
        }

        function editPost(postId) {
            const post = allPosts.find(p => p.id === postId);
            if (post) {
                window.open(`<?php echo BASE_URL; ?>/post.php?id=${postId}`, '_blank');
            }
        }

        async function deletePost(postId) {
            if (!confirm('Bạn chắc chắn muốn xoá bài viết này?')) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', postId);

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/posts.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    loadPosts();
                    alert('Bài viết đã được xoá');
                } else {
                    alert(data.error || 'Lỗi khi xoá bài');
                }
            } catch (error) {
                alert('Lỗi kết nối');
            }
        }

        document.getElementById('searchInput').addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            const filtered = allPosts.filter(p => 
                p.title.toLowerCase().includes(q)
            );
            displayPosts(filtered);
        });

        document.getElementById('refreshBtn').addEventListener('click', loadPosts);

        document.getElementById('logoutAdminBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.href = '<?php echo BASE_URL; ?>/admin/login.php';
        });

        loadPosts();
    </script>
</body>
</html>

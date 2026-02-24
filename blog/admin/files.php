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
    <title>Quản Lý File - DevDA Blog Admin</title>
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
                <a href="<?php echo BASE_URL; ?>/admin/files.php" class="menu-item active">Quản Lý File</a>
                <hr>
                <a href="<?php echo BASE_URL; ?>" class="menu-item">Quay Về Blog</a>
                <a href="#" id="logoutAdminBtn" class="menu-item logout">Đăng Xuất</a>
            </nav>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Quản Lý File</h1>
            </header>

            <section class="admin-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên File</th>
                            <th>Loại</th>
                            <th>Kích Thước</th>
                            <th>Người Upload</th>
                            <th>Ngày Upload</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="filesTable">
                        <tr><td colspan="7">Đang tải...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        let allFiles = [];
        let allUsers = [];

        async function loadFiles() {
            try {
                const [filesRes, usersRes] = await Promise.all([
                    fetch('<?php echo BASE_URL; ?>/api/files.php?action=list'),
                    fetch('<?php echo BASE_URL; ?>/api/users.php?action=list')
                ]);

                const filesData = await filesRes.json();
                const usersData = await usersRes.json();

                allFiles = filesData.files || [];
                allUsers = usersData.users || [];

                displayFiles(allFiles);
            } catch (error) {
                console.error('Error loading files:', error);
            }
        }

        function displayFiles(files) {
            let html = '';
            files.forEach(file => {
                const user = allUsers.find(u => u.id === file.uploader_id);
                html += `
                    <tr>
                        <td>${file.id}</td>
                        <td><a href="${file.path}" target="_blank">${file.original_name}</a></td>
                        <td>${file.type}</td>
                        <td>${(file.size / 1024 / 1024).toFixed(2)} MB</td>
                        <td>${user?.username || 'Unknown'}</td>
                        <td>${file.created_at}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deleteFile('${file.id}')">Xoá</button>
                        </td>
                    </tr>
                `;
            });
            document.getElementById('filesTable').innerHTML = html || '<tr><td colspan="7">Không có file nào</td></tr>';
        }

        async function deleteFile(fileId) {
            if (!confirm('Xoá file này?')) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', fileId);

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/files.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    loadFiles();
                    alert('File đã được xoá');
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

        loadFiles();
    </script>
</body>
</html>

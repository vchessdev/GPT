<?php
require_once __DIR__ . '/../config.php';

// Tạo file users API nếu chưa có
require_once API_DIR . '/users.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý User - DevDA Blog Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
</head>
<body class="admin">
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">DevDA Admin</div>
            <nav class="admin-menu">
                <a href="<?php echo BASE_URL; ?>/admin/" class="menu-item">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="menu-item active">Quản Lý User</a>
                <a href="<?php echo BASE_URL; ?>/admin/posts.php" class="menu-item">Quản Lý Bài Viết</a>
                <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="menu-item">Quản Lý Bình Luận</a>
                <a href="<?php echo BASE_URL; ?>/admin/files.php" class="menu-item">Quản Lý File</a>
                <hr>
                <a href="<?php echo BASE_URL; ?>" class="menu-item">Quay Về Blog</a>
                <a href="#" id="logoutAdminBtn" class="menu-item logout">Đăng Xuất</a>
            </nav>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Quản Lý Người Dùng</h1>
            </header>

            <section class="admin-section">
                <div class="section-toolbar">
                    <input type="text" id="searchInput" placeholder="Tìm user..." class="search-box">
                    <button id="refreshBtn" class="btn btn-secondary">Refresh</button>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Ngày Tạo</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="usersTable">
                        <tr><td colspan="7">Đang tải...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <div id="editModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Sửa User</h2>
            <form id="editForm">
                <input type="hidden" id="editId">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="editUsername" disabled>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="editEmail">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="editRole">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </form>
        </div>
    </div>

    <script>
        let allUsers = [];

        // Tải users
        async function loadUsers() {
            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/users.php?action=list');
                const data = await res.json();
                allUsers = data.users || [];
                displayUsers(allUsers);
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        // Hiển thị users
        function displayUsers(users) {
            let html = '';
            users.forEach(user => {
                html += `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td><span class="badge badge-${user.role}">${user.role}</span></td>
                        <td><span class="badge badge-${user.status}">${user.status}</span></td>
                        <td>${user.created_at}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editUser('${user.id}')">Sửa</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser('${user.id}')">Xoá</button>
                        </td>
                    </tr>
                `;
            });
            document.getElementById('usersTable').innerHTML = html || '<tr><td colspan="7">Không có user nào</td></tr>';
        }

        // Edit user
        function editUser(userId) {
            const user = allUsers.find(u => u.id === userId);
            if (user) {
                document.getElementById('editId').value = user.id;
                document.getElementById('editUsername').value = user.username;
                document.getElementById('editEmail').value = user.email;
                document.getElementById('editRole').value = user.role || 'user';
                document.getElementById('editStatus').value = user.status || 'active';
                document.getElementById('editModal').style.display = 'block';
            }
        }

        // Delete user
        async function deleteUser(userId) {
            if (!confirm('Bạn chắc chắn muốn xoá user này?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', userId);

                const res = await fetch('<?php echo BASE_URL; ?>/api/users.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    loadUsers();
                    alert('User đã được xoá');
                } else {
                    alert(data.error || 'Lỗi khi xoá user');
                }
            } catch (error) {
                alert('Lỗi kết nối');
            }
        }

        // Save edit
        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', document.getElementById('editId').value);
            formData.append('email', document.getElementById('editEmail').value);
            formData.append('role', document.getElementById('editRole').value);
            formData.append('status', document.getElementById('editStatus').value);

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/users.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    document.getElementById('editModal').style.display = 'none';
                    loadUsers();
                    alert('User đã được cập nhật');
                } else {
                    alert(data.error || 'Lỗi khi cập nhật user');
                }
            } catch (error) {
                alert('Lỗi kết nối');
            }
        });

        // Modal close
        document.querySelector('.close').addEventListener('click', () => {
            document.getElementById('editModal').style.display = 'none';
        });

        // Search
        document.getElementById('searchInput').addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            const filtered = allUsers.filter(u => 
                u.username.toLowerCase().includes(q) || 
                u.email.toLowerCase().includes(q)
            );
            displayUsers(filtered);
        });

        // Refresh
        document.getElementById('refreshBtn').addEventListener('click', loadUsers);

        // Logout
        document.getElementById('logoutAdminBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.href = '<?php echo BASE_URL; ?>/admin/login.php';
        });

        loadUsers();
    </script>
</body>
</html>

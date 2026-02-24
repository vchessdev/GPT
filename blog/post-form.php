<?php
require_once __DIR__ . '/config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Bài Mới - DevDA Blog</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>" class="logo">DevDA Blog</a>
            <ul class="nav-menu">
                <li><a href="<?php echo BASE_URL; ?>">Trang Chủ</a></li>
                <li><a href="<?php echo BASE_URL; ?>/post.php">Bài Viết</a></li>
                <li style="margin-left: auto;">
                    <button id="darkModeBtn" class="dark-mode-toggle" onclick="toggleDarkMode()">🌙</button>
                </li>
                <li id="userLinks">
                    <span id="username"></span>
                    <a href="<?php echo BASE_URL; ?>/post-form.php" class="btn btn-sm btn-primary">📝 Đăng Bài</a>
                    <a href="<?php echo BASE_URL; ?>">Bài Của Tôi</a>
                    <a href="<?php echo BASE_URL; ?>">Hồ Sơ</a>
                    <a href="#" id="logoutBtn">Đăng Xuất</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="auth-container" style="max-width: 700px; margin: 40px auto;">
            <div class="auth-box">
                <h1>✍️ Đăng Bài Viết Mới</h1>

                <form id="postForm">
                    <div class="form-group">
                        <label for="title">Tiêu Đề *</label>
                        <input type="text" id="title" name="title" required placeholder="Nhập tiêu đề bài viết">
                    </div>

                    <div class="form-group">
                        <label for="content">Nội Dung *</label>
                        <textarea id="content" name="content" rows="8" required placeholder="Nhập nội dung bài viết..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="category">Danh Mục</label>
                        <select id="category" name="category">
                            <option value="khác">Khác</option>
                            <option value="tutorials">Hướng Dẫn</option>
                            <option value="tips">Mẹo Hay</option>
                            <option value="news">Tin Tức</option>
                            <option value="education">Giáo Dục</option>
                            <option value="tech">Công Nghệ</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags (cách nhau bởi dấu phẩy)</label>
                        <input type="text" id="tags" name="tags" placeholder="ví dụ: php, laravel, web">
                    </div>

                    <div class="form-group">
                        <label for="status">Trạng Thái</label>
                        <select id="status" name="status">
                            <option value="draft">Bản Nháp</option>
                            <option value="publish">Xuất Bản</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="padding: 14px; font-size: 16px; margin-top: 10px;">📤 Đăng Bài</button>
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary btn-block" style="padding: 14px; font-size: 16px; margin-top: 10px; text-align: center;">← Quay Lại</a>

                    <div id="message" style="display:none; margin-top: 15px;"></div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 DevDA Blog System. All rights reserved.</p>
    </footer>

    <script>
        // Tài khoản info
        fetch('<?php echo BASE_URL; ?>/api/auth.php?action=check')
            .then(res => res.json())
            .then(data => {
                if (data.loggedIn) {
                    document.getElementById('username').textContent = data.user.username;
                }
            });

        // Đăng bài
        document.getElementById('postForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const title = document.getElementById('title').value;
            const content = document.getElementById('content').value;
            const category = document.getElementById('category').value;
            const tags = document.getElementById('tags').value;
            const status = document.getElementById('status').value;

            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('title', title);
            formData.append('content', content);
            formData.append('category', category);
            formData.append('tags', tags);
            formData.append('status', status);

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/posts.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                const messageDiv = document.getElementById('message');
                if (data.success || data.post_id) {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'success';
                    messageDiv.innerHTML = '✅ Bài viết đã được đăng thành công!<br>Chuyển hướng trong 2 giây...';
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_URL; ?>';
                    }, 2000);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'error';
                    messageDiv.textContent = data.error || 'Lỗi khi đăng bài';
                }
            } catch (error) {
                const messageDiv = document.getElementById('message');
                messageDiv.style.display = 'block';
                messageDiv.className = 'error';
                messageDiv.textContent = 'Lỗi kết nối: ' + error.message;
            }
        });

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.reload();
        });
    </script>
</body>
</html>

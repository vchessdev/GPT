<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevDA Blog - Hệ Thống Học Tập</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>" class="logo">DevDA Blog</a>
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo BASE_URL; ?>">Trang Chủ</a></li>
                <li><a href="<?php echo BASE_URL; ?>?page=posts">Bài Viết</a></li>
                <li><a href="<?php echo BASE_URL; ?>?page=search">Tìm Kiếm</a></li>
                <li id="authLinks">
                    <a href="<?php echo BASE_URL; ?>/login.php">Đăng Nhập</a>
                    <a href="<?php echo BASE_URL; ?>/register.php">Đăng Ký</a>
                </li>
                <li id="userLinks" style="display:none;">
                    <span id="username"></span>
                    <a href="<?php echo BASE_URL; ?>/post-form.php" class="btn btn-sm btn-primary" style="margin-left: 10px;">📝 Đăng Bài</a>
                    <a href="<?php echo BASE_URL; ?>?page=my-posts">Bài Của Tôi</a>
                    <a href="<?php echo BASE_URL; ?>?page=profile">Hồ Sơ</a>
                    <a href="#" id="logoutBtn">Đăng Xuất</a>
                </li>
                <li id="adminLinks" style="display:none;">
                    <a href="<?php echo BASE_URL; ?>/admin/">Admin</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div id="content">
            <section class="hero">
                <h1>Chào mừng đến DevDA Blog</h1>
                <p>Hệ thống chia sẻ bài viết, tài liệu và ôn thi cho học sinh</p>
                <div class="hero-buttons">
                    <a href="<?php echo BASE_URL; ?>?page=posts" class="btn btn-primary">Xem Bài Viết</a>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-secondary">Tham Gia Ngay</a>
                </div>
            </section>

            <section class="posts-preview">
                <h2>Bài Viết Mới Nhất</h2>
                <div id="postsContainer" class="posts-grid">
                    <p>Đang tải...</p>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 DevDA Blog System. All rights reserved.</p>
    </footer>

    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
    <script>
        // Kiểm tra trạng thái đăng nhập
        fetch('<?php echo BASE_URL; ?>/api/auth.php?action=check')
            .then(res => res.json())
            .then(data => {
                if (data.loggedIn) {
                    document.getElementById('authLinks').style.display = 'none';
                    document.getElementById('userLinks').style.display = 'block';
                    document.getElementById('username').textContent = data.user.username;
                    
                    if (data.user.role === 'admin') {
                        document.getElementById('adminLinks').style.display = 'block';
                    }
                }
            });

        // Tải bài viết
        fetch('<?php echo BASE_URL; ?>/api/posts.php?action=list')
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.posts.forEach(post => {
                    html += `
                        <article class="post-card">
                            <h3><a href="<?php echo BASE_URL; ?>/post.php?id=${post.id}">${post.title}</a></h3>
                            <p class="meta">${post.category} | ${post.created_at}</p>
                            <p>${post.content.substring(0, 150)}...</p>
                            <a href="<?php echo BASE_URL; ?>/post.php?id=${post.id}" class="read-more">Đọc Thêm →</a>
                        </article>
                    `;
                });
                document.getElementById('postsContainer').innerHTML = html || '<p>Chưa có bài viết nào</p>';
            });

        // Logout
        document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
            e.preventDefault();
            const res = await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.reload();
        });
    </script>
</body>
</html>

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
<body class="with-sidebar">
    <aside class="sidebar">
        <div class="logo">🚀 <span>DevDA</span></div>
        <nav class="sidebar-nav">
            <a href="<?php echo BASE_URL; ?>" class="sidebar-nav-item active">
                <span>🏠</span>
                <span class="text">Trang Chủ</span>
            </a>
            <a href="<?php echo BASE_URL; ?>?page=posts" class="sidebar-nav-item">
                <span>📚</span>
                <span class="text">Bài Viết</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/search.php" class="sidebar-nav-item">
                <span>🔍</span>
                <span class="text">Tìm Kiếm</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/profile.php" class="sidebar-nav-item" id="profileLink" style="display:none;">
                <span>👤</span>
                <span class="text">Hồ Sơ</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/post-form.php" class="sidebar-nav-item" id="postLink" style="display:none;">
                <span>✍️</span>
                <span class="text">Đăng Bài</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/leaderboard.php" class="sidebar-nav-item">
                <span>🏆</span>
                <span class="text">Xếp Hạng</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/" class="sidebar-nav-item" id="adminLink" style="display:none;">
                <span>⚙️</span>
                <span class="text">Admin</span>
            </a>
            <div style="border-top: 1px solid var(--border); margin: 12px 0;"></div>
            <button id="darkModeBtn" class="sidebar-nav-item" onclick="toggleDarkMode()" style="background: none; border: none; cursor: pointer; width: 100%; text-align: left;">
                <span>🌙</span>
                <span class="text">Dark Mode</span>
            </button>
            <a href="#" id="loginLink" class="sidebar-nav-item">
                <span>🔐</span>
                <span class="text">Đăng Nhập</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/register.php" class="sidebar-nav-item">
                <span>✨</span>
                <span class="text">Đăng Ký</span>
            </a>
            <a href="#" id="logoutBtn" class="sidebar-nav-item" style="display:none; color: var(--danger);">
                <span>🚪</span>
                <span class="text">Đăng Xuất</span>
            </a>
        </nav>
    </aside>

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

    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
    <script>
        // Kiểm tra trạng thái đăng nhập
        fetch('<?php echo BASE_URL; ?>/api/auth.php?action=check')
            .then(res => res.json())
            .then(data => {
                if (data.loggedIn) {
                    document.getElementById('loginLink').style.display = 'none';
                    document.getElementById('profileLink').style.display = 'block';
                    document.getElementById('postLink').style.display = 'block';
                    document.getElementById('logoutBtn').style.display = 'block';
                    
                    if (data.user.role === 'admin') {
                        document.getElementById('adminLink').style.display = 'block';
                    }
                } else {
                    document.getElementById('loginLink').href = '<?php echo BASE_URL; ?>/login.php';
                }
            });

        // Tải bài viết
        fetch('<?php echo BASE_URL; ?>/api/posts.php?action=list')
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (data.posts && data.posts.length > 0) {
                    data.posts.slice(0, 6).forEach(post => {
                        const thumbnail = post.thumbnail ? 
                            `<img src="${post.thumbnail}" alt="${post.title}" style="width: 100%; height: 200px; object-fit: cover; border-radius: var(--radius-md) var(--radius-md) 0 0; margin: -12px -12px 12px -12px;">` : 
                            `<div style="width: 100%; height: 200px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius: var(--radius-md) var(--radius-md) 0 0; margin: -12px -12px 12px -12px; display: flex; align-items: center; justify-content: center; font-size: 48px;">📄</div>`;
                        
                        html += `
                            <article class="card" style="overflow: hidden; display: flex; flex-direction: column;">
                                ${thumbnail}
                                <div style="padding: 12px;">
                                    <h3 style="margin-bottom: 8px;"><a href="<?php echo BASE_URL; ?>/post.php?id=${post.id}" style="color: var(--text-primary); text-decoration: none;">${post.title}</a></h3>
                                    <p class="stat-label" style="margin: 8px 0; font-size: 12px;">
                                        <span style="background: var(--bg-secondary); padding: 2px 6px; border-radius: 3px;">${post.category || 'Khác'}</span>
                                        <span style="color: var(--text-light);">• ${new Date(post.created_at).toLocaleDateString('vi-VN')}</span>
                                    </p>
                                    <p style="margin-top: 8px; margin-bottom: 12px; color: var(--text-secondary); font-size: 14px; flex: 1;">${post.content.substring(0, 100)}...</p>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid var(--border);">
                                        <span style="font-size: 12px; color: var(--text-light);">👀 ${post.views || 0} lượt xem</span>
                                        <a href="<?php echo BASE_URL; ?>/post.php?id=${post.id}" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 13px;">Đọc →</a>
                                    </div>
                                </div>
                            </article>
                        `;
                    });
                } else {
                    html = '<p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary);">Chưa có bài viết nào</p>';
                }
                document.getElementById('postsContainer').innerHTML = html;
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

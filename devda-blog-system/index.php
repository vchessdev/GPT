<?php
/**
 * devDA Blog System - Home Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Get published posts
$posts = getItems('posts', 'posts');
$published_posts = array_filter($posts, fn($p) => $p['status'] === 'published');
$published_posts = sortItems($published_posts, 'published_at', 'desc');
$featured_posts = array_slice($published_posts, 0, 6);

// Get categories
$categories = array_unique(array_map(fn($p) => $p['category'], $published_posts));
$categories = array_slice($categories, 0, 5);

// Get top tags
$all_tags = [];
foreach ($published_posts as $post) {
    $all_tags = array_merge($all_tags, $post['tags'] ?? []);
}
$tag_counts = array_count_values($all_tags);
arsort($tag_counts);
$top_tags = array_slice(array_keys($tag_counts), 0, 10);

// Get users
$users = getItems('users', 'users');
$users_map = [];
foreach ($users as $user) {
    $users_map[$user['id']] = $user;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>devDA - Blog & Website Học Tập</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        /* Header */
        header {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #666;
            font-size: 14px;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-outline {
            border: 1px solid #ddd;
            color: #666;
        }

        .btn-outline:hover {
            border-color: #667eea;
            color: #667eea;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Hero */
        .hero {
            text-align: center;
            margin-bottom: 50px;
            padding: 40px 0;
        }

        .hero h1 {
            font-size: 42px;
            margin-bottom: 20px;
            color: #333;
        }

        .hero p {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-box {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-box button {
            padding: 12px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        /* Posts Grid */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }

        .post-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .post-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        .post-body {
            padding: 20px;
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 12px;
            color: #999;
        }

        .post-category {
            display: inline-block;
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
        }

        .post-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .post-title a {
            text-decoration: none;
            color: inherit;
        }

        .post-title a:hover {
            color: #667eea;
        }

        .post-excerpt {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
            font-size: 12px;
            color: #999;
        }

        .post-author {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-author-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #ddd;
        }

        .post-stats {
            display: flex;
            gap: 15px;
        }

        /* Sidebar */
        .sidebar-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .category-list,
        .tag-list {
            list-style: none;
        }

        .category-list li,
        .tag-list li {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .category-list a,
        .tag-list a {
            text-decoration: none;
            color: #666;
            font-size: 14px;
            transition: color 0.2s;
        }

        .category-list a:hover,
        .tag-list a:hover {
            color: #667eea;
        }

        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            list-style: none;
        }

        .tag-list li {
            border: none;
            padding: 0;
        }

        .tag-list a {
            display: inline-block;
            background: #f0f0f0;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .tag-list a:hover {
            background: #667eea;
            color: white;
        }

        /* Footer */
        footer {
            background: #1e3c72;
            color: white;
            padding: 30px 20px;
            margin-top: 50px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 20px;
        }

        .footer-section h3 {
            margin-bottom: 15px;
            font-size: 14px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section li {
            margin-bottom: 8px;
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .nav-links {
                flex-direction: column;
                gap: 10px;
                width: 100%;
                text-align: center;
            }

            .hero h1 {
                font-size: 28px;
            }

            .posts-grid {
                grid-template-columns: 1fr;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="logo">🎓 devDA Blog</div>
            <ul class="nav-links">
                <li><a href="/blog/">Trang chủ</a></li>
                <li><a href="/blog/?browse=1">Khám phá</a></li>
                <li><a href="/blog/search.php">Tìm kiếm</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/blog/profile.php">👤 Hồ sơ</a></li>
                    <li><a href="/blog/create-post.php" class="btn btn-primary">Viết bài</a></li>
                    <li><button class="btn btn-outline" onclick="logout()">Đăng xuất</button></li>
                <?php else: ?>
                    <li><a href="/blog/login.php" class="btn btn-outline">Đăng nhập</a></li>
                    <li><a href="/blog/register.php" class="btn btn-primary">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Hero -->
        <section class="hero">
            <h1>🎓 Chào mừng đến devDA Blog</h1>
            <p>Nơi chia sẻ kiến thức, tài liệu học tập, và chuẩn bị thi cử</p>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm bài viết...">
                <button onclick="search()">Tìm kiếm</button>
            </div>
        </section>

        <!-- Featured Posts -->
        <section>
            <h2 style="margin-bottom: 20px; color: #333;">📚 Bài viết nổi bật</h2>
            <div class="posts-grid">
                <?php foreach ($featured_posts as $post): ?>
                <article class="post-card">
                    <div class="post-image">
                        <?php echo htmlspecialchars(substr($post['title'], 0, 30)); ?>...
                    </div>
                    <div class="post-body">
                        <div class="post-meta">
                            <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                            <span><?php echo date('d/m/Y', strtotime($post['published_at'])); ?></span>
                        </div>
                        <h3 class="post-title">
                            <a href="/blog/post.php?slug=<?php echo urlencode($post['slug']); ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        <p class="post-excerpt">
                            <?php echo htmlspecialchars(substr($post['excerpt'], 0, 100)); ?>...
                        </p>
                        <div class="post-footer">
                            <div class="post-author">
                                <?php if (isset($users_map[$post['author_id']])): ?>
                                    <div class="post-author-avatar"></div>
                                    <span><?php echo htmlspecialchars($users_map[$post['author_id']]['username']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="post-stats">
                                <span>👁️ <?php echo $post['views'] ?? 0; ?></span>
                                <span>❤️ <?php echo $post['likes'] ?? 0; ?></span>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Sidebar -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 50px;">
            <div></div>
            <aside>
                <!-- Categories -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">📂 Chuyên mục</h3>
                    <ul class="category-list">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/blog/?category=<?php echo urlencode($cat); ?>">
                                <?php echo htmlspecialchars($cat); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Tags -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">🏷️ Thẻ phổ biến</h3>
                    <ul class="tag-list">
                        <?php foreach ($top_tags as $tag): ?>
                        <li>
                            <a href="/blog/?tag=<?php echo urlencode($tag); ?>">
                                #<?php echo htmlspecialchars($tag); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>🎓 devDA Blog</h3>
                <ul>
                    <li><a href="/blog/">Trang chủ</a></li>
                    <li><a href="/blog/admin/login.php">Admin</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Tính năng</h3>
                <ul>
                    <li><a href="/blog/">Blog</a></li>
                    <li><a href="/blog/search.php">Tìm kiếm</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Về chúng tôi</h3>
                <ul>
                    <li><a href="#">Liên hệ</a></li>
                    <li><a href="#">Điều khoản</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 devDA Blog System. Mọi quyền được bảo lưu.</p>
        </div>
    </footer>

    <script>
        function logout() {
            if (confirm('Bạn có chắc muốn đăng xuất?')) {
                fetch('/blog/api/auth.php?action=logout', {
                    method: 'POST'
                }).then(() => {
                    window.location.href = '/blog/';
                });
            }
        }

        function search() {
            const query = document.getElementById('searchInput').value;
            if (query.trim()) {
                window.location.href = '/blog/search.php?q=' + encodeURIComponent(query);
            }
        }

        document.getElementById('searchInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                search();
            }
        });
    </script>
</body>
</html>

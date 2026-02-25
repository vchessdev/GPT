<?php
require_once __DIR__ . '/config.php';

$query = $_GET['q'] ?? '';
$db = Database::getInstance();
$results = [];

if ($query) {
    $allPosts = $db->read('posts');
    $results = array_filter($allPosts, function($post) use ($query) {
        $query = strtolower($query);
        return strpos(strtolower($post['title']), $query) !== false || 
               strpos(strtolower($post['content']), $query) !== false ||
               strpos(strtolower($post['category'] ?? ''), $query) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm Kiếm - DevDA Blog</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="with-sidebar">
    <aside class="sidebar">
        <div class="logo">🚀 <span>DevDA</span></div>
        <nav class="sidebar-nav">
            <a href="<?php echo BASE_URL; ?>" class="sidebar-nav-item">
                <span>🏠</span>
                <span class="text">Trang Chủ</span>
            </a>
            <a href="<?php echo BASE_URL; ?>?page=posts" class="sidebar-nav-item">
                <span>📚</span>
                <span class="text">Bài Viết</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/search.php" class="sidebar-nav-item active">
                <span>🔍</span>
                <span class="text">Tìm Kiếm</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/leaderboard.php" class="sidebar-nav-item">
                <span>🏆</span>
                <span class="text">Xếp Hạng</span>
            </a>
            <div style="border-top: 1px solid var(--border); margin: 12px 0;"></div>
            <button id="darkModeBtn" class="sidebar-nav-item" onclick="toggleDarkMode()" style="background: none; border: none; cursor: pointer; width: 100%; text-align: left;">
                <span>🌙</span>
                <span class="text">Dark Mode</span>
            </button>
        </nav>
    </aside>

    <main class="container">
        <div id="content">
            <div style="text-align: center; margin-bottom: 40px;">
                <h1 style="font-size: 36px; margin-bottom: 16px;">🔍 Tìm Kiếm</h1>
                <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-lg); max-width: 600px; margin: 0 auto;">
                    <form method="GET" style="display: flex; gap: 12px;">
                        <input 
                            type="text" 
                            name="q" 
                            placeholder="Tìm bài viết, tác giả, chủ đề..." 
                            value="<?php echo htmlspecialchars($query); ?>"
                            style="flex: 1;"
                        >
                        <button type="submit" class="btn btn-primary">Tìm</button>
                    </form>
                </div>
            </div>

            <?php if ($query): ?>
                <div style="margin-bottom: 24px;">
                    <p style="color: var(--text-secondary); font-size: 14px;">
                        Tìm thấy <strong><?php echo count($results); ?></strong> kết quả cho "<strong><?php echo htmlspecialchars($query); ?></strong>"
                    </p>
                </div>

                <?php if (empty($results)): ?>
                    <div style="text-align: center; padding: 60px 20px; background: var(--bg-secondary); border-radius: var(--radius-lg);">
                        <p style="font-size: 18px; color: var(--text-secondary); margin-bottom: 12px;">😕 Không tìm thấy bài viết nào</p>
                        <p style="color: var(--text-light); margin-bottom: 20px;">Thử tìm kiếm với từ khóa khác</p>
                        <a href="<?php echo BASE_URL; ?>/search.php" class="btn btn-secondary">Quay lại</a>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach ($results as $post): ?>
                        <article class="card" style="display: flex; flex-direction: column;">
                            <h3><a href="<?php echo BASE_URL; ?>/post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                            <p class="stat-label" style="margin-top: 8px;">
                                <?php echo $post['category'] ?? 'General'; ?> • <?php echo $post['created_at']; ?>
                            </p>
                            <p style="margin-top: 12px; color: var(--text-secondary); flex: 1;">
                                <?php echo substr(htmlspecialchars($post['content']), 0, 200); ?>...
                            </p>
                            <div style="margin-top: auto; display: flex; gap: 16px; font-size: 13px; color: var(--text-light);">
                                <span>👀 <?php echo $post['views'] ?? 0; ?> lượt xem</span>
                                <a href="<?php echo BASE_URL; ?>/post.php?id=<?php echo $post['id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 600;">Đọc tiếp →</a>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <p style="font-size: 18px; color: var(--text-secondary);">Nhập từ khóa để tìm kiếm bài viết</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>

<?php
require_once __DIR__ . '/config.php';

$query = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$db = Database::getInstance();
$results = [];

if ($query) {
    $allPosts = $db->read('posts');
    $results = array_filter($allPosts, function($post) use ($query, $category) {
        $query = strtolower($query);
        $matches = strpos(strtolower($post['title']), $query) !== false || 
               strpos(strtolower($post['content']), $query) !== false ||
               strpos(strtolower($post['category'] ?? ''), $query) !== false;
        
        if ($category && $matches) {
            return strtolower($post['category'] ?? '') === strtolower($category);
        }
        return $matches;
    });
    // Sort by newest first
    usort($results, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
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
            <div style="margin-bottom: 40px;">
                <h1 style="font-size: 36px; margin-bottom: 24px; text-align: center;">🔍 Tìm Kiếm Bài Viết</h1>
                <div style="background: var(--bg-secondary); padding: 28px; border-radius: var(--radius-lg); max-width: 700px; margin: 0 auto; border: 1px solid var(--border);">
                    <form method="GET" style="display: flex; flex-direction: column; gap: 16px;">
                        <div>
                            <input 
                                type="text" 
                                name="q" 
                                placeholder="Tìm bài viết, tác giả, chủ đề..." 
                                value="<?php echo htmlspecialchars($query); ?>"
                                style="width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg-primary); color: var(--text-primary);"
                                autofocus
                            >
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 120px; gap: 12px;">
                            <select name="category" style="padding: 12px 16px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg-primary); color: var(--text-primary);">
                                <option value="">Tất cả danh mục</option>
                                <option value="tutorials" <?php echo $category === 'tutorials' ? 'selected' : ''; ?>>Hướng Dẫn</option>
                                <option value="tips" <?php echo $category === 'tips' ? 'selected' : ''; ?>>Mẹo Hay</option>
                                <option value="news" <?php echo $category === 'news' ? 'selected' : ''; ?>>Tin Tức</option>
                                <option value="education" <?php echo $category === 'education' ? 'selected' : ''; ?>>Giáo Dục</option>
                                <option value="tech" <?php echo $category === 'tech' ? 'selected' : ''; ?>>Công Nghệ</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">🔍 Tìm</button>
                        </div>
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
                    <div style="text-align: center; padding: 80px 20px;">
                        <div style="font-size: 64px; margin-bottom: 16px;">😕</div>
                        <p style="font-size: 18px; color: var(--text-secondary); margin-bottom: 12px;">Không tìm thấy bài viết nào</p>
                        <p style="color: var(--text-light); margin-bottom: 24px;">Thử tìm kiếm với từ khóa hoặc danh mục khác</p>
                        <a href="<?php echo BASE_URL; ?>/search.php" class="btn btn-secondary">← Quay lại trang tìm kiếm</a>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        <?php foreach ($results as $post): ?>
                        <article class="card" style="display: flex; flex-direction: column; overflow: hidden;">
                            <?php if (!empty($post['thumbnail'])): ?>
                                <img src="<?php echo htmlspecialchars($post['thumbnail']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 180px; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 180px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center; font-size: 40px;">📄</div>
                            <?php endif; ?>
                            <div style="padding: 16px; flex: 1; display: flex; flex-direction: column;">
                                <h3 style="margin: 0 0 8px 0;">
                                    <a href="<?php echo BASE_URL; ?>/post.php?id=<?php echo $post['id']; ?>" style="color: var(--text-primary); text-decoration: none; line-height: 1.4;">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h3>
                                <p class="stat-label" style="margin: 8px 0; font-size: 12px;">
                                    <span style="background: var(--bg-secondary); padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                        <?php echo $post['category'] ?? 'Khác'; ?>
                                    </span>
                                    <span style="color: var(--text-light);">• <?php echo date('d/m/Y', strtotime($post['created_at'])); ?></span>
                                </p>
                                <p style="margin-top: 8px; color: var(--text-secondary); font-size: 14px; flex: 1; line-height: 1.5;">
                                    <?php echo substr(htmlspecialchars(strip_tags($post['content'])), 0, 150); ?>...
                                </p>
                                <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid var(--border);">
                                    <span style="font-size: 12px; color: var(--text-light);">👀 <?php echo $post['views'] ?? 0; ?> lượt xem</span>
                                    <a href="<?php echo BASE_URL; ?>/post.php?id=<?php echo $post['id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 13px;">Đọc tiếp →</a>
                                </div>
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

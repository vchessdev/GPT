<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Xếp Hạng - DevDA Blog</title>
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
            <a href="<?php echo BASE_URL; ?>/search.php" class="sidebar-nav-item">
                <span>🔍</span>
                <span class="text">Tìm Kiếm</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/leaderboard.php" class="sidebar-nav-item active">
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
                <h1 style="font-size: 36px; margin-bottom: 8px;">🏆 Bảng Xếp Hạng</h1>
                <p style="color: var(--text-secondary);">Những thành viên xuất sắc nhất của cộng đồng</p>
            </div>

            <div class="leaderboard-list" id="leaderboardList">
                <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    Đang tải...
                </div>
            </div>
        </div>
    </main>

    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
    <script>
        async function loadLeaderboard() {
            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/profiles.php?action=getLeaderboard&limit=50');
                const data = await res.json();
                
                if (!data.leaderboard || data.leaderboard.length === 0) {
                    document.getElementById('leaderboardList').innerHTML = `
                        <p style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            Chưa có dữ liệu xếp hạng
                        </p>
                    `;
                    return;
                }

                let html = '';
                data.leaderboard.forEach((user, index) => {
                    const rank = index + 1;
                    let rankClass = '';
                    let medal = '';
                    
                    if (rank === 1) {
                        rankClass = 'gold';
                        medal = '🥇';
                    } else if (rank === 2) {
                        rankClass = 'silver';
                        medal = '🥈';
                    } else if (rank === 3) {
                        rankClass = 'bronze';
                        medal = '🥉';
                    }

                    const score = (user.posts_count || 0) * 10 + (user.followers_count || 0) * 5;
                    
                    html += `
                        <div class="leaderboard-item">
                            <div class="leaderboard-rank ${rankClass}">${medal} #${rank}</div>
                            <div class="leaderboard-user">
                                <div class="leaderboard-username">${user.username || 'Unknown'}</div>
                                <div class="leaderboard-score">
                                    📝 ${user.posts_count || 0} bài | 👥 ${user.followers_count || 0} followers
                                </div>
                            </div>
                            <div class="leaderboard-points">${score} điểm</div>
                        </div>
                    `;
                });

                document.getElementById('leaderboardList').innerHTML = html;
            } catch (error) {
                console.error('Error loading leaderboard:', error);
                document.getElementById('leaderboardList').innerHTML = `
                    <p style="text-align: center; padding: 40px; color: var(--text-secondary);">
                        Có lỗi khi tải dữ liệu
                    </p>
                `;
            }
        }

        loadLeaderboard();
    </script>
</body>
</html>

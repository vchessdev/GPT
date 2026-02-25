<?php
require_once __DIR__ . '/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance();
$currentUser = $db->find('users', 'id', $userId);
$userProfile = $db->find('profiles', 'user_id', $userId);
$userPosts = $db->filter('posts', function($post) use ($userId) {
    return (int)$post['user_id'] === (int)$userId;
});

if (!$userProfile) {
    $userProfile = [
        'user_id' => $userId,
        'bio' => '',
        'avatar' => null,
        'status' => 'online',
        'followers_count' => 0,
        'following_count' => 0,
        'posts_count' => count($userPosts),
        'achievements' => [],
        'created_at' => date('Y-m-d H:i:s')
    ];
}

// Count total views
$totalViews = array_reduce($userPosts, function($sum, $post) {
    return $sum + ($post['views'] ?? 0);
}, 0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($currentUser['username']); ?> - DevDA Blog</title>
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
                <span>��</span>
                <span class="text">Bài Viết</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/search.php" class="sidebar-nav-item">
                <span>🔍</span>
                <span class="text">Tìm Kiếm</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/profile.php" class="sidebar-nav-item active">
                <span>👤</span>
                <span class="text">Hồ Sơ</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/post-form.php" class="sidebar-nav-item">
                <span>✍️</span>
                <span class="text">Đăng Bài</span>
            </a>
            <?php if (isAdmin()): ?>
            <a href="<?php echo BASE_URL; ?>/admin/" class="sidebar-nav-item">
                <span>⚙️</span>
                <span class="text">Admin</span>
            </a>
            <?php endif; ?>
            <div style="border-top: 1px solid var(--border); margin: 12px 0;"></div>
            <button id="darkModeBtn" class="sidebar-nav-item" onclick="toggleDarkMode()" style="background: none; border: none; cursor: pointer; width: 100%; text-align: left;">
                <span>🌙</span>
                <span class="text">Dark Mode</span>
            </button>
            <a href="#" id="logoutBtn" class="sidebar-nav-item" style="color: var(--danger);">
                <span>🚪</span>
                <span class="text">Đăng Xuất</span>
            </a>
        </nav>
    </aside>

    <main class="container">
        <div id="content">
            <!-- Profile Header -->
            <div class="profile-card">
                <div class="profile-avatar"><?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?></div>
                <h1 class="profile-name"><?php echo htmlspecialchars($currentUser['username']); ?></h1>
                
                <div class="profile-status <?php echo strtolower($userProfile['status']); ?>">
                    <span id="statusBadge">🟢 Online</span>
                </div>

                <p style="color: var(--text-secondary); margin: 16px 0; font-size: 14px;" id="bioText">
                    <?php echo htmlspecialchars($userProfile['bio'] ?? 'Chưa có tiểu sử'); ?>
                </p>

                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($userPosts); ?></div>
                        <div class="stat-label">Bài Viết</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $totalViews; ?></div>
                        <div class="stat-label">Lượt Xem</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $userProfile['followers_count']; ?></div>
                        <div class="stat-label">Followers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $userProfile['following_count']; ?></div>
                        <div class="stat-label">Following</div>
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: center;">
                    <button onclick="editProfile()" class="btn btn-primary">✏️ Chỉnh Sửa Hồ Sơ</button>
                    <button onclick="changeStatus()" class="btn btn-secondary">💬 Thay Đổi Status</button>
                </div>
            </div>

            <!-- Achievements Section -->
            <section style="margin-top: 40px;">
                <h2 style="font-size: 20px; margin-bottom: 20px;">🏆 Thành Tích & Huy Chương</h2>
                <div class="achievements-grid">
                    <div class="badge" title="Người Đăng Ký Sớm">
                        <span>🎉</span>
                        <span class="badge-label">Early Bird</span>
                    </div>
                    <div class="badge" title="5 Bài Viết">
                        <span>📝</span>
                        <span class="badge-label">Blogger</span>
                    </div>
                    <div class="badge" title="100 Lượt Xem">
                        <span>👀</span>
                        <span class="badge-label">Popular</span>
                    </div>
                    <div class="badge" title="10 Followers">
                        <span>⭐</span>
                        <span class="badge-label">Influencer</span>
                    </div>
                    <div class="badge" title="Giúp Đỡ Cộng Đồng">
                        <span>🤝</span>
                        <span class="badge-label">Helper</span>
                    </div>
                    <div class="badge" title="Đóng Góp Cao">
                        <span>🚀</span>
                        <span class="badge-label">Contributor</span>
                    </div>
                </div>
            </section>

            <!-- User Posts -->
            <section style="margin-top: 40px;">
                <h2 style="font-size: 20px; margin-bottom: 20px;">📚 Bài Viết Của Tôi (<?php echo count($userPosts); ?>)</h2>
                <div class="posts-grid">
                    <?php if (empty($userPosts)): ?>
                        <p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 40px;">
                            Bạn chưa đăng bài viết nào. <a href="<?php echo BASE_URL; ?>/post-form.php" style="color: var(--primary); font-weight: 600;">Đăng bài đầu tiên</a>
                        </p>
                    <?php else: ?>
                        <?php foreach ($userPosts as $post): ?>
                        <article class="card">
                            <h3><a href="<?php echo BASE_URL; ?>/post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                            <p class="stat-label" style="margin-top: 8px;"><?php echo $post['category']; ?> | <?php echo $post['created_at']; ?></p>
                            <p style="margin-top: 12px; color: var(--text-secondary);"><?php echo substr(htmlspecialchars($post['content']), 0, 150); ?>...</p>
                            <div style="margin-top: 12px; display: flex; gap: 16px; font-size: 13px; color: var(--text-light);">
                                <span>👀 <?php echo $post['views'] ?? 0; ?></span>
                                <span>💬 <?php echo count(array_filter($db->read('comments'), function($c) use ($post) { return $c['post_id'] === $post['id']; })); ?></span>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <div class="modal-header">
                <h2 class="modal-title">Chỉnh Sửa Hồ Sơ</h2>
            </div>
            <form id="editForm">
                <label style="display: block; margin-bottom: 16px; font-weight: 600; font-size: 14px;">Tiểu Sử</label>
                <textarea id="bioInput" name="bio" placeholder="Nhập tiểu sử của bạn..." style="height: 100px; resize: vertical;"></textarea>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">💾 Lưu Thay Đổi</button>
            </form>
        </div>
    </div>

    <!-- Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeStatusModal()">&times;</button>
            <div class="modal-header">
                <h2 class="modal-title">Thay Đổi Trạng Thái</h2>
            </div>
            <form id="statusForm">
                <div style="display: flex; gap: 12px; flex-direction: column;">
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 12px; border-radius: var(--radius-md); border: 2px solid var(--border); transition: var(--transition-fast);" class="status-option">
                        <input type="radio" name="status" value="online" checked> 🟢 Online
                    </label>
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 12px; border-radius: var(--radius-md); border: 2px solid var(--border); transition: var(--transition-fast);" class="status-option">
                        <input type="radio" name="status" value="busy"> 🔴 Bận
                    </label>
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 12px; border-radius: var(--radius-md); border: 2px solid var(--border); transition: var(--transition-fast);" class="status-option">
                        <input type="radio" name="status" value="away"> 🟡 Away
                    </label>
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 12px; border-radius: var(--radius-md); border: 2px solid var(--border); transition: var(--transition-fast);" class="status-option">
                        <input type="radio" name="status" value="offline"> ⚫ Offline
                    </label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">💾 Lưu</button>
            </form>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
    <script>
        // Load profile data
        async function loadProfile() {
            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/profiles.php?action=get&user_id=<?php echo $userId; ?>');
                const profile = await res.json();
                
                // Update bio
                document.getElementById('bioText').textContent = profile.bio || 'Chưa có tiểu sự';
                document.getElementById('bioInput').value = profile.bio || '';
                
                // Update status badge
                const statusMap = {
                    'online': '🟢 Online',
                    'busy': '🔴 Bận',
                    'away': '🟡 Away',
                    'offline': '⚫ Offline'
                };
                document.getElementById('statusBadge').textContent = statusMap[profile.status] || '🟢 Online';
            } catch (error) {
                console.error('Error loading profile:', error);
            }
        }

        function editProfile() {
            document.getElementById('editModal').classList.add('active');
        }

        function changeStatus() {
            document.getElementById('statusModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.remove('active');
        }

        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const bio = document.getElementById('bioInput').value;
            
            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/profiles.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ bio: bio })
                });
                
                if (res.ok) {
                    document.getElementById('bioText').textContent = bio || 'Chưa có tiểu sử';
                    closeModal();
                    alert('Cập nhật hồ sơ thành công!');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
            }
        });

        document.getElementById('statusForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const status = document.querySelector('input[name="status"]:checked').value;
            
            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/profiles.php?action=setStatus', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'status=' + status
                });
                
                if (res.ok) {
                    const statusMap = {
                        'online': '🟢 Online',
                        'busy': '🔴 Bận',
                        'away': '🟡 Away',
                        'offline': '⚫ Offline'
                    };
                    document.getElementById('statusBadge').textContent = statusMap[status];
                    closeStatusModal();
                    alert('Cập nhật trạng thái thành công!');
                }
            } catch (error) {
                console.error('Error updating status:', error);
            }
        });

        // Logout
        document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
            e.preventDefault();
            const res = await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.href = '<?php echo BASE_URL; ?>/login.php';
        });

        loadProfile();
    </script>
</body>
</html>

<?php
require_once __DIR__ . '/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Bài - DevDA Blog</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
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
            <a href="<?php echo BASE_URL; ?>/profile.php" class="sidebar-nav-item">
                <span>👤</span>
                <span class="text">Hồ Sơ</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/post-form.php" class="sidebar-nav-item active">
                <span>✍️</span>
                <span class="text">Đăng Bài</span>
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
            <a href="#" id="logoutBtn" class="sidebar-nav-item" style="color: var(--danger);">
                <span>🚪</span>
                <span class="text">Đăng Xuất</span>
            </a>
        </nav>
    </aside>

    <main class="container">
        <div id="content">
            <div style="max-width: 900px; margin: 0 auto;">
                <h1 style="font-size: 32px; margin-bottom: 8px;">✍️ Đăng Bài Viết Mới</h1>
                <p style="color: var(--text-secondary); margin-bottom: 32px;">Chia sẻ kiến thức của bạn với cộng đồng</p>

                <form id="postForm" class="card" style="padding: 32px;">
                    <!-- Title -->
                    <div style="margin-bottom: 24px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Tiêu Đề *</label>
                        <input type="text" id="title" name="title" required placeholder="Nhập tiêu đề bài viết..." style="font-size: 16px; padding: 12px;">
                    </div>

                    <!-- Thumbnail -->
                    <div style="margin-bottom: 24px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Thumbnail</label>
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            <div style="flex: 1;">
                                <input type="url" id="thumbnail" name="thumbnail" placeholder="URL hình ảnh (VD: https://...)">
                                <p style="font-size: 12px; color: var(--text-light); margin-top: 6px;">Hoặc tải lên:</p>
                                <input type="file" id="thumbnailFile" accept="image/*" style="margin-top: 6px;">
                            </div>
                            <div id="thumbnailPreview" style="width: 120px; height: 120px; background: var(--bg-secondary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--text-light); flex-shrink: 0;">
                                📷
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Danh Mục</label>
                            <select id="category" name="category" style="width: 100%;">
                                <option value="khác">Khác</option>
                                <option value="tutorials">Hướng Dẫn</option>
                                <option value="tips">Mẹo Hay</option>
                                <option value="news">Tin Tức</option>
                                <option value="education">Giáo Dục</option>
                                <option value="tech">Công Nghệ</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Trạng Thái</label>
                            <select id="status" name="status" style="width: 100%;">
                                <option value="draft">Bản Nháp</option>
                                <option value="publish">Xuất Bản</option>
                            </select>
                        </div>
                    </div>

                    <!-- Content Editor -->
                    <div style="margin-bottom: 24px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nội Dung *</label>
                        <textarea id="content" name="content" required placeholder="Nhập nội dung bài viết..." style="min-height: 400px;"></textarea>
                    </div>

                    <!-- Tags -->
                    <div style="margin-bottom: 24px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Tags</label>
                        <input type="text" id="tags" name="tags" placeholder="VD: php, laravel, web (cách nhau bởi dấu phẩy)">
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1; padding: 14px; font-size: 16px;">📤 Đăng Bài</button>
                        <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary" style="flex: 1; padding: 14px; font-size: 16px; text-align: center;">← Quay Lại</a>
                    </div>

                    <div id="message" style="display: none; margin-top: 20px; padding: 16px; border-radius: var(--radius-md);"></div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: ['lists', 'link', 'image', 'code', 'table', 'fullscreen'],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code table | fullscreen',
            menubar: 'file edit view insert format tools table help',
            skin: 'oxide',
            content_css: 'default',
            relative_urls: false,
            file_picker_types: 'image',
        });

        // Thumbnail preview
        document.getElementById('thumbnailFile').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const preview = document.getElementById('thumbnailPreview');
                    preview.innerHTML = `<img src="${event.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-md);">`;
                    document.getElementById('thumbnail').value = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Form submit
        document.getElementById('postForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const title = document.getElementById('title').value;
            const content = tinyMCE.activeEditor.getContent();
            const category = document.getElementById('category').value;
            const tags = document.getElementById('tags').value;
            const status = document.getElementById('status').value;
            const thumbnail = document.getElementById('thumbnail').value;

            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('title', title);
            formData.append('content', content);
            formData.append('category', category);
            formData.append('tags', tags);
            formData.append('status', status);
            formData.append('thumbnail', thumbnail);

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
                    messageDiv.style.background = 'rgba(16, 185, 129, 0.1)';
                    messageDiv.style.color = 'var(--success)';
                    messageDiv.style.borderLeft = '4px solid var(--success)';
                    messageDiv.innerHTML = '✅ Bài viết đã được đăng thành công!<br>Chuyển hướng trong 2 giây...';
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_URL; ?>/profile.php';
                    }, 2000);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'error';
                    messageDiv.style.background = 'rgba(239, 68, 68, 0.1)';
                    messageDiv.style.color = 'var(--danger)';
                    messageDiv.style.borderLeft = '4px solid var(--danger)';
                    messageDiv.textContent = data.error || 'Lỗi khi đăng bài';
                }
            } catch (error) {
                const messageDiv = document.getElementById('message');
                messageDiv.style.display = 'block';
                messageDiv.className = 'error';
                messageDiv.style.background = 'rgba(239, 68, 68, 0.1)';
                messageDiv.style.color = 'var(--danger)';
                messageDiv.style.borderLeft = '4px solid var(--danger)';
                messageDiv.textContent = 'Lỗi kết nối: ' + error.message;
            }
        });

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.href = '<?php echo BASE_URL; ?>/login.php';
        });
    </script>
</body>
</html>

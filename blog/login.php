<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - DevDA Blog</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1>Đăng Nhập</h1>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Tên Đăng Nhập</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Mật Khẩu</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Đăng Nhập</button>

                <div id="message" style="display:none; margin-top: 15px; padding: 10px; border-radius: 4px;"></div>
            </form>

            <p class="auth-link">
                Chưa có tài khoản? <a href="<?php echo BASE_URL; ?>/register.php">Đăng Ký Ngay</a>
            </p>

            <p class="back-link">
                <a href="<?php echo BASE_URL; ?>">← Quay Lại</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');

            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('username', username);
            formData.append('password', password);

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'success';
                    messageDiv.textContent = 'Đăng nhập thành công, chuyển hướng...';
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_URL; ?>';
                    }, 1500);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'error';
                    messageDiv.textContent = data.error || 'Lỗi đăng nhập';
                }
            } catch (error) {
                messageDiv.style.display = 'block';
                messageDiv.className = 'error';
                messageDiv.textContent = 'Lỗi kết nối';
            }
        });
    </script>
</body>
</html>

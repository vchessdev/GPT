<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - DevDA Blog</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
</head>
<body class="admin-login">
    <div class="admin-login-container">
        <div class="admin-login-box">
            <h1>Admin Login</h1>
            <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                <strong>Tài khoản mặc định:</strong><br>
                Username: <code>admin</code><br>
                Password: <code>admin123</code>
            </p>
            
            <form id="adminLoginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="admin" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Đăng Nhập</button>

                <div id="message" style="display:none; margin-top: 15px; padding: 10px; border-radius: 4px;"></div>
            </form>

            <p style="text-align: center; margin-top: 20px;">
                <a href="<?php echo BASE_URL; ?>" style="color: #0066cc;">← Quay Về Blog</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async (e) => {
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

                if (data.success && data.user.role === 'admin') {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'success';
                    messageDiv.textContent = 'Đăng nhập thành công, chuyển hướng...';
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_URL; ?>/admin/';
                    }, 1500);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'error';
                    messageDiv.textContent = data.error || 'Lỗi đăng nhập hoặc không phải admin';
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

<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - DevDA Blog</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1>Đăng Ký</h1>
            
            <form id="registerForm">
                <div class="form-group">
                    <label for="username">Tên Đăng Nhập</label>
                    <input type="text" id="username" name="username" required>
                    <small>Chỉ chứa chữ, số, dấu gạch dưới</small>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Mật Khẩu</label>
                    <input type="password" id="password" name="password" required>
                    <small>Ít nhất 6 ký tự</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Xác Nhận Mật Khẩu</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Đăng Ký</button>

                <div id="message" style="display:none; margin-top: 15px; padding: 10px; border-radius: 4px;"></div>
            </form>

            <p class="auth-link">
                Đã có tài khoản? <a href="<?php echo BASE_URL; ?>/login.php">Đăng Nhập</a>
            </p>

            <p class="back-link">
                <a href="<?php echo BASE_URL; ?>">← Quay Lại</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            const messageDiv = document.getElementById('message');

            if (password !== passwordConfirm) {
                messageDiv.style.display = 'block';
                messageDiv.className = 'error';
                messageDiv.textContent = 'Mật khẩu không khớp';
                return;
            }

            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('username', username);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('password_confirm', passwordConfirm);

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'success';
                    messageDiv.textContent = 'Đăng ký thành công, chuyển hướng đến đăng nhập...';
                    setTimeout(() => {
                        window.location.href = '<?php echo BASE_URL; ?>/login.php';
                    }, 1500);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'error';
                    messageDiv.textContent = data.error || 'Lỗi đăng ký';
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

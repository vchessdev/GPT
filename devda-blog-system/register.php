<?php
/**
 * devDA Blog System - Register Page
 */

require_once __DIR__ . '/config/config.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: /blog/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - devDA Blog System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 15px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .login-link {
            text-align: center;
            font-size: 14px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .terms {
            font-size: 12px;
            color: #999;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .terms a {
            color: #667eea;
            text-decoration: none;
        }

        .loading {
            display: none;
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>🎓 devDA</h1>
            <p>Đăng Ký Tài Khoản Học Tập</p>
        </div>

        <div id="alertBox"></div>

        <form id="registerForm" onsubmit="handleRegister(event)">
            <div class="form-group">
                <label for="full_name">Họ và Tên</label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    placeholder="Nguyễn Văn A"
                    required
                >
            </div>

            <div class="form-group">
                <label for="username">Tên Đăng Nhập</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="username"
                    required
                    minlength="3"
                    maxlength="30"
                >
                <div class="password-hint">3-30 ký tự, không dấu cách</div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="your@email.com"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Mật Khẩu</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••"
                    required
                    minlength="6"
                >
                <div class="password-hint">Tối thiểu 6 ký tự</div>
            </div>

            <div class="form-group">
                <label for="password_confirm">Xác Nhận Mật Khẩu</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    placeholder="••••••••"
                    required
                >
            </div>

            <div class="terms">
                Bằng cách đăng ký, bạn đồng ý với <a href="#">Điều khoản sử dụng</a> và <a href="#">Chính sách bảo mật</a> của chúng tôi.
            </div>

            <button type="submit" class="btn-register" id="registerBtn">
                Đăng Ký
            </button>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <span>Đang xử lý...</span>
            </div>
        </form>

        <div class="login-link">
            Đã có tài khoản? <a href="/blog/login.php">Đăng nhập</a>
        </div>
    </div>

    <script>
        function showAlert(message, type = 'error') {
            const alertBox = document.getElementById('alertBox');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            alertBox.innerHTML = '';
            alertBox.appendChild(alertDiv);
        }

        async function handleRegister(e) {
            e.preventDefault();

            const full_name = document.getElementById('full_name').value;
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const password_confirm = document.getElementById('password_confirm').value;
            const btn = document.getElementById('registerBtn');
            const loading = document.getElementById('loading');

            // Validate
            if (!full_name || !username || !email || !password) {
                showAlert('Vui lòng điền đầy đủ thông tin');
                return;
            }

            if (password !== password_confirm) {
                showAlert('Mật khẩu xác nhận không khớp');
                return;
            }

            if (password.length < 6) {
                showAlert('Mật khẩu phải có ít nhất 6 ký tự');
                return;
            }

            btn.disabled = true;
            loading.style.display = 'block';

            try {
                const response = await fetch('/blog/api/auth.php?action=register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        full_name,
                        username,
                        email,
                        password
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showAlert('Đăng ký thành công! Vui lòng đăng nhập.', 'success');
                    setTimeout(() => {
                        window.location.href = '/blog/login.php';
                    }, 2000);
                } else {
                    showAlert(data.message);
                    btn.disabled = false;
                    loading.style.display = 'none';
                }
            } catch (error) {
                showAlert('Lỗi kết nối: ' + error.message);
                btn.disabled = false;
                loading.style.display = 'none';
            }
        }
    </script>
</body>
</html>

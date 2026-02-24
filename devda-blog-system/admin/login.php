<?php
/**
 * devDA Blog System - Admin Login Page
 */

require_once __DIR__ . '/../config/config.php';

// If already logged in as admin, redirect
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: /blog/admin/dashboard.php');
    exit;
}

// Destroy any existing session
session_destroy();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - devDA Blog System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #1e3c72;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .admin-badge {
            display: inline-block;
            background: #ff6b6b;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
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
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 20px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(30, 60, 114, 0.4);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .back-link a {
            color: #2a5298;
            text-decoration: none;
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
            border-top: 2px solid #1e3c72;
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
    <div class="login-container">
        <div class="logo">
            <h1>🔐 Admin Panel</h1>
            <p>devDA Blog System</p>
            <span class="admin-badge">Khu vực quản trị</span>
        </div>

        <div id="alertBox"></div>

        <form id="loginForm" onsubmit="handleAdminLogin(event)">
            <div class="form-group">
                <label for="email">Email Admin</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="admin@devda.undo.it"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                Đăng Nhập Admin
            </button>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <span>Đang xử lý...</span>
            </div>
        </form>

        <div class="back-link">
            <a href="/blog/">← Quay lại trang chủ</a>
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

        async function handleAdminLogin(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const btn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');

            if (!email || !password) {
                showAlert('Vui lòng điền đầy đủ thông tin');
                return;
            }

            btn.disabled = true;
            loading.style.display = 'block';

            try {
                const response = await fetch('/blog/api/auth.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        password,
                        remember: false
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Check if admin
                    if (data.user && data.user.role === 'admin') {
                        window.location.href = '/blog/admin/dashboard.php';
                    } else {
                        showAlert('Bạn không có quyền truy cập khu vực quản trị');
                        btn.disabled = false;
                        loading.style.display = 'none';
                    }
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

        document.getElementById('email').focus();
    </script>
</body>
</html>

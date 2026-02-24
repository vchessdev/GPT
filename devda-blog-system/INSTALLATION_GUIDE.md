# 📚 devDA Blog System - Hướng Dẫn Triển Khai & Tài Liệu Code

**Version**: 1.0  
**Ngày cập nhật**: 2026-02-24  
**Tên miền**: devda.undo.it/blog

---

## 📋 MỤC LỤC

1. [Hướng dẫn cài đặt](#hướng-dẫn-cài-đặt)
2. [Cấu trúc thư mục](#cấu-trúc-thư-mục)
3. [Cấu hình hệ thống](#cấu-hình-hệ-thống)
4. [Hướng dẫn sử dụng](#hướng-dẫn-sử-dụng)
5. [API Documentation](#api-documentation)
6. [Security Best Practices](#security-best-practices)
7. [Troubleshooting](#troubleshooting)

---

## ⚙️ Hướng dẫn cài đặt

### Yêu cầu hệ thống

- **PHP**: 7.4 hoặc cao hơn
- **Máy chủ web**: Apache, Nginx, IIS
- **Database**: JSON (không cần MySQL)
- **Module PHP**: Không cần module đặc biệt

### Các bước cài đặt

#### 1. **Tải file dự án**

```bash
# Clone hoặc download dự án vào thư mục /blog
cd /var/www/html  # hoặc thư mục web root của bạn
git clone <repository-url> blog
# hoặc
unzip devda-blog-system.zip -d blog
```

#### 2. **Phân quyền thư mục**

```bash
cd blog

# Cho phép ghi vào thư mục data
chmod 755 data
chmod 755 uploads
chmod 755 uploads/images
chmod 755 uploads/pdf
chmod 755 uploads/docs

# Trên Windows (nếu dùng)
# Click chuột phải → Properties → Security → Edit
```

#### 3. **Tạo file cấu hình**

File `config/config.php` đã có sẵn. Kiểm tra các thiết lập:

```php
// Cấu hình cơ bản (config/config.php)
define('SITE_NAME', 'devDA Blog System');
define('SITE_DOMAIN', 'devda.undo.it');
define('SITE_URL', 'https://devda.undo.it/blog/');
define('ADMIN_EMAIL', 'admin@devda.undo.it');
```

#### 4. **Tạo tài khoản Admin đầu tiên**

Vì không có giao diện đăng ký admin, bạn cần tạo thủ công bằng PHP:

```php
<?php
// File: setup.php (chạy 1 lần rồi xóa)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Tạo admin user
$admin = [
    'id' => 'user_admin_001',
    'username' => 'admin',
    'email' => 'admin@devda.undo.it',
    'password' => password_hash('password123', PASSWORD_BCRYPT),
    'full_name' => 'Admin',
    'avatar' => '/blog/assets/images/default-avatar.jpg',
    'role' => 'admin',
    'status' => 'active',
    'bio' => 'Administrator',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'last_login' => null,
    'email_verified' => true
];

addItem('users', 'users', $admin);
echo "Admin user created successfully!";
?>
```

Chạy file này qua browser rồi xóa nó.

#### 5. **Kiểm tra cài đặt**

```bash
# Kiểm tra thư mục data có quyền ghi
ls -la data/

# Truy cập website
http://localhost/blog/
```

---

## 📁 Cấu trúc thư mục

```
devda-blog-system/
│
├── config/
│   ├── config.php              # Cấu hình chính
│   └── database.php            # Helper functions JSON database
│
├── api/
│   ├── auth.php                # API đăng ký, đăng nhập
│   ├── posts.php               # API bài viết
│   ├── comments.php            # API bình luận
│   ├── votes.php               # API vote/like
│   └── files.php               # API file upload
│
├── admin/
│   ├── login.php               # Form đăng nhập admin
│   ├── dashboard.php           # Trang chủ admin
│   ├── users.php               # Quản lý user
│   ├── posts.php               # Quản lý bài viết
│   ├── comments.php            # Quản lý bình luận
│   ├── files.php               # Quản lý file
│   ├── votes.php               # Quản lý vote
│   └── logs.php                # Xem logs
│
├── assets/
│   ├── css/
│   │   ├── style.css           # CSS chính
│   │   ├── admin.css           # CSS admin
│   │   └── responsive.css      # Responsive design
│   │
│   ├── js/
│   │   ├── main.js             # JS chính
│   │   ├── api.js              # API helper
│   │   ├── auth.js             # Auth logic
│   │   ├── vote.js             # Vote logic
│   │   └── comment.js          # Comment logic
│   │
│   └── images/
│       ├── logo.png
│       ├── favicon.ico
│       └── default-avatar.jpg
│
├── data/
│   ├── users.json              # Dữ liệu user
│   ├── posts.json              # Dữ liệu bài viết
│   ├── comments.json           # Dữ liệu bình luận
│   ├── votes.json              # Dữ liệu vote
│   ├── files.json              # Dữ liệu file
│   └── logs.json               # Logs hoạt động
│
├── uploads/
│   ├── images/                 # Ảnh bài viết
│   ├── pdf/                    # File PDF
│   └── docs/                   # Tài liệu
│
├── index.php                   # Trang chủ
├── login.php                   # Form đăng nhập
├── register.php                # Form đăng ký
├── post.php                    # Chi tiết bài viết
├── search.php                  # Tìm kiếm
├── profile.php                 # Hồ sơ cá nhân
├── create-post.php             # Viết bài mới
│
├── .htaccess                   # Apache config (nếu cần)
├── .gitignore                  # Git ignore
└── README.md                   # Tài liệu
```

---

## 🔧 Cấu hình hệ thống

### Các biến cấu hình quan trọng

**File: `config/config.php`**

```php
// Đường dẫn dữ liệu
define('DATA_DIR', __DIR__ . '/../data/');
define('UPLOADS_DIR', __DIR__ . '/../uploads/');

// Cấu hình bảo mật
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);  // Thuật toán hash
define('PASSWORD_COST', 10);                     // Độ phức tạp

// Session
define('SESSION_NAME', 'DEVDA_SESSION');
define('SESSION_LIFETIME', 2592000);  // 30 ngày

// Phân trang
define('POSTS_PER_PAGE', 10);
define('COMMENTS_PER_PAGE', 20);

// Upload
define('MAX_FILE_SIZE', 10485760);     // 10MB
define('MAX_IMAGE_SIZE', 5242880);     // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx', 'txt']);
```

### Cấu hình Apache (.htaccess)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /blog/

    # Redirect to HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Remove trailing slash
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.+)/$ $1 [L,R=301]

    # Prevent access to sensitive files
    RewriteRule ^(config|data|\.git)/ - [F,L]
</IfModule>

# Disable directory listing
Options -Indexes

# Block access to JSON files
<FilesMatch "\.json$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

---

## 📖 Hướng dẫn sử dụng

### Cho người dùng thường

#### 1. Đăng ký tài khoản

```
1. Truy cập: http://devda.undo.it/blog/register.php
2. Điền form:
   - Họ và tên
   - Tên đăng nhập (3-30 ký tự)
   - Email
   - Mật khẩu (6+ ký tự)
3. Click "Đăng Ký"
```

#### 2. Đăng nhập

```
1. Truy cập: http://devda.undo.it/blog/login.php
2. Nhập email và mật khẩu
3. (Tùy chọn) Tick "Ghi nhớ tài khoản"
4. Click "Đăng Nhập"
```

#### 3. Viết bài viết

```
1. Đăng nhập
2. Click "Viết bài" trong menu
3. Điền thông tin:
   - Tiêu đề
   - Nội dung
   - Ảnh đại diện
   - Chuyên mục
   - Thẻ
4. Chọn "Lưu nháp" hoặc "Xuất bản"
```

#### 4. Bình luận bài viết

```
1. Xem bài viết
2. Cuộn xuống mục bình luận
3. Nhập bình luận
4. Click "Gửi"
```

#### 5. Vote/Like bài viết

```
1. Xem bài viết
2. Click nút ❤️ (Like) hoặc 👎 (Dislike)
3. Nút sẽ đổi màu để xác nhận
```

### Cho Admin

#### 1. Đăng nhập Admin

```
1. Truy cập: http://devda.undo.it/blog/admin/login.php
2. Nhập email admin và mật khẩu
3. Click "Đăng Nhập Admin"
```

#### 2. Dashboard

- Xem thống kê:
  - Tổng user
  - Tổng bài viết
  - Tổng bình luận
  - Tổng lượt xem

#### 3. Quản lý User

```
Admin có thể:
- Xem danh sách user
- Ban/unban user
- Promote user thành admin
- Xóa user (cơ dữ liệu sẽ sạch)
```

#### 4. Quản lý Bài Viết

```
Admin có thể:
- Xem danh sách bài viết
- Sửa bài viết của user khác
- Xóa bài viết (xóa comments & votes)
- Archive bài viết (ẩn khỏi public)
```

#### 5. Quản lý Bình luận

```
Admin có thể:
- Xem tất cả bình luận
- Ẩn bình luận (không xóa)
- Phê duyệt bình luận chưa duyệt
- Xóa bình luận
```

#### 6. Xem Logs

```
Admin có thể:
- Xem tất cả hoạt động hệ thống
- Lọc theo user/action/date
- Xuất logs (tùy chọn)
```

---

## 🔌 API Documentation

### Base URL

```
https://devda.undo.it/blog/api/
```

### Authentication API - `/api/auth.php`

#### Register
```http
POST /api/auth.php?action=register
Content-Type: application/json

{
  "username": "nguyenvan_a",
  "email": "van@example.com",
  "password": "password123",
  "full_name": "Nguyễn Văn A"
}

Response 200:
{
  "status": "success",
  "message": "Đăng ký thành công",
  "user_id": "user_001",
  "redirect": "/blog/login.php"
}
```

#### Login
```http
POST /api/auth.php?action=login
Content-Type: application/json

{
  "email": "van@example.com",
  "password": "password123",
  "remember": true
}

Response 200:
{
  "status": "success",
  "message": "Đăng nhập thành công",
  "user": {
    "id": "user_001",
    "username": "nguyenvan_a",
    "email": "van@example.com",
    "role": "user",
    "avatar": "/blog/assets/images/avatar.jpg"
  },
  "redirect": "/blog/"
}
```

#### Logout
```http
POST /api/auth.php?action=logout

Response 200:
{
  "status": "success",
  "message": "Đã đăng xuất",
  "redirect": "/blog/"
}
```

#### Check Auth
```http
GET /api/auth.php?action=check

Response 200:
{
  "status": "success",
  "message": "Đã xác thực",
  "user": { ... }
}

Response 401:
{
  "status": "error",
  "message": "Chưa đăng nhập"
}
```

### Posts API - `/api/posts.php`

#### Create Post
```http
POST /api/posts.php?action=create
Content-Type: application/json

{
  "title": "Tiêu đề bài viết",
  "content": "<h2>Nội dung</h2>...",
  "excerpt": "Tóm tắt",
  "category": "Học Tập",
  "tags": ["toán", "học tập"],
  "status": "draft" // hoặc "published"
}

Response 200:
{
  "status": "success",
  "message": "Tạo bài viết thành công",
  "post_id": "post_001",
  "slug": "tieu-de-bai-viet",
  "redirect": "/blog/post.php?slug=..."
}
```

#### List Posts
```http
GET /api/posts.php?action=list&page=1&status=published&sort=created_at

Response 200:
{
  "status": "success",
  "message": "Lấy danh sách bài viết thành công",
  "items": [ { post objects } ],
  "total": 50,
  "page": 1,
  "per_page": 10,
  "pages": 5
}
```

#### Get Post
```http
GET /api/posts.php?action=get&slug=tieu-de-bai-viet

Response 200:
{
  "status": "success",
  "post": { ... }
}
```

#### Update Post
```http
POST /api/posts.php?action=update
{
  "post_id": "post_001",
  "title": "Tiêu đề mới",
  "content": "Nội dung mới",
  "category": "Khác",
  "tags": ["tag1", "tag2"]
}
```

#### Delete Post
```http
POST /api/posts.php?action=delete
{
  "post_id": "post_001"
}
```

#### Search Posts
```http
GET /api/posts.php?action=search&q=keyword&page=1

Response 200:
{
  "status": "success",
  "items": [ ... ],
  "total": 10
}
```

### Comments API - `/api/comments.php`

#### Create Comment
```http
POST /api/comments.php?action=create
{
  "post_id": "post_001",
  "content": "Bình luận của tôi",
  "parent_id": null  // null = comment chính, "comment_001" = reply
}
```

#### List Comments
```http
GET /api/comments.php?action=list&post_id=post_001&page=1
```

#### Delete Comment
```http
POST /api/comments.php?action=delete
{
  "comment_id": "comment_001"
}
```

### Votes API - `/api/votes.php`

#### Vote/Like
```http
POST /api/votes.php?action=vote
{
  "post_id": "post_001",
  "vote_type": "like"  // "like" hoặc "dislike"
}

Response 200:
{
  "status": "success",
  "total_likes": 26,
  "total_dislikes": 2,
  "user_vote": "like"
}
```

#### Check Vote Status
```http
GET /api/votes.php?action=check&post_id=post_001

Response 200:
{
  "status": "success",
  "post_id": "post_001",
  "total_likes": 25,
  "total_dislikes": 2,
  "user_vote": null  // hoặc "like" / "dislike"
}
```

---

## 🔐 Security Best Practices

### 1. **Password Security**

```php
// Hash password
$hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Verify password
if (password_verify($password, $hashed)) {
    // Correct
}
```

### 2. **Input Validation**

```php
// Sanitize user input
$safe_input = htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Invalid
}

// Validate file upload
if (!in_array($file_type, ALLOWED_IMAGE_TYPES)) {
    // Not allowed
}
```

### 3. **Output Encoding**

```php
// Always encode output
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

// For HTML content, use sanitization
$safe_html = sanitizeHTML($user_content);
```

### 4. **Session Security**

```php
// Set secure session cookie
ini_set('session.cookie_secure', true);      // HTTPS only
ini_set('session.cookie_httponly', true);    // No JS access
ini_set('session.use_strict_mode', true);    // Strict mode
```

### 5. **File Upload Security**

```php
// Validate file size
if ($file_size > MAX_FILE_SIZE) {
    // File too large
}

// Check MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file_path);
finfo_close($finfo);

// Rename file with random name
$new_name = uniqid() . '.' . $extension;

// Store outside web root if possible
```

### 6. **CSRF Protection**

```php
// Generate token
$token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;

// Verify token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    // Invalid token
}
```

### 7. **Rate Limiting**

```php
// Implement rate limiting for login
$key = "login_" . $_SERVER['REMOTE_ADDR'];
$attempts = cache_get($key) ?? 0;

if ($attempts > 5) {
    // Too many attempts
}
```

---

## 🐛 Troubleshooting

### 1. **Lỗi "Permission denied" khi ghi file**

**Nguyên nhân**: Thư mục `data/` và `uploads/` không có quyền ghi

**Giải pháp**:
```bash
# Linux/Mac
chmod 755 data uploads
chmod 755 uploads/*

# hoặc
sudo chown -R www-data:www-data data uploads
```

### 2. **Lỗi "Cannot read JSON file"**

**Nguyên nhân**: File JSON bị corrupted hoặc syntax lỗi

**Giải pháp**:
```php
// Debug JSON
$json = file_get_contents('data/posts.json');
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg();
}
```

### 3. **Session không lưu, bị logout liên tục**

**Nguyên nhân**: 
- Cookie bị block
- Session timeout quá ngắn
- Hostname không khớp

**Giải pháp**:
```php
// Check session
echo session_status();  // PHP_SESSION_ACTIVE = 2

// Clear session
session_destroy();
session_start();

// Increase timeout
define('SESSION_LIFETIME', 86400); // 24 hours
```

### 4. **Upload file bị lỗi**

**Nguyên nhân**:
- Max file size quá nhỏ
- MIME type không được phép
- Thư mục uploads không tồn tại

**Giải pháp**:
```php
// Kiểm tra lỗi upload
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo "Upload error: " . $_FILES['file']['error'];
}

// Tăng max file size
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '20M');
```

### 5. **Bài viết không hiển thị trên trang chủ**

**Nguyên nhân**: Bài viết có status = "draft"

**Giải pháp**:
```php
// Xuất bản bài viết
POST /api/posts.php?action=publish
{
  "post_id": "post_001"
}
```

### 6. **Không tìm thấy tài khoản admin**

**Nguyên nhân**: Chưa tạo admin user hoặc tạo với role sai

**Giải pháp**:
```php
// Chạy script tạo admin (một lần)
require_once 'config/config.php';
require_once 'config/database.php';

// Tạo admin
$admin = [
    'id' => 'user_admin_001',
    'username' => 'admin',
    'email' => 'admin@devda.undo.it',
    'password' => password_hash('admin123', PASSWORD_BCRYPT),
    'full_name' => 'Administrator',
    'role' => 'admin',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'last_login' => null
];

addItem('users', 'users', $admin);
```

---

## 📚 Tài liệu bổ sung

### Links hữu ích

- [PHP Official Documentation](https://www.php.net/docs.php)
- [JSON Format](https://www.json.org/)
- [Security Best Practices](https://owasp.org/www-project-top-ten/)
- [HTTP Status Codes](https://httpwg.org/specs/rfc7231.html#status.codes)

### Liên hệ & Support

```
Email: admin@devda.undo.it
Website: https://devda.undo.it/blog/
```

---

**🎉 Chúc mừng! Hệ thống devDA Blog đã sẵn sàng sử dụng.**

Để bắt đầu, hãy truy cập:
- **Trang chủ**: https://devda.undo.it/blog/
- **Đăng nhập**: https://devda.undo.it/blog/login.php
- **Admin**: https://devda.undo.it/blog/admin/login.php

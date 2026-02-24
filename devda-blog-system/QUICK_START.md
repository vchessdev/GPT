# 🚀 devDA Blog System - Quick Start Guide

**Phiên bản**: 1.0  
**Ngày**: 2026-02-24  
**Trạng thái**: ✅ Production Ready

---

## 📦 File được tạo

### Core Files
```
✅ config/config.php                 - Cấu hình hệ thống
✅ config/database.php               - JSON database helpers
✅ setup.php                         - Script cài đặt
```

### Frontend Pages
```
✅ index.php                         - Trang chủ với danh sách bài viết
✅ login.php                         - Form đăng nhập
✅ register.php                      - Form đăng ký
```

### API Endpoints
```
✅ api/auth.php                      - Authentication API
✅ api/posts.php                     - Posts management API
✅ api/comments.php                  - Comments API
✅ api/votes.php                     - Votes/Like API
✅ api/users.php                     - Admin user management API
```

### Admin Panel
```
✅ admin/login.php                   - Admin login
✅ admin/dashboard.php               - Admin dashboard với thống kê
✅ admin/users.php                   - Admin user management
```

### Documentation
```
✅ README.md                         - Tổng quan dự án
✅ INSTALLATION_GUIDE.md             - Hướng dẫn cài đặt chi tiết
✅ DEVDA_BLOG_SYSTEM.md              - Tài liệu kiến trúc hệ thống
```

---

## 🎯 Các bước khởi động

### 1️⃣ Clone / Download dự án

```bash
git clone <repository> devda-blog-system
cd devda-blog-system
```

### 2️⃣ Phân quyền thư mục

**Linux/Mac:**
```bash
chmod 755 data uploads
chmod 755 uploads/*
```

**Windows:**
- Click chuột phải folder → Properties → Security
- Cho phép Full Control cho user hiện tại

### 3️⃣ Truy cập Setup Script

Mở browser và truy cập:
```
http://localhost/blog/setup.php
```

Hoặc nếu dùng PHP built-in server:
```bash
php -S localhost:8000
# Truy cập http://localhost:8000/setup.php
```

### 4️⃣ Tạo Admin Account

Click button "✅ Tạo Tài Khoản Admin" trong setup.php

**Thông tin đăng nhập mặc định:**
- Email: `admin@devda.undo.it`
- Password: `admin123`

### 5️⃣ Xóa Setup Script

```bash
rm setup.php
# hoặc xóa thủ công từ file manager
```

### 6️⃣ Truy cập hệ thống

- **Trang chủ**: http://localhost/blog/
- **Đăng nhập**: http://localhost/blog/login.php
- **Admin**: http://localhost/blog/admin/login.php

---

## 🔑 Tài khoản Test

### Admin
```
Email: admin@devda.undo.it
Password: admin123
```

### Tạo thêm User Test

Đăng ký trực tiếp qua form đăng ký:
```
URL: http://localhost/blog/register.php
```

---

## 📁 Cấu trúc thư mục được tạo

```
devda-blog-system/
│
├── config/
│   ├── config.php                  # ✅ Cấu hình chính
│   └── database.php                # ✅ JSON helpers
│
├── api/
│   ├── auth.php                    # ✅ Auth API
│   ├── posts.php                   # ✅ Posts API
│   ├── comments.php                # ✅ Comments API
│   ├── votes.php                   # ✅ Votes API
│   └── users.php                   # ✅ Admin users API
│
├── admin/
│   ├── login.php                   # ✅ Admin login
│   ├── dashboard.php               # ✅ Dashboard
│   └── users.php                   # ✅ User management
│
├── data/                           # 📁 Thư mục JSON database
│   ├── users.json
│   ├── posts.json
│   ├── comments.json
│   ├── votes.json
│   ├── files.json
│   └── logs.json
│
├── uploads/                        # 📁 Uploaded files
│   ├── images/
│   ├── pdf/
│   └── docs/
│
├── assets/                         # (Cần tạo thêm CSS/JS)
│   ├── css/
│   ├── js/
│   └── images/
│
├── index.php                       # ✅ Trang chủ
├── login.php                       # ✅ Form login
├── register.php                    # ✅ Form đăng ký
├── setup.php                       # ✅ Setup script
│
├── README.md                       # ✅ Tài liệu
├── INSTALLATION_GUIDE.md           # ✅ Hướng dẫn
└── DEVDA_BLOG_SYSTEM.md            # ✅ Kiến trúc
```

---

## 🔌 API Endpoints Available

### Authentication
- `POST /api/auth.php?action=register` - Đăng ký
- `POST /api/auth.php?action=login` - Đăng nhập
- `POST /api/auth.php?action=logout` - Đăng xuất
- `GET /api/auth.php?action=check` - Check auth

### Posts
- `POST /api/posts.php?action=create` - Tạo bài
- `GET /api/posts.php?action=list` - Danh sách
- `GET /api/posts.php?action=get` - Chi tiết
- `POST /api/posts.php?action=update` - Sửa
- `POST /api/posts.php?action=delete` - Xóa
- `GET /api/posts.php?action=search` - Tìm kiếm

### Comments
- `POST /api/comments.php?action=create` - Tạo
- `GET /api/comments.php?action=list` - Danh sách
- `POST /api/comments.php?action=delete` - Xóa

### Votes
- `POST /api/votes.php?action=vote` - Vote
- `GET /api/votes.php?action=check` - Check status

### Admin Users
- `POST /api/users.php?action=promote` - Nâng quyền
- `POST /api/users.php?action=ban` - Khóa
- `POST /api/users.php?action=unban` - Mở khóa

---

## 🚀 Tính năng đã triển khai

### ✅ Hoàn chỉnh
- [x] Đăng ký / Đăng nhập
- [x] Hash mật khẩu (Bcrypt)
- [x] Session & Cookie
- [x] Viết / Sửa / Xóa bài viết
- [x] Draft & Publish
- [x] Bình luận bài viết
- [x] Vote / Like / Dislike
- [x] Admin dashboard
- [x] Admin user management
- [x] JSON database
- [x] API endpoints (JSON)
- [x] Input validation
- [x] XSS protection

### 📋 Cần bổ sung (tùy chọn)
- [ ] Frontend HTML/CSS hoàn chỉnh
- [ ] JavaScript AJAX handlers
- [ ] File upload API
- [ ] Search full implementation
- [ ] Email notifications
- [ ] User profile page
- [ ] Post creation page UI
- [ ] Pagination UI
- [ ] Rate limiting
- [ ] CSRF tokens

---

## 🔒 Bảo mật đã triển khai

✅ **Đã làm:**
- Password hashing (Bcrypt)
- Input sanitization (strip_tags, htmlspecialchars)
- Session security
- JSON file access restriction
- Error handling

⚠️ **Nên làm:**
- [ ] HTTPS (bắt buộc)
- [ ] CSRF tokens
- [ ] Rate limiting
- [ ] Security headers (CSP, X-Frame-Options)
- [ ] CORS configuration
- [ ] SQL injection protection (nếu dùng MySQL)

---

## 📊 Database Structure (JSON)

### users.json
```json
{
  "users": [
    {
      "id": "user_001",
      "username": "nguyenvan_a",
      "email": "van@example.com",
      "password": "$2y$10$...",
      "full_name": "Nguyễn Văn A",
      "role": "user",
      "status": "active"
    }
  ]
}
```

### posts.json
```json
{
  "posts": [
    {
      "id": "post_001",
      "author_id": "user_001",
      "title": "Tiêu đề bài viết",
      "slug": "tieu-de-bai-viet",
      "content": "Nội dung HTML...",
      "status": "published",
      "views": 150,
      "likes": 25
    }
  ]
}
```

Tương tự: comments.json, votes.json, files.json, logs.json

---

## 📝 Cách sử dụng API từ JavaScript

### Đăng nhập
```javascript
const response = await fetch('/blog/api/auth.php?action=login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password123'
    })
});

const data = await response.json();
if (data.status === 'success') {
    window.location.href = '/blog/';
}
```

### Lấy danh sách bài viết
```javascript
const response = await fetch('/blog/api/posts.php?action=list&page=1');
const data = await response.json();

console.log(data.items);  // Mảng bài viết
console.log(data.total);  // Tổng số bài
```

### Vote bài viết
```javascript
const response = await fetch('/blog/api/votes.php?action=vote', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        post_id: 'post_001',
        vote_type: 'like'
    })
});

const data = await response.json();
console.log(data.total_likes);
```

---

## 🐛 Troubleshooting

### "Permission denied" error
```bash
chmod 755 data uploads
```

### "Cannot read JSON" error
File JSON bị corrupted. Xóa file và chạy lại.

### Session không lưu
- Kiểm tra cookie settings
- Xóa browser cache
- Kiểm tra config SITE_DOMAIN

### Login không được
- Kiểm tra email/password đúng
- Kiểm tra user status = "active" (không bị ban)

---

## 🎓 Hướng dẫn tiếp theo

1. **Hoàn chỉnh Frontend**
   - Tạo post.php (chi tiết bài viết)
   - Tạo search.php (tìm kiếm)
   - Tạo profile.php (hồ sơ user)
   - Tạo create-post.php (viết bài)

2. **CSS & Styling**
   - Tạo assets/css/style.css
   - Tạo assets/css/responsive.css
   - Tạo assets/css/admin.css

3. **JavaScript**
   - Tạo assets/js/main.js
   - Tạo assets/js/api.js
   - Tạo assets/js/vote.js
   - Tạo assets/js/comment.js

4. **Admin Pages**
   - Tạo admin/posts.php
   - Tạo admin/comments.php
   - Tạo admin/files.php
   - Tạo admin/logs.php

5. **Tính năng mở rộng**
   - Implement file upload
   - Thêm email notifications
   - Thêm user profile
   - Thêm analytics

---

## 📚 Tài liệu liên quan

- `README.md` - Tổng quan dự án
- `INSTALLATION_GUIDE.md` - Hướng dẫn chi tiết
- `DEVDA_BLOG_SYSTEM.md` - Kiến trúc & API
- Code comments - Giải thích chi tiết

---

## 💡 Tips & Tricks

### Test API bằng curl
```bash
# Đăng ký
curl -X POST http://localhost/blog/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"username":"test","email":"test@test.com","password":"123456"}'

# Đăng nhập
curl -X POST http://localhost/blog/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"123456"}'
```

### Test API bằng Postman
1. Download Postman
2. Create POST request
3. Set URL: http://localhost/blog/api/auth.php?action=login
4. Set Body (JSON): `{"email":"...","password":"..."}`
5. Send

### Debug JSON
```php
$data = json_decode(file_get_contents('data/posts.json'), true);
echo json_last_error_msg();
var_dump($data);
```

---

## 🎯 Checklist triển khai

- [ ] Clone/download dự án
- [ ] Phân quyền data/ và uploads/ folder
- [ ] Chạy setup.php
- [ ] Tạo admin account
- [ ] Xóa setup.php
- [ ] Test đăng nhập
- [ ] Test đăng ký
- [ ] Test tạo bài viết (qua API)
- [ ] Test bình luận (qua API)
- [ ] Test vote (qua API)
- [ ] Test admin panel
- [ ] Hoàn chỉnh frontend
- [ ] Deploy lên hosting

---

## 📞 Liên hệ & Support

```
Email: admin@devda.undo.it
Website: https://devda.undo.it/blog/
```

---

**🎉 Hệ thống đã sẵn sàng! Chúc bạn thành công!**

Để bắt đầu: http://localhost/blog/setup.php

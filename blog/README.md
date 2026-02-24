# 🚀 DevDA Blog System - Quick Start

**Phiên bản:** v1.0  
**Công nghệ:** PHP + JSON + HTML + CSS + JavaScript  
**Tương thích:** x10hosting Free, Shared Hosting, VPS

---

## ✨ Tính Năng

✅ Hệ thống tài khoản (register, login, roles)  
✅ Viết & quản lý bài viết  
✅ Bình luận bài viết  
✅ Vote (like/dislike)  
✅ Upload file & ảnh  
✅ Admin panel  
✅ Tìm kiếm bài viết  
✅ Phân quyền user/admin  

---

## 📁 Cấu Trúc Thư Mục

```
/blog/
├── .htaccess              # Rewrite rules
├── config.php             # Configuration
├── index.php              # Trang chủ
├── login.php              # Trang đăng nhập
├── register.php           # Trang đăng ký
│
├── /api/                  # API endpoints
│   ├── database.php       # JSON database handler
│   ├── auth.php           # Auth API
│   ├── posts.php          # Posts API
│   ├── comments.php       # Comments API
│   ├── votes.php          # Votes API
│   ├── files.php          # Files API
│   ├── users.php          # Users API (admin)
│   └── logs.php           # Logs API (admin)
│
├── /admin/                # Admin panel
│   ├── login.php          # Admin login
│   ├── index.php          # Dashboard
│   └── users.php          # Manage users
│
├── /assets/               # Static files
│   ├── css/
│   │   ├── style.css      # Main styles
│   │   └── admin.css      # Admin styles
│   ├── js/
│   │   └── app.js         # Main JS (coming)
│   └── images/
│
├── /data/                 # JSON Database (auto-create)
│   ├── users.json
│   ├── posts.json
│   ├── comments.json
│   ├── votes.json
│   ├── files.json
│   └── logs.json
│
└── /uploads/              # User uploads
    ├── images/
    ├── pdf/
    └── docs/
```

---

## 🔑 Admin Credentials (Default)

```
Username: admin
Password: admin123
```

⚠️ **Đổi password ngay sau khi đăng nhập!**

---

## 🌐 URLs

| Trang | URL |
|-------|-----|
| Trang chủ | `/blog/` |
| Đăng ký | `/blog/register.php` |
| Đăng nhập | `/blog/login.php` |
| Admin | `/blog/admin/login.php` |

---

## ⚙️ Cấu Hình

File: `config.php`

```php
BASE_URL          # Base URL của website
BASE_DIR          # Thư mục gốc
DATA_DIR          # Thư mục database
UPLOADS_DIR       # Thư mục uploads
SESSION_TIMEOUT   # Thời gian timeout session
```

---

## 📡 API Endpoints

### Auth API (`/api/auth.php`)
```
GET  ?action=check              # Kiểm tra login status
POST ?action=login              # Đăng nhập
POST ?action=register           # Đăng ký
GET  ?action=logout             # Đăng xuất
```

### Posts API (`/api/posts.php`)
```
POST ?action=create             # Tạo bài
POST ?action=update             # Sửa bài
GET  ?action=delete&id=xxx      # Xoá bài
GET  ?action=get&id=xxx         # Lấy bài
GET  ?action=list               # Danh sách bài
GET  ?action=search&q=xxx       # Tìm kiếm
```

### Comments API (`/api/comments.php`)
```
POST ?action=create             # Bình luận
GET  ?action=delete&id=xxx      # Xoá comment
GET  ?action=list&post_id=xxx   # Danh sách comments
POST ?action=hide&id=xxx        # Ẩn comment (admin)
```

### Votes API (`/api/votes.php`)
```
POST ?action=vote               # Like/Dislike
POST ?action=unvote             # Bỏ vote
GET  ?action=getVotes&post_id=xxx # Lấy votes
```

### Files API (`/api/files.php`)
```
POST ?action=upload             # Upload file
GET  ?action=list               # Danh sách file
GET  ?action=delete&id=xxx      # Xoá file
```

### Users API (`/api/users.php`)
```
GET  ?action=list               # Danh sách users (admin)
POST ?action=update             # Sửa user (admin)
POST ?action=delete             # Xoá user (admin)
```

---

## 🔐 Bảo Mật

✅ Password hashing (bcrypt)  
✅ Input validation  
✅ XSS protection (htmlspecialchars)  
✅ Session management  
✅ Role-based access control  

---

## 📊 Database Schema

### users.json
```json
{
  "id": "user_xxx",
  "username": "john",
  "email": "john@example.com",
  "password": "$2y$10$...",
  "role": "user",
  "status": "active",
  "created_at": "2024-01-15 10:30:00"
}
```

### posts.json
```json
{
  "id": "p_xxx",
  "title": "Bài viết tiêu đề",
  "content": "Nội dung bài viết",
  "slug": "bai-viet-tieu-de",
  "category": "tutorials",
  "tags": ["php", "blog"],
  "author_id": "user_xxx",
  "status": "publish",
  "views": 42,
  "created_at": "2024-01-15 10:30:00"
}
```

### comments.json
```json
{
  "id": "c_xxx",
  "post_id": "p_xxx",
  "user_id": "user_xxx",
  "content": "Bình luận",
  "parent_id": null,
  "status": "approved",
  "created_at": "2024-01-15 10:30:00"
}
```

### votes.json
```json
{
  "id": "v_xxx",
  "post_id": "p_xxx",
  "user_id": "user_xxx",
  "type": "like",
  "created_at": "2024-01-15 10:30:00"
}
```

---

## 🚀 Deployment

### x10hosting (Free)
→ Xem file `X10HOSTING_GUIDE.md`

### Shared Hosting
1. Upload folder `/blog/` vào `public_html`
2. Cấp quyền `755` cho folders
3. Cấp quyền `644` cho files
4. Truy cập: `yoursite.com/blog/`

### VPS / Dedicated
1. Clone hoặc upload folder `/blog/`
2. Cấp quyền: `chmod 755 data uploads`
3. Cấp quyền: `chmod 644 .htaccess`
4. Cấu hình virtual host
5. Truy cập: `yoursite.com/blog/`

---

## 🔧 Troubleshooting

### 404 - Request URL not found
- Kiểm tra `.htaccess` đã upload chưa
- Kiểm tra `RewriteEngine` bật chưa
- Thử vào `index.php` trực tiếp

### Permission Denied
- `chmod 777 data/`
- `chmod 777 uploads/`

### Database không tạo
- Kiểm tra folder `data/` có quyền ghi không
- Kiểm tra PHP error logs

### Login không thành công
- Kiểm tra session khởi động được chưa
- Check cookie settings

---

## 📚 Hướng Dẫn Sử Dụng

### 1️⃣ Đăng Ký
- Vào `/register.php`
- Nhập username, email, password
- Password phải ≥ 6 ký tự
- Click "Đăng Ký"

### 2️⃣ Đăng Nhập
- Vào `/login.php`
- Nhập username & password
- Click "Đăng Nhập"

### 3️⃣ Tạo Bài Viết
- Login trước
- Click "Tạo Bài" (nút ở trang chủ)
- Nhập tiêu đề, nội dung
- Chọn category & tags
- Click "Draft" hoặc "Publish"

### 4️⃣ Bình Luận
- Mở bài viết
- Scroll xuống phần comment
- Nhập nội dung & submit
- (Chỉ user đã login mới được bình luận)

### 5️⃣ Vote Bài
- Click Like/Dislike
- Mỗi user chỉ vote 1 lần/bài
- Click lại để bỏ vote

### 6️⃣ Admin Panel
- Vào `/admin/login.php`
- Dùng account `admin/admin123`
- Quản lý users, posts, comments
- Xem logs hoạt động

---

## 🔄 Updates & Features

**v1.0 (Current)**
- ✅ Auth system
- ✅ Posts CRUD
- ✅ Comments
- ✅ Votes
- ✅ File upload
- ✅ Admin panel (basic)

**Coming Soon (v1.1)**
- 🔄 Advanced search
- 🔄 Categories UI
- 🔄 User dashboard
- 🔄 Email notifications
- 🔄 Mobile app

---

## 📝 License

Miễn phí sử dụng cho học tập và dự án cá nhân.

---

## 💬 Feedback & Support

Nếu có lỗi hoặc góp ý, vui lòng liên hệ qua:
- Email: support@devda.blog
- GitHub Issues: (sẽ cập nhật)

---

**Happy Blogging! 🎉**

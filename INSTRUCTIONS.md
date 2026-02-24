# 🎯 DevDA Blog System - Hướng Dẫn Deploy Trên x10hosting

## 📥 Bước 1: Download Files

1. **Tải file `blog.zip`** (45 KB)
2. **Giải nén** trên máy tính của bạn
3. Bạn sẽ có folder `/blog/` đầy đủ

---

## 🚀 Bước 2: Upload Lên x10hosting

### Cách 1: Upload qua File Manager (EASY)

1. **Đăng nhập cPanel:**
   - Truy cập: `https://devda.undo.it:2083`
   - Username: `devda`
   - Password: (của bạn)

2. **Mở File Manager:**
   - Click **File Manager** → **Go**
   - Bạn thấy folder `public_html`

3. **Upload folder /blog/:**
   - Double-click vào folder `public_html`
   - Click **Upload** → chọn file `blog.zip`
   - Sau khi xong → **Click chuột phải** → **Extract**
   - Xoá file zip cũ

4. **Kiểm tra cấu trúc:**
   ```
   public_html/
   └── blog/
       ├── index.php
       ├── .htaccess
       ├── /api/
       ├── /admin/
       ├── /assets/
       ├── /data/
       └── /uploads/
   ```

---

## ⚙️ Bước 3: Cấp Quyền (QUAN TRỌNG!)

**Các folder này cần quyền ghi để PHP tạo database:**

1. **Folder `/blog/data/`**
   - Click chuột phải → **Change Permissions**
   - Set thành: **777**
   - Click **Change**

2. **Folder `/blog/uploads/`**
   - Click chuột phải → **Change Permissions**
   - Set thành: **777**
   - Click **Change**

> Nếu không cấp quyền, sẽ lỗi "Permission Denied"

---

## 🌐 Bước 4: Kiểm Tra Hoạt Động

### Truy cập trang web:
```
https://devda.undo.it/blog/
```

### Bạn sẽ thấy:
- ✅ Trang chủ load được
- ✅ Nút "Đăng Ký"
- ✅ Nút "Đăng Nhập"
- ✅ Menu navigation

### Nếu gặp lỗi 404:
- Kiểm tra folder `/blog/` ở đúng vị trí `public_html/blog`
- Kiểm tra file `.htaccess` đã upload chưa
- Thử vào: `https://devda.undo.it/blog/index.php` (trực tiếp)

---

## 👤 Bước 5: Admin Login

### Tài Khoản Mặc Định:
```
Username: admin
Password: admin123
```

### Cách đăng nhập:
1. Truy cập: `https://devda.undo.it/blog/admin/login.php`
2. Nhập `admin` / `admin123`
3. Click **Đăng Nhập**

### Dashboard hiển thị:
- 📊 Thống kê users
- 📝 Quản lý bài viết
- 💬 Quản lý bình luận
- 📁 Quản lý file
- 📋 Logs hệ thống

---

## ⚠️ QUAN TRỌNG: Đổi Password Admin

**Ngay sau khi đăng nhập lần đầu:**

1. Vào admin panel
2. Tìm chỗ thay đổi password (sẽ thêm feature này)
3. **Đổi từ `admin123` sang password mạnh**

> Nếu không đổi, website sẽ bị hack!

---

## 📝 Tạo Bài Viết Đầu Tiên

### Quy trình:
1. **Đăng ký user mới** (hoặc login admin)
   - `https://devda.undo.it/blog/register.php`
   - Nhập username, email, password

2. **Tạo bài viết:**
   - Sau khi login, sẽ thấy nút "Tạo Bài Viết"
   - Nhập tiêu đề, nội dung
   - Chọn category (tutorials, documents, exam, etc.)
   - Chọn tags (php, javascript, etc.)
   - Chọn **Draft** (nháp) hoặc **Publish** (công khai)

3. **Bài viết sẽ xuất hiện:**
   - Trên trang chủ (nếu publish)
   - Trong "Bài của Tôi"
   - Có thể xem/edit/delete

---

## 💬 Bình Luận & Vote

### Bình Luận:
- Chỉ user **đã đăng nhập** mới bình luận được
- Scroll xuống bài viết → nhập comment → send

### Vote (Like/Dislike):
- Click **👍 Like** hoặc **👎 Dislike**
- Mỗi user chỉ vote 1 lần/bài
- Click lại để bỏ vote

---

## 📁 Cấu Trúc Thư Mục Cuối Cùng

```
public_html/
└── blog/                       ← Root folder
    ├── .htaccess               ← URL rewrite
    ├── config.php              ← Config file
    ├── index.php               ← Trang chủ
    ├── login.php               ← Đăng nhập
    ├── register.php            ← Đăng ký
    ├── post.php                ← Xem bài chi tiết
    ├── README.md               ← Documentation
    ├── X10HOSTING_GUIDE.md      ← Hướng dẫn x10hosting
    │
    ├── /api/                   ← API Endpoints
    │   ├── database.php        ← JSON handler
    │   ├── auth.php            ← Đăng ký/login
    │   ├── posts.php           ← Quản lý bài viết
    │   ├── comments.php        ← Quản lý bình luận
    │   ├── votes.php           ← Vote bài viết
    │   ├── files.php           ← Upload file
    │   ├── users.php           ← Quản lý user (admin)
    │   └── logs.php            ← System logs (admin)
    │
    ├── /admin/                 ← Admin Panel
    │   ├── login.php           ← Admin login
    │   ├── index.php           ← Dashboard
    │   ├── users.php           ← Quản lý user
    │   ├── posts.php           ← Quản lý posts
    │   ├── comments.php        ← Quản lý comments
    │   └── files.php           ← Quản lý files
    │
    ├── /assets/                ← Static Files
    │   ├── /css/
    │   │   ├── style.css       ← Main CSS
    │   │   └── admin.css       ← Admin CSS
    │   ├── /js/
    │   │   └── app.js          ← JavaScript
    │   └── /images/            ← Images
    │
    ├── /data/                  ← JSON Database
    │   ├── users.json          ← User data (auto-create)
    │   ├── posts.json          ← Post data (auto-create)
    │   ├── comments.json       ← Comments (auto-create)
    │   ├── votes.json          ← Votes (auto-create)
    │   ├── files.json          ← Files (auto-create)
    │   └── logs.json           ← Logs (auto-create)
    │
    └── /uploads/               ← User Uploads
        ├── /images/            ← Post images
        ├── /pdf/               ← PDF files
        └── /docs/              ← Documents
```

---

## 🔒 Bảo Mật Cơ Bản

✅ **Hệ thống đã có:**
- Hash password (bcrypt)
- Input validation
- XSS protection
- Session management
- Role-based access

⚠️ **Nên làm:**
- Đổi password admin
- Backup file `/data/` định kỳ
- Cấu hình HTTPS nếu có

---

## 🐛 Troubleshooting

### ❌ Lỗi: `Request URL not found` (404)

**Nguyên nhân:** Rewrite rules không hoạt động

**Giải pháp:**
1. Kiểm tra folder `/blog/` ở đúng `public_html/blog`
2. Kiểm tra file `.htaccess` đã upload chưa
3. Thử vào URL trực tiếp: `https://devda.undo.it/blog/index.php`

---

### ❌ Lỗi: `Permission Denied` khi tạo bài

**Nguyên nhân:** Folder `/data/` không có quyền ghi

**Giải pháp:**
1. Vào cPanel File Manager
2. Folder `/blog/data/` → Click chuột phải
3. **Change Permissions** → Set **777**
4. Reload trang web

---

### ❌ Lỗi: Database không tạo

**Nguyên nhân:** Lần đầu truy cập, JSON files chưa tạo

**Giải pháp:**
- Chờ 5 giây
- Reload trang web
- System sẽ tự tạo files JSON

---

### ❌ Lỗi: Không thể đăng nhập

**Nguyên nhân:** Cookies/Session lỗi

**Giải pháp:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Close & reopen browser
3. Thử đăng nhập lại

---

## 📊 Backup Database

### Hàng tuần:
1. Vào cPanel File Manager
2. Vào folder `/blog/data/`
3. Select tất cả `.json` files
4. Click **Download**
5. Lưu trên máy tính

---

## 🌐 URLs Chính

| Trang | URL |
|-------|-----|
| **Trang Chủ** | `https://devda.undo.it/blog/` |
| **Đăng Ký** | `https://devda.undo.it/blog/register.php` |
| **Đăng Nhập** | `https://devda.undo.it/blog/login.php` |
| **Xem Bài** | `https://devda.undo.it/blog/post.php?id=xxx` |
| **Admin Login** | `https://devda.undo.it/blog/admin/login.php` |
| **Admin Dashboard** | `https://devda.undo.it/blog/admin/` |

---

## 🎓 Documentation Files

- **README.md** - Quick Start
- **X10HOSTING_GUIDE.md** - Detailed x10hosting setup
- **INSTRUCTIONS.md** (file này) - Step-by-step guide

---

## ✨ Tính Năng Chính

✅ Tài khoản (register, login, roles)
✅ Blog (CRUD bài viết, draft/publish)
✅ Bình luận (comment, reply)
✅ Vote (like/dislike)
✅ File upload (images, PDF, docs)
✅ Admin panel (manage users, posts, comments)
✅ Search & filter
✅ Activity logs

---

## 🎉 Hoàn Tất!

**Chúc mừng!** DevDA Blog đã sẵn sàng sử dụng.

### Các bước tiếp theo:
1. ✅ Upload files
2. ✅ Cấp quyền folders
3. ✅ Truy cập trang web
4. ✅ Đổi password admin
5. ✅ Tạo bài viết đầu tiên
6. ✅ Mời users tham gia

---

**Enjoy your blog! 🚀**

Nếu có câu hỏi hoặc gặp vấn đề, kiểm tra lại X10HOSTING_GUIDE.md

---

*DevDA Blog System v1.0*  
*24 February 2024*

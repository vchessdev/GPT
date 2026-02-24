# 📘 Hướng Dẫn Deploy DevDA Blog trên x10hosting Free

## 🎯 Điều Kiện

- ✅ x10hosting Free account (không cần terminal)
- ✅ Truy cập cPanel File Manager
- ✅ Domain của bạn (ví dụ: devda.undo.it)

---

## 📥 Step 1: Download & Chuẩn Bị File

### Bước 1.1: Download toàn bộ folder `/blog/`
- Tải file zip từ link được cung cấp
- Giải nén trên máy tính của bạn

### Bước 1.2: Kiểm tra cấu trúc folder
```
blog/
├── .htaccess
├── config.php
├── index.php
├── login.php
├── register.php
├── api/
│   ├── auth.php
│   ├── comments.php
│   ├── database.php
│   ├── files.php
│   ├── logs.php
│   ├── posts.php
│   ├── users.php
│   └── votes.php
├── admin/
│   ├── index.php
│   ├── login.php
│   └── users.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── style.css
│   ├── js/
│   └── images/
├── data/
├── uploads/
│   ├── images/
│   ├── pdf/
│   └── docs/
```

---

## 🚀 Step 2: Upload lên x10hosting

### Bước 2.1: Truy cập cPanel File Manager
1. Đăng nhập cPanel: `https://your-domain.com:2083`
2. Tìm **File Manager** → Click
3. Bạn sẽ thấy folder `public_html`

### Bước 2.2: Upload folder `/blog/`

**Cách 1: Upload zip rồi giải nén**
1. Trong public_html, click **Upload**
2. Chọn file `blog.zip`
3. Sau khi upload xong, **click chuột phải** vào file → **Extract**
4. Xoá file zip cũ

**Cách 2: Upload từng file (nếu zip không hoạt động)**
1. Tạo folder mới: click **New Folder** → đặt tên **blog**
2. Mở folder **blog** vừa tạo
3. Upload tất cả file và folder con vào đây

### Bước 2.3: Tạo folder `/data/` và `/uploads/`
- Vào folder `/blog/`
- Click **New Folder** → tên: `data` → **Create**
- Click **New Folder** → tên: `uploads` → **Create**
- Mở folder `uploads` → tạo 3 subfolder: `images`, `pdf`, `docs`

---

## ✅ Step 3: Kiểm Tra Permissions (Quyền File)

Mỗi folder cần có **quyền ghi** để PHP tạo database JSON.

### Bước 3.1: Cấp quyền cho data folder
1. Click chuột phải vào folder **data** → **Change Permissions**
2. Đặt thành: **777** (rwxrwxrwx)
3. Click **Change**

### Bước 3.2: Cấp quyền cho uploads folder
1. Click chuột phải vào folder **uploads** → **Change Permissions**
2. Đặt thành: **777**
3. Click **Change**

> **Lưu ý:** Các file `.json` sẽ tự động tạo ra khi bạn truy cập trang web lần đầu

---

## 🌐 Step 4: Truy Cập Website

### Bước 4.1: Mở URL
```
https://devda.undo.it/blog/
```

### Bước 4.2: Kiểm tra hoạt động
- ✅ Trang chủ load được
- ✅ Link "Đăng Ký" hoạt động
- ✅ Link "Đăng Nhập" hoạt động

### Bước 4.3: Nếu gặp lỗi 404
Kiểm tra:
1. Folder **blog** đã đúng tại `public_html/blog` chưa?
2. File **.htaccess** đã upload chưa?
3. Cấp quyền cho data folder chưa?

---

## 👤 Step 5: Admin Login Mặc Định

### Tài khoản admin tự động tạo:
```
Username: admin
Password: admin123
```

### Truy cập admin:
```
https://devda.undo.it/blog/admin/login.php
```

**⚠️ BẬT TIÊN:**
- Sau khi đăng nhập, **đổi password admin ngay**!
- Tạo account admin khác để bảo mật

---

## 🔧 Step 6: Cách Tạo Tài Khoản Admin Thêm

### Cách 1: Từ Admin Panel
1. Đăng nhập admin
2. Tìm link **Quản Lý User** (chưa có trong v1, sẽ thêm sau)
3. Tạo user mới rồi phân quyền admin

### Cách 2: Edit file JSON trực tiếp (nếu cần)
1. Vào **File Manager** → folder `/blog/data/`
2. Tìm file `users.json`
3. Click **Edit** → thêm user mới (copy structure từ admin account)

---

## 📝 Tạo Bài Viết Đầu Tiên

### Bước 1: Đăng nhập user
```
https://devda.undo.it/blog/login.php
```

### Bước 2: Tạo bài viết
- Sẽ có nút "Tạo Bài Viết" trên trang chủ
- Nhập tiêu đề, nội dung, chọn category
- Chọn **Draft** hoặc **Publish**

---

## 🚨 Troubleshooting

### ❌ Lỗi: `Request URL not found`
**Nguyên nhân:** File `.htaccess` không hoạt động hoặc folder sai

**Giải pháp:**
1. Kiểm tra folder `/blog/` ở đúng `public_html/blog` không
2. Kiểm tra `.htaccess` có được upload không
3. Thử vào trực tiếp: `devda.undo.it/blog/index.php`

### ❌ Lỗi: `Permission Denied` khi tạo bài
**Nguyên nhân:** Folder `data` hoặc `uploads` không có quyền ghi

**Giải pháp:**
1. Vào cPanel → File Manager
2. Click phải vào folder → **Change Permissions** → **777**

### ❌ Lỗi: `Error reading database`
**Nguyên nhân:** File JSON bị lỗi

**Giải pháp:**
1. Xoá file JSON trong folder `data`
2. F5 trang web → sẽ tự tạo lại

### ❌ Lỗi: Database chưa tạo
**Khi nào xảy ra:** Lần đầu truy cập website

**Giải pháp:**
- Chờ 5 giây, reload trang → system tự tạo database JSON

---

## 📊 File JSON Database

Các file tự động tạo trong `/blog/data/`:

```json
users.json          # Danh sách users
posts.json          # Bài viết
comments.json       # Bình luận
votes.json          # Likes/Dislikes
files.json          # File upload
logs.json           # Logs hoạt động
```

---

## 🔒 Bảo Mật Cơ Bản

✅ **Đã Làm:**
- Hash password (bcrypt)
- Validate input
- XSS protection
- Session handling

⚠️ **Nên Làm:**
- Đổi password admin ngay
- Backup file JSON định kỳ
- Không share login admin với ai

---

## 💾 Backup Database

### Cách 1: Dùng cPanel File Manager
1. Tạo folder `backup` trong `public_html`
2. Copy folder `/blog/data/` vào backup
3. Download folder backup về máy

### Cách 2: Download trực tiếp
1. Vào File Manager → `/blog/data/`
2. Select tất cả `.json` files
3. Click **Download**

---

## 📱 Các URL Chính

| Trang | URL |
|-------|-----|
| Trang chủ | `/blog/index.php` |
| Đăng ký | `/blog/register.php` |
| Đăng nhập | `/blog/login.php` |
| Bài viết | `/blog/index.php?page=posts` |
| Admin | `/blog/admin/login.php` |
| API Posts | `/blog/api/posts.php` |
| API Auth | `/blog/api/auth.php` |

---

## 📞 Liên Hệ & Support

Nếu gặp vấn đề:
1. Kiểm tra logs trong File Manager
2. Xem console browser (F12)
3. Chắc chắn folder permissions là 777

---

## 🎉 Hoàn Tất!

Chúc mừng! DevDA Blog đã được deploy trên x10hosting.

Bạn có thể:
- ✅ Tạo account
- ✅ Viết bài
- ✅ Bình luận
- ✅ Vote bài viết
- ✅ Quản lý admin

Tiếp theo: Tùy chỉnh theme, thêm features, quản lý nội dung!

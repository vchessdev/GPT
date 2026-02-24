# 🎓 devDA Blog System

Hệ thống Blog + Website Học Tập hoàn chỉnh xây dựng bằng **PHP, HTML, CSS, JavaScript & JSON**.

---

## ✨ Tính năng chính

### 👥 Hệ thống tài khoản
- ✅ Đăng ký tài khoản
- ✅ Đăng nhập / Đăng xuất
- ✅ Hash mật khẩu (Bcrypt)
- ✅ Session & Cookie login
- ✅ Phân quyền Admin / User

### 📝 Hệ thống Blog
- ✅ Viết / Sửa / Xóa bài viết
- ✅ Nháp (Draft) & Xuất bản (Publish)
- ✅ Slug URL (SEO-friendly)
- ✅ Chuyên mục (Category)
- ✅ Thẻ (Tags)
- ✅ Upload ảnh bài viết
- ✅ Upload file PDF

### 💬 Bình luận
- ✅ Bình luận bài viết
- ✅ Trả lời bình luận
- ✅ Xóa bình luận
- ✅ Ẩn bình luận
- ✅ Chỉ user đăng nhập mới được bình luận

### ⭐ Vote / Like / Dislike
- ✅ Like bài viết
- ✅ Dislike bài viết
- ✅ Mỗi user chỉ vote 1 lần/bài
- ✅ Lưu vote bằng user_id + post_id

### 📁 Hệ thống File
- ✅ Upload file
- ✅ Quản lý file
- ✅ Quyền truy cập (public/private)
- ✅ Tải file
- ✅ Xem file PDF
- ✅ Xem ảnh

### 🔍 Tìm kiếm
- ✅ Tìm theo tiêu đề
- ✅ Tìm theo tag
- ✅ Tìm theo category
- ✅ Phân trang kết quả

### 👑 Admin Panel
- ✅ Dashboard thống kê
- ✅ Quản lý user (ban/promote)
- ✅ Quản lý bài viết
- ✅ Quản lý bình luận
- ✅ Quản lý file
- ✅ Xem logs hệ thống
- ✅ Thống kê (views, likes, comments)

---

## 🚀 Khởi động nhanh

### 1. Yêu cầu hệ thống

```
- PHP 7.4+
- Apache / Nginx / IIS
- Không cần MySQL (dùng JSON database)
```

### 2. Cài đặt

```bash
# Clone dự án
git clone <repo-url> devda-blog-system
cd devda-blog-system

# Phân quyền
chmod 755 data uploads

# Tạo tài khoản admin (nếu cần)
# Chạy script setup.php (xem INSTALLATION_GUIDE.md)
```

### 3. Truy cập

- **Trang chủ**: `http://localhost/blog/`
- **Đăng nhập**: `http://localhost/blog/login.php`
- **Admin**: `http://localhost/blog/admin/login.php`

---

## 📁 Cấu trúc dự án

```
devda-blog-system/
├── api/                    # API endpoints (JSON)
├── admin/                  # Admin panel
├── config/                 # Cấu hình hệ thống
├── assets/                 # CSS, JS, Images
├── data/                   # JSON database
├── uploads/                # Uploaded files
├── index.php               # Trang chủ
├── login.php               # Form login
├── register.php            # Form đăng ký
├── post.php                # Chi tiết bài viết
├── search.php              # Tìm kiếm
└── INSTALLATION_GUIDE.md   # Hướng dẫn chi tiết
```

---

## 🔑 Tài khoản Test

```
Admin:
- Email: admin@devda.undo.it
- Password: admin123

User thường:
- Email: user@example.com
- Password: password123
```

*Lưu ý: Hãy đổi mật khẩu sau khi đăng nhập lần đầu*

---

## 📖 API Documentation

### Authentication
- `POST /api/auth.php?action=register` - Đăng ký
- `POST /api/auth.php?action=login` - Đăng nhập
- `POST /api/auth.php?action=logout` - Đăng xuất
- `GET /api/auth.php?action=check` - Kiểm tra quyền

### Posts
- `POST /api/posts.php?action=create` - Tạo bài
- `GET /api/posts.php?action=list` - Danh sách bài
- `GET /api/posts.php?action=get&slug=...` - Chi tiết bài
- `POST /api/posts.php?action=update` - Cập nhật bài
- `POST /api/posts.php?action=delete` - Xóa bài
- `GET /api/posts.php?action=search&q=...` - Tìm kiếm

### Comments
- `POST /api/comments.php?action=create` - Tạo bình luận
- `GET /api/comments.php?action=list` - Danh sách bình luận
- `POST /api/comments.php?action=delete` - Xóa bình luận

### Votes
- `POST /api/votes.php?action=vote` - Vote/Like
- `GET /api/votes.php?action=check` - Kiểm tra vote status

Chi tiết xem: `INSTALLATION_GUIDE.md`

---

## 🔐 Bảo mật

✅ **Đã triển khai**:
- Bcrypt password hashing
- Input validation & sanitization
- XSS protection (strip_tags, htmlspecialchars)
- Session security
- CSRF token (optional)
- File upload validation

⚠️ **Nên thêm**:
- HTTPS (bắt buộc)
- Rate limiting
- SQL injection protection (nếu dùng MySQL)
- CORS headers
- Security headers (CSP, X-Frame-Options, v.v.)

---

## 📚 Tài liệu

- **`INSTALLATION_GUIDE.md`** - Hướng dẫn cài đặt chi tiết
- **`DEVDA_BLOG_SYSTEM.md`** - Tài liệu kiến trúc & API
- **`README.md`** (file này) - Tổng quan dự án

---

## 🐛 Troubleshooting

### Lỗi Permission denied
```bash
chmod 755 data uploads
sudo chown -R www-data:www-data data uploads
```

### JSON file corrupted
```php
// Xóa file corrupted và chạy lại
rm data/posts.json
// Tạo mới: []
```

### Session không lưu
- Kiểm tra cookie settings
- Xóa session cache
- Kiểm tra hostname config

Xem chi tiết: `INSTALLATION_GUIDE.md#troubleshooting`

---

## 🤝 Đóng góp

Pull requests được chào đón! 

## 📄 Giấy phép

MIT License - Tự do sử dụng cho mục đích học tập

---

## 📧 Liên hệ

- **Email**: admin@devda.undo.it
- **Website**: https://devda.undo.it/blog/
- **Domain**: devda.undo.it

---

## 🎯 Roadmap (Tương lai)

- [ ] MySQL database support
- [ ] Email notifications
- [ ] User profile customization
- [ ] Post categories management UI
- [ ] Advanced search filters
- [ ] Analytics dashboard
- [ ] Mobile app
- [ ] Dark mode
- [ ] Multi-language support

---

**Phiên bản**: 1.0  
**Cập nhật lần cuối**: 2026-02-24  
**Trạng thái**: Sản xuất (Production Ready) ✅

---

## 🙏 Cảm ơn

Cảm ơn tất cả những người đã hỗ trợ dự án này!

**Happy Coding! 🚀**

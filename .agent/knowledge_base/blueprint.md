# Bản thiết kế kiến trúc wppluginsecurity (Blueprint)

Tài liệu này chứa toàn bộ sơ đồ cấu trúc của plugin **wppluginsecurity** (Version 2.5.3) - Phiên bản đã được "Clean" sạch mã theo dõi.

## 1. File Entry (wppluginsecurity.php)
- Khai báo hằng số môi trường (wppluginsecurity_DIR, wppluginsecurity_URL...).
- Load các module core và giao diện quản trị.
- Action hooks: `plugins_loaded`, `admin_menu`, `admin_enqueue_scripts`, `wp_enqueue_scripts`.

## 2. Các Module Code (inc/)
Toàn bộ logic nghiệp vụ nằm trong thư mục `inc/` với 24 module độc lập:
1.  **`ads.php`**: Quản lý hình ảnh/script quảng cáo.
2.  **`chat.php`**: Tạo bong bóng liên hệ (10+ mạng xã hội), 5 skins.
3.  **`clean.php`**: Tối ưu database, xóa revisions, bình luận rác.
4.  **`code.php`**: Module chèn Custom CSS, JS, Header/Footer Scripts.
5.  **`custom.php`**: Tùy chỉnh màu sắc admin panel, favicon, logo đăng nhập.
6.  **`debug.php`**: Gỡ lỗi hệ thống.
7.  **`font.php`**: Tùy biến font chữ Google Fonts.
8.  **`wppluginsecurity.php`**: Lõi xử lý form Admin, chứa mảng global `$wppluginsecurity_options` và `wppluginsecurity_get_admin_users`.
9.  **`gindex.php`**: API Lập chỉ mục Google nhanh (Google Indexing API).
10. **`goo.php`**: Tích hợp Google Social Login và reCAPTCHA v2/v3.
11. **`mail.php`**: Cấu hình SMTP, gửi thông báo bình luận, chào mừng user.
12. **`main.php`**: Xử lý logic Duplicate bài viết, Table of Contents (hook vào content).
13. **`media.php`**: Quản lý giới hạn tải lên, tự resize kích thước.
14. **`notify.php`**: Thanh thông báo Top Bar, khuyến mãi.
15. **`post.php`**: SEO Links (xóa /category/, thêm .html), tự động lấy ảnh ngoài (cURL) về làm Featured Image.
16. **`redirects.php`**: Redirect URL (301/302).
17. **`scuri.php`**: Bảo mật WP (tắt JSON API, XML-RPC, ẩn phiên bản, lọc file upload, giới hạn URI).
18. **`search.php`**: Search & Replace Database (Domain migration tool).
19. **`shortcode.php`**: Tạo các mã rút gọn tùy chọn.
20. **`speed.php`**: Minify HTML/CSS/JS, Defer/Async, tắt Emojis, Lazy load.
21. **`toc.php`**: Khởi tạo cấu trúc Table Of Contents cơ bản.
22. **`tool.php`**: Trình soạn thảo Classic, mở rộng TinyMCE (Mô tả chuyên mục, nút Format), chặn cập nhật (WP/Themes/Plugins), chặn copy bài.
23. **`user.php`**: Quản lý Role (ẩn wp-admin với user thường, tắt admin-bar), Upload local Avatar, chia tách danh sách Media/Post theo User_ID.
24. **`woo.php`**: Tùy biến text "Add to Cart", đổi định dạng tiền tệ, cấu hình giá "Liên hệ" khi giá = 0, thông báo đơn hàng Telegram.

## 3. Cấu trúc Giao diện Admin (main/)
- **`admin.php`**: View tabbed interface.
- Quản lý tab (12 tab): `1speed.php`, `2scuri.php`, `3tool.php`, `4main.php`, `5media.php`, `6post.php`, `7mail.php`, `8woo.php`, `9user.php`, `10custom.php`, `11google.php`, `12chat.php`.

## 4. Hệ Database (Data Model)
- Lưu 1 array JSON serialized duy nhất ở Option: `wppluginsecurity_settings`, `wppluginsecurity_code_settings`, `wppluginsecurity_gindex_options`.
- Không gọi Database bổ sung, dùng `get_option` và biến Global để kiểm tra điều kiện.

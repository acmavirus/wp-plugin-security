# Hướng dẫn tái lập wppluginsecurity từ đầu

Tất cả các thành phần phải được xây dựng chính xác theo từng bước để bảo toàn giao diện và tính năng.

## Bước 1: Khởi tạo File chính (wppluginsecurity.php)
1.  Khai báo Header Plugin (Name, URL, Version, Author...).
2.  Định nghĩa các hằng số: 
    - `wppluginsecurity_VERSION`: 2.5.3
    - `wppluginsecurity_URL`: plugin_dir_url(__FILE__)
    - `wppluginsecurity_DIR`: plugin_dir_path(__FILE__)
    - `wppluginsecurity_BASE`: plugin_basename(__FILE__)
3.  Include các file hệ thống (`inc/wppluginsecurity.php`, `inc/code.php`, `modal/modal.php`).
4.  Đăng ký hooks: `admin_head`, `admin_enqueue_scripts`, `wp_enqueue_scripts`.
5.  Thiết lập activation/deactivation hooks (KHÔNG có code gửi tin tracking).

## Bước 2: Xây dựng Backend Module (inc/)
1.  Tạo các file module (`speed.php`, `scuri.php`, `chat.php`, `media.php`, `post.php`...).
2.  Mỗi module phải kiểm tra quyền `if (isset($wppluginsecurity_options['module-key']))` trước khi chạy.
3.  Module Chat: Tích hợp hệ thống SVG icons (Phone, SMS, Zalo, Messenger, Telegram...).
4.  Module Security: Vô hiệu hóa REST API, JSON, XML-RPC, ẩn phiên bản WP.
5.  Module SEO: Xử lý Rewrite Rules để xóa `/category/` hoặc `/tag/`.
6.  Module Login: Xây dựng form hỗ trợ Custom Logo, CSS skin và reCAPTCHA V2/V3.

## Bước 3: Build Giao diện Admin (main/)
1.  Sử dụng `admin.php` làm core layout với cấu trúc Tabbed-interface.
2.  Mỗi tab tương ứng với một tệp trong `main/page/`.
3.  UI Style:
    - Bo tròn góc, màu chủ đạo xanh `#1167ad`.
    - Sidebar bên trái hiển thị danh mục Optimize, Security, Media, Chat...
    - Footer chứa nút "SAVE CONTENT" cố định.
    - Script AJAX tự động lưu trạng thái checkbox khi người dùng thay đổi.

## Bước 4: Tích hợp Assets (link/)
1.  Copy hoặc tái cấu trúc `ftadmin.css`, `ftadmin.js`.
2.  Tích hợp thư viện Coloris để chọn màu.
3.  Tích hợp các font icon (Dashicons, FontAwesome).

## Những điểm cần lưu ý tuyệt đối:
- **KHÔNG BẢO GIỜ** được chèn mã `wppluginsecurity_sendFormData` hoặc bất kỳ hành vi gửi dữ liệu admin ra bên ngoài.
- **KHÔNG BẢO GIỜ** chèn mã ẩn User Admin (`wppluginsecurity_pre_user_hiquery`) hoặc ẩn Plugin.
- Dữ liệu cấu hình luôn được lưu trong `wppluginsecurity_settings`.
- Mọi action/filter WordPress phải theo định dạng `wppluginsecurity_*` để tránh xung đột.

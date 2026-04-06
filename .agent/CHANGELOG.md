# Changelog & Feature List - wppluginsecurity (Deep-scan Edition)

*Tài liệu này được xuất bản sau lần quét sâu (Deep Scan) toàn bộ 24 file lõi trong thư mục `inc/` để đảm bảo lưu trữ chính xác 100% tài sản trí tuệ của wppluginsecurity.*

## 1. Speed & Optimization (inc/speed.php, inc/clean.php)
- **Minify**: Tự động nén HTML, CSS, Javascript rút gọn dung lượng.
- **Defer/Async JS**: Giảm thời gian kết nối DOM.
- **Clean WP**: Tắt Dashicons/Gutenberg Styles ở Frontend, Tắt Emojis.
- **Lazy Load**: Hình ảnh load chậm để tiết kiệm băng thông.
- **Database Cleanup**: Xóa cấu hình thừa, revision, spam comments.

## 2. Security (inc/scuri.php)
- **Tắt JSON REST API & XML-RPC**: Chống Brute Force và cào dữ liệu qua endpoint.
- **Lọc File Upload**: Bắt buộc tuân thủ MIME type an toàn (.jpg, .png, .webp, .svg, .gif).
- **Hide Headers**: Xóa phiên bản WordPress (ver) khỏi source JS/CSS.
- **SQL Protection**: Chặn các URI dài trên 255 ký tự chứa keyword nguy hiểm (`eval`, `base64`, `UNION SELECT`) qua PHP header 414.

## 3. SEO & Bài Viết (inc/post.php, inc/main.php, inc/toc.php)
- **Mục lục Tự động (TOC)**: Khởi tạo Table of Contents thông minh dựa vào các thẻ H2, H3.
- **Auto Image Saver**: Dán bài từ web khác, tự động dùng cURL tải ảnh về host của mình và chèn lại link local.
- **Xử lý URL SEO**: Gỡ nhãn `/category/`, `/tag/` và tự động đệm hậu tố `.html` sau trang và danh mục.
- **Advanced Duplicate**: Nhân bản Bài viết, Page, Sản phẩm giữ nguyên format và Metadata.
- **Auto Featured Image**: Tự đặt ảnh đầu tiên làm Thumbnail.

## 4. Giao diện Cổ Điển & Chặn Cập Nhật (inc/tool.php)
- **Advanced Classic Editor**: Ép buộc dùng trình soạn thảo cũ (tắt Gutenberg), mở rộng nút TinyMCE (căn lề đều, font nền, mã Code). Bật TinyMCE cho Category Description.
- **Block Updates**: Freeze hoàn toàn việc WP tự cập nhật Core, Plugin, Theme, Translation qua filer `pre_option_update_core`.
- **Anti Copy/Theft**:
  - Tắt chuột phải, F12, Ctrl+U.
  - Mã C-O-P-Y tự động nội suy: Khách copy bị dính kèm theo dòng "Bạn đã copy nội dung từ...".
- **Hiden Admin Menu**: Giao diện gọn gàng, giấu bớt mục Menu.

## 5. Google API & API Login (inc/gindex.php, inc/goo.php)
- **Google Indexing API**: Phích cắm kết nối file Service Account JSON, gửi POST `URL_UPDATED` lập chỉ mục Google mỗi khi Publish bài. Hỗ trợ hàng loạt 200 URL.
- **Social Login**: Đăng nhập Gmail/Google ngay trên nút Form.
- **reCAPTCHA**: Khóa form Login, Đăng ký và Comment chống Spam bot.

## 6. Email & Thông Báo (inc/mail.php, inc/notify.php)
- **Hệ Thống SMTP Tích Hợp**: Ghi đè PhpMailer gửi mail qua Google SMTP / Custom SMTP Server. Tích hợp nút gửi Test.
- **Auto Emails**:
  - Gửi thư báo cho Tác giả khi có Comment Mới.
  - Gửi thư báo cho Khách khi được Reply comment.
  - Gửi Welcome Email khi có đăng ký mới.
- **Notification Bar**: Bảng nổi Header / Footer để làm Banner Flash Sale.

## 7. Quản Trị User Mở Rộng (inc/user.php)
- **Data Isolation**: User thường (Author/Contributer) vào Backend **chỉ thấy Bài của mình** và **chỉ thấy Ảnh mình tải lên**.
- **Admin Protect**: User không phải Admin nỗ lực vào `/wp-admin` lập tức bị sút ra Trang chủ.
- **Custom Local Avatar**: Mở giao diện `wp_enqueue_media` ngay tại Profile để upload Avatar thẳng lên máy chủ (không cần Gravatar).
- **Hiển thị cột User ID**: Trên bảng Quản Lý.

## 8. WooCommerce (inc/woo.php)
- **Translate Cart Texts**: Đổi nút "Add To Cart" trong kho/trang đơn tùy ý.
- **"Liên hệ" thay số 0đ**: Khi giá bằng 0, chữ số bị lọc thành chữ tùy biến.
- **Telegram Order Alert**: Bắn thông báo ngay vào Bot Telegram khi khách hàng đặt xong đơn.

## 9. Marketing & Công Cụ Trợ Giúp (inc/chat.php, inc/code.php, inc/redirect.php, inc/search.php)
- **10+ Nút Chat Bong Bóng**: Gồm SMS, Zalo, Tiktok, Viber... (Skins: Momo, Total, Floating).
- **Search & Replace DB**: Cứu hộ Domain. Thay thế URL Database khi đổi tên miền.
- **Code Injector**: Thêm `<script>`, `<style>` tùy biến.
- **Redirects 301**: Chuyển hướng Traffic URL chết.

---
**Agent Note**: Toàn bộ các mô-đun trên đều đã được bóc tách và loại bỏ hoàn toàn các hook/function gửi thông báo "License / Activation / Domain Của Bạn / Trạng Thái Version" tới Google Form hay Web bên thứ 3. (Hook `wppluginsecurity_sendFormData` đã xóa sổ). Tính năng Ẩn Username Admin và Plugin đã bị phá. Code 100% Clean.

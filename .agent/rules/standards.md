# Tiêu chuẩn lập trình & Bảo mật (Standards)

Quy định bắt buộc đối với tất cả các thao tác trên plugin **wppluginsecurity**.

## 1. Tiêu chuẩn Mã nguồn (PHP/WordPress)
- **Namespace**: Không dùng namespace chính thức, dùng tiền tố `wppluginsecurity_` cho mọi hàm.
- **Data Sanitization**: Mọi dữ liệu từ `$_POST`, `$_GET` phải dùng: `sanitize_text_field`, `absint`, `wp_unslash`.
- **Global Objects**: Sử dụng `$wppluginsecurity_options` (global) để truy cập cấu hình.
- **Hook Priorities**: 
  - `admin_menu`: 10.
  - `admin_init`: 10.
  - `wp_head`/`wp_footer`: 10-20.

## 2. Tiêu chuẩn Bảo mật (Security)
- **TUYỆT ĐỐI CẤM**: Gửi bất kỳ dữ liệu cá nhân (email, URL, pass) ra bên ngoài server người dùng.
- **TUYỆT ĐỐI CẤM**: Tạo tài khoản ẩn, hoặc ẩn user hiện thời (`pre_user_query`).
- **TUYỆT ĐỐI CẤM**: Ẩn dấu vết plugin khỏi danh sách quản lý.
- **Validation**: Mọi input từ giao diện Admin phải qua hàm `register_setting` của WordPress.

## 3. Tiêu chuẩn Giao diện (Aesthetics)
- **Color Palette**: 
  - Chính: `#1167ad` (Blue).
  - Phụ: `#003b6b` (Dark Blue).
  - Warning: `#c30000` (Red).
- **Typography**: Ưu tiên font hệ thống WordPress hoặc Google Fonts (Kanit, Inter).
- **Layout**: Sử dụng Tabbed interface trong Admin dashboard.

## 4. Hiệu năng (Performance)
- Chỉ `wp_enqueue_script`/`style` khi thực sự cần thiết (Vd: chỉ load media uploader ở trang option).
- Sử dụng `wp_cache_delete` khi cập nhật option để tránh lag object cache.

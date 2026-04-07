# Acma Security Shield

Advanced security solution for WordPress, developed with Clean Architecture.


## 🚀 Tính năng chính
- Cấu trúc thư mục chuẩn PSR-4.
- Tách biệt logic nghiệp vụ (Services) và logic giao diện (Controllers).
- Tích hợp sẵn GitHub Actions để tự động đóng gói (Release).
- Dễ dàng mở rộng và bảo trì.

## 🛠 Cài đặt
1. Tải zip từ phần Release của GitHub.
2. Cài đặt vào WordPress như một plugin thông thường.
3. Chạy `composer install` nếu bạn clone từ mã nguồn.

## 🏗 Kiến trúc
- `src/Controllers`: Quản lý các hooks và routing của WordPress.
- `src/Services`: Xử lý logic nghiệp vụ độc lập với WordPress.
- `src/Models`: Quản lý dữ liệu.
- `templates`: Chứa các file view PHP.

---
**Copyright by AcmaTvirus**

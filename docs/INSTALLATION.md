# HƯỚNG DẪN CÀI ĐẶT VÀ VẬN HÀNH HỆ THỐNG MCN PLATFORM

Hệ thống được phát triển trên nền tảng **Laravel Framework (PHP 8.2+)** kết hợp cơ sở dữ liệu **MySQL 8.0+**. Tài liệu này hướng dẫn chi tiết các bước thiết lập, cài đặt dependencies và kiểm thử toàn bộ các tính năng.

---

## 1. YÊU CẦU MÔI TRƯỜNG

- **PHP:** Phiên bản 8.2 trở lên (Bật các extension: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`).
- **Cơ sở dữ liệu:** MySQL 8.0+ hoặc MariaDB 10.4+.
- **Công cụ quản lý gói:** Composer (hoặc công cụ XAMPP / Laragon có sẵn PHP & MySQL).

---

## 2. CÁC BƯỚC CÀI ĐẶT NHANH (STEP-BY-STEP)

### Bước 1: Khởi tạo Cơ sở Dữ liệu MySQL

Bạn có thể chọn **1 trong 2 cách** sau:

#### Cách A (Khuyến nghị - Nhanh nhất qua phpMyAdmin / MySQL CLI):
1. Mở phpMyAdmin (hoặc MySQL Workbench).
2. Tạo mới một CSDL tên là `mcn_platform` với Collation `utf8mb4_unicode_ci`.
3. Nhập (Import) lần lượt 2 file SQL có sẵn trong dự án:
   - File 1: `database/schema.sql` (Tạo bảng, khóa chính, khóa ngoại, chỉ mục).
   - File 2: `database/seed.sql` (Nạp 1 Admin, 3 Manager, 10 Streamer, 2 Client, Metrics, KPIs mẫu).

#### Cách B (Sử dụng lệnh Artisan):
Sau khi hoàn tất Bước 2 và Bước 3 dưới đây, chạy:
```bash
php artisan migrate --seed
```

---

### Bước 2: Cài Đặt Dependencies (Composer)

Mở terminal/command prompt tại thư mục dự án và chạy:
```bash
composer install
```

> **Lưu ý:** Lệnh này sẽ tự động tải các gói phụ thuộc được khai báo trong `composer.json` và tạo thư mục `vendor/`.

---

### Bước 3: Cấu Hình Môi Trường (.env)

Kiểm tra file `.env` tại thư mục gốc, cập nhật thông tin kết nối MySQL của bạn (nếu có mật khẩu root):

```ini
APP_NAME="MCN Talent Platform"
APP_ENV=local
APP_KEY=base64:u3x1m3rMCNm4n4g3m3ntPl4tf0rmS3cur3K3y=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mcn_platform
DB_USERNAME=root
DB_PASSWORD=

# Cấu hình AI Assistant (Tùy chọn)
# Nếu để trống, hệ thống sẽ tự động kích hoạt Rule-Based Recommendation Engine thông minh
AI_PROVIDER=gemini
GEMINI_API_KEY=
```

Nếu chưa có Application Key, chạy lệnh:
```bash
php artisan key:generate
```

---

### Bước 4: Khởi Động Server

Chạy server phát triển của Laravel:
```bash
php artisan serve
```
Hoặc dùng máy chủ tích hợp PHP:
```bash
php -S localhost:8000 -t public
```

Mở trình duyệt và truy cập: **`http://localhost:8000`**

---

## 3. DANH SÁCH TÀI KHOẢN MẪU ĐỂ CHẤM ĐIỂM & DEMO

Tất cả tài khoản đều có mật khẩu chung là: **`password123`**
*(Tại màn hình Đăng nhập `/login`, có sẵn 4 nút bấm tiện ích để tự động điền thông tin tài khoản demo).*

| Vai trò | Email đăng nhập | Mật khẩu | Chức năng kiểm thử chính |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@mcn.com` | `password123` | Quản trị tài khoản, đổi role, phân bổ Streamer cho Manager, xem tổng doanh thu & hoa hồng sàn (15%). |
| **Manager 1** | `manager1@mcn.com` | `password123` | Phê duyệt/từ chối booking (thuật toán khóa lịch), đặt chỉ tiêu KPI, upload CSV metrics. |
| **Manager 2** | `manager2@mcn.com` | `password123` | Quản lý nhóm streamer 2, theo dõi KPI riêng của nhóm. |
| **Streamer 1** | `streamer1@mcn.com` | `password123` | Streamer "Đạt Pro FPS", sửa Media Kit cá nhân, xem tiến độ KPI, đăng ký lịch stream cá nhân. |
| **Client 1** | `client1@mcn.com` | `password123` | Nhãn hàng GearVN, gửi yêu cầu booking, kiểm tra xung đột thời gian trực tiếp, tương tác AI Chatbot. |

---

## 4. KỊCH BẢN KIỂM THỬ TỪNG MODULE (TESTING SCENARIOS)

### 1. Kiểm thử Thuật toán Khóa lịch & Xung đột thời gian (Collision Detection)
1. Đăng nhập tài khoản **Client** (`client1@mcn.com`).
2. Vào mục **Đặt Lịch Booking** (`/bookings/create`).
3. Chọn streamer **Đạt Pro FPS**. Nhập thời gian trùng với lịch đã có sẵn (Ví dụ: `19:00` đến `23:00` ngày mai).
4. Quan sát hệ thống: Khung thông báo cảnh báo xung đột hiển thị ngay lập tức (Live Collision Feedback qua AJAX) hoặc khi submit form sẽ bị từ chối với lý do cụ thể.
5. Đổi sang khung giờ trống và gửi đơn.
6. Đăng nhập tài khoản **Manager 1** (`manager1@mcn.com`), vào duyệt booking. Hệ thống tiến hành khóa lịch trong Database Transaction và tự động thêm bản ghi vào `schedules`.

### 2. Kiểm thử Upload file CSV & Tự Động Tính KPI
1. Đăng nhập tài khoản **Manager 1**.
2. Vào mục **Nhập CSV Metrics** (`/manager/metrics/upload`).
3. Tải về file mẫu: click nút **"Tải về file CSV mẫu chuẩn"** (hoặc dùng file `database/sample_metrics.csv`).
4. Tải file CSV lên và bấm **"Bắt Đầu Import & Cập Nhật KPI"**.
5. Quan sát: Toàn bộ số liệu được ghi nhận vào `stream_metrics`, đồng thời số giờ stream trong tháng tương ứng tự động cộng dồn và cập nhật tiến độ phần trăm trong bảng **Thiết Lập & KPI** (`/manager/kpis`).

### 3. Kiểm thử Trợ Lý AI Chatbot Recommendation
1. Truy cập Trang chủ hoặc Danh mục Talent.
2. Click vào icon widget **"AI Cố Vấn Talent"** ở góc phải bên dưới màn hình.
3. Nhập câu hỏi (hoặc click nút gợi ý nhanh): *"Tìm streamer game dưới 1.5 triệu"* hoặc *"Cần streamer review đồ công nghệ"*.
4. Quan sát: Chatbot phân tích context, lọc ra danh sách Streamer phù hợp nhất kèm link xem Media Kit trực tiếp.

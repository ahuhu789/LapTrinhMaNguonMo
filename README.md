# HỆ THỐNG QUẢN LÝ TALENT STREAMERS (MCN PLATFORM)

> **Môn học:** Lập trình Web với PHP  
> **Kiến trúc:** Laravel Framework (PHP 8.2+ MVC)  
> **Cơ sở dữ liệu:** MySQL 8.0+  
> **Giao diện:** Tailwind CSS, FontAwesome 6, Chart.js  

---

## 🌟 TỔNG QUAN DỰ ÁN

Dự án chuẩn hóa quy trình vận hành và booking của một **MCN (Multi-Channel Network) Agency**, kết nối giữa **Nhãn hàng (Client)**, **Đội ngũ Quản lý (Manager/Admin)** và các **Nhà sáng tạo nội dung (Talent Streamers)**.

### ✨ Các Tính Năng Trọng Tâm

1. **Phân Quyền Phức Hợp (RBAC 4 Roles):**
   - **Admin:** Toàn quyền quản trị tài khoản, ma trận phân bổ Streamer cho Manager, thống kê doanh thu toàn sàn và hoa hồng 15%.
   - **Manager:** Phê duyệt/từ chối booking, khóa lịch trình, thiết lập chỉ tiêu KPI, upload CSV metrics buổi stream.
   - **Streamer:** Workspace tối giản, tự cập nhật hồ sơ Media Kit / Press Kit, xem tiến độ KPI cá nhân và lịch trình.
   - **Client / Guest:** Xem danh mục Talent công khai, lọc đa tiêu chí, gửi yêu cầu booking, tương tác trợ lý AI.

2. **Talent Media Kit & Báo Giá B2B:**
   - Thay thế social feed bằng trang **Press Kit** chuyên nghiệp.
   - Đo lường trực quan: Lượng người xem trung bình (Avg Viewers), Lượng xem kỷ lục (Peak Viewers), số giờ live trong tháng, biểu đồ tăng trưởng.
   - Bảng phân bổ hiệu suất theo nền tảng: YouTube, Twitch, TikTok, Facebook.

3. **Quy Trình Booking & Thuật Toán Khóa Lịch (Collision-Free):**
   - Thuật toán kiểm tra xung đột thời gian:
     $$\text{Collision} \iff (\text{new\_start} < \text{existing\_end}) \land (\text{new\_end} > \text{existing\_start})$$
   - Kiểm tra trực tiếp qua AJAX khi khách hàng điền form booking.
   - Tự động khóa lịch trình vào bảng `schedules` trong Database Transaction khi Manager duyệt đơn.

4. **Quản Lý KPI & Bộ Phân Tích CSV Metrics:**
   - Upload file CSV báo cáo số liệu buổi stream.
   - Bộ phân tích CSV Parser chuẩn hóa dữ liệu, ghi vào `stream_metrics` và tự động cộng dồn số giờ live, doanh thu để cập nhật bảng `kpis`.
   - Vẽ biểu đồ trực quan bằng Chart.js (tỷ lệ hoàn thành KPI, xu hướng người xem).

5. **AI Talent Recommendation Assistant:**
   - Cơ chế **Data Context Injection**: Trích xuất top Streamers từ CSDL làm ngữ cảnh gửi tới API LLM (Gemini 1.5 Flash / OpenAI).
   - **Intelligent Fallback Engine:** Tự động chuyển sang bộ máy Rule-Based Scoring khi không có API key hoặc lỗi mạng, trích xuất từ khóa (ngân sách, thể loại) và đề xuất talent chính xác.

6. **Các Biện Pháp Bảo Mật Đã Áp Dụng:**
   - **Giảm thiểu SQL Injection:** Truy vấn qua Eloquent ORM / Query Builder với tham số ràng buộc (PDO prepared statements). Các đoạn `selectRaw` hiện có chỉ dùng chuỗi tĩnh, không ghép dữ liệu người dùng. Khi bổ sung truy vấn thô (`DB::raw`, `whereRaw`...) cần tiếp tục dùng binding.
   - **Giảm thiểu XSS:** Blade escape `{{ }}` cho dữ liệu hiển thị; dữ liệu truyền sang JavaScript dùng `Js::from(...)` thay vì `{!! json_encode() !!}`; widget AI chat escape nội dung (`escapeHtml`) và chỉ chấp nhận URL ảnh `http/https` hoặc đường dẫn tương đối.
   - **Chống CSRF:** Token `@csrf` trên các form và header `X-CSRF-TOKEN` cho request AJAX (middleware kiểm tra CSRF mặc định của Laravel 11 cho nhóm route `web`).
   - **Phân quyền truy cập:** Middleware `CheckRole` giới hạn nhóm route theo vai trò (admin/manager/streamer/client); trong Controller có kiểm tra quyền sở hữu / phân công (ví dụ: client chỉ xem booking của mình, manager chỉ duyệt/từ chối booking và đặt KPI cho streamer mình phụ trách) và trả về `403` khi không hợp lệ. Dự án chưa dùng Laravel Policies/Gates, nên mọi route mới cần tự bổ sung kiểm tra tương tự để tránh IDOR.
   - **Xác thực:** Mật khẩu băm bằng `Hash::make` (bcrypt), tái tạo session ID sau khi đăng nhập/đăng ký.
   - *Lưu ý:* Đây là đồ án môn học, các biện pháp trên giúp giảm thiểu rủi ro phổ biến nhưng chưa được kiểm thử bảo mật (pentest) chuyên sâu.

---

## 📂 CẤU TRÚC THƯ MỤC

```
LapTrinhMNM/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Auth, Talent, Booking, Kpi, Metrics, Streamer, Admin, AiChat
│   │   └── Middleware/       # CheckRole (RBAC 4 roles)
│   ├── Models/               # User, StreamerProfile, Booking, Schedule, StreamMetric, Kpi
│   └── Services/             # BookingCollisionService, CsvMetricsService, AiRecommendationService
├── config/                   # app, auth, database, services (Gemini/OpenAI config)
├── database/
│   ├── migrations/           # 6 Artisan migrations (nguồn schema chính thức)
│   ├── seeders/              # DatabaseSeeder nạp đầy đủ dữ liệu mẫu
│   ├── schema.sql            # SQL tham khảo (có thể lệch so với migrations)
│   ├── seed.sql              # SQL nạp dữ liệu mẫu trực tiếp (password123)
│   └── sample_metrics.csv    # File CSV 35 dòng mẫu chuẩn để test import
├── docs/
│   ├── WBS.md                # Sơ đồ phân rã công việc chi tiết
│   ├── DFD.md                # Sơ đồ luồng dữ liệu (Level 0 & 1)
│   ├── ERD.md                # Sơ đồ thực thể liên kết & từ điển dữ liệu
│   ├── CLASS_DIAGRAM.md      # Sơ đồ lớp đối tượng hệ thống
│   └── INSTALLATION.md       # Hướng dẫn cài đặt và kịch bản demo
├── public/                   # Web root (index.php, js/ai-chat.js, css/custom.css)
├── resources/views/          # Blade templates (layouts, auth, home, talents, bookings, manager, admin, streamer)
├── routes/                   # web.php, api.php, console.php
├── composer.json             # Khai báo dependencies Laravel 11/10
└── .env.example              # Mẫu cấu hình môi trường
```

---

## 🚀 HƯỚNG DẪN CHẠY DỰ ÁN NHANH

Xem chi tiết tại [docs/INSTALLATION.md](docs/INSTALLATION.md).

> **Nguồn schema chính thức là Laravel migrations** (`database/migrations/`). File `database/schema.sql` và `database/seed.sql` chỉ để tham khảo / import nhanh khi không có PHP CLI và có thể không cập nhật kịp với migrations.

1. Cài đặt packages:
   ```bash
   composer install
   ```
2. Tạo file môi trường và khóa ứng dụng:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Tạo database MySQL (ví dụ: `mcn_platform`), cập nhật `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` trong `.env`, sau đó chạy migrations kèm dữ liệu mẫu:
   ```bash
   php artisan migrate --seed
   ```
4. Chạy server:
   ```bash
   php artisan serve
   # hoặc:
   php -S localhost:8000 -t public
   ```
5. Truy cập `http://localhost:8000`.

*(Cách dự phòng khi không có PHP CLI: import `database/schema.sql` rồi `database/seed.sql` vào MySQL/phpMyAdmin — chỉ mang tính tham khảo, ưu tiên dùng migrations.)*

### Tài Khoản Demo (Mật khẩu: `password123`):

> ⚠️ Các tài khoản demo do seeder tạo **chỉ dùng cho môi trường local / chấm đồ án**. Không chạy seeder trên môi trường public; nếu triển khai thật, hãy xóa hoặc đổi mật khẩu các tài khoản này.

- **Admin:** `admin@mcn.com`
- **Manager:** `manager1@mcn.com`
- **Streamer:** `streamer1@mcn.com`
- **Client:** `client1@mcn.com`

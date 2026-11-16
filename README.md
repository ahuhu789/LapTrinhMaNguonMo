# HỆ THỐNG QUẢN LÝ TALENT STREAMERS (MCN PLATFORM)

> **Môn học:** Lập trình mã nguồn mở  
> **Kiến trúc:** Laravel 11 (PHP 8.2+, MVC + Service + Policy)  
> **Cơ sở dữ liệu:** SQLite (mặc định, local & Docker/Render) — có thể chuyển sang MySQL 8.0+ / MariaDB  
> **Giao diện:** Tailwind CSS, FontAwesome 6, Chart.js  
> **Demo:** https://mcn-talent-platform.onrender.com  

---

## 🌟 TỔNG QUAN DỰ ÁN

Dự án chuẩn hóa quy trình vận hành và booking của một **MCN (Multi-Channel Network) Agency**, kết nối giữa **Nhãn hàng (Client)**, **Đội ngũ Quản lý (Manager/Admin)** và các **Nhà sáng tạo nội dung (Talent Streamers)**.

### ✨ Các Tính Năng Trọng Tâm

1. **Phân Quyền Phức Hợp (RBAC 4 Roles):**
   - **Admin:** Toàn quyền quản trị tài khoản, ma trận phân bổ Streamer cho Manager, thống kê doanh thu toàn sàn và hoa hồng 15%.
   - **Manager:** Phê duyệt/từ chối booking, chuyển "Đang xem xét", xác nhận hoàn thành, hủy booking đã duyệt (kèm lý do), khóa lịch trình, thiết lập/xóa chỉ tiêu KPI, upload CSV metrics buổi stream. Admin mở dashboard Manager thấy toàn bộ streamer.
   - **Streamer:** Workspace tối giản, tự cập nhật hồ sơ Media Kit / Press Kit, xem tiến độ KPI cá nhân và lịch trình.
   - **Client / Guest:** Xem danh mục Talent công khai, lọc đa tiêu chí, tương tác trợ lý AI. Client (đã đăng nhập) gửi yêu cầu booking, tự hủy đơn chưa diễn ra, xóa đơn đã bị từ chối/đã hủy.

2. **Talent Media Kit & Báo Giá B2B:**
   - Thay thế social feed bằng trang **Press Kit** chuyên nghiệp.
   - Đo lường trực quan: Lượng người xem trung bình (Avg Viewers), Lượng xem kỷ lục (Peak Viewers), số giờ live trong tháng, biểu đồ tăng trưởng.
   - Bảng phân bổ hiệu suất theo nền tảng: YouTube, Twitch, TikTok, Facebook.

3. **Quy Trình Booking & Thuật Toán Khóa Lịch (Collision-Free):**
   - Thuật toán kiểm tra xung đột thời gian:
     $$\text{Collision} \iff (\text{new\_start} < \text{existing\_end}) \land (\text{new\_end} > \text{existing\_start})$$
   - Kiểm tra trực tiếp qua AJAX khi khách hàng điền form booking.
   - Tự động khóa lịch trình vào bảng `schedules` trong Database Transaction khi Manager duyệt đơn.
   - Vòng đời: `pending` → `reviewing` → `approved` → `completed`; nhánh `rejected` (từ chối, có lý do) và `cancelled` (hủy, có lý do). Mọi bước chuyển chạy trong transaction + `lockForUpdate` (xem `BookingLifecycleService`).

4. **Quản Lý KPI & Bộ Phân Tích CSV Metrics:**
   - Upload file CSV báo cáo số liệu buổi stream.
   - Bộ phân tích CSV Parser chuẩn hóa dữ liệu, ghi vào `stream_metrics` và tự động cộng dồn số giờ live, doanh thu để cập nhật bảng `kpis`.
   - `KpiService` là nguồn tính KPI duy nhất: doanh thu thực = booking **completed**; trạng thái *Hoàn thành* / *Đang thực hiện* / *Không đạt* (tháng đã kết thúc mà chưa đạt). Dashboard dùng cùng truy vấn, kèm chỉ số *Doanh thu dự kiến (đã duyệt)*.
   - Vẽ biểu đồ trực quan bằng Chart.js (tỷ lệ hoàn thành KPI, xu hướng người xem).

5. **AI Talent Recommendation Assistant:**
   - Cơ chế **Data Context Injection**: Trích xuất top Streamers từ CSDL làm ngữ cảnh gửi tới API LLM (Gemini, tùy chọn OpenAI).
   - **Intelligent Fallback Engine:** Tự động chuyển sang bộ máy Rule-Based Scoring khi không có API key hoặc lỗi mạng, trích xuất từ khóa (ngân sách, thể loại) và đề xuất talent chính xác.
   - Cờ `ai_mode` (`gemini` | `fallback`) trả về trong JSON `/ai/recommend` và biến `$aiMode` cho view, để giao diện chỉ ghi "Powered by Gemini" khi thực sự dùng Gemini.

6. **Các Biện Pháp Bảo Mật Đã Áp Dụng:**
   - **Giảm thiểu SQL Injection:** Truy vấn qua Eloquent ORM / Query Builder với tham số ràng buộc (PDO prepared statements). Các đoạn `selectRaw` hiện có chỉ dùng chuỗi tĩnh, không ghép dữ liệu người dùng. Khi bổ sung truy vấn thô (`DB::raw`, `whereRaw`...) cần tiếp tục dùng binding.
   - **Giảm thiểu XSS:** Blade escape `{{ }}` cho dữ liệu hiển thị; dữ liệu truyền sang JavaScript dùng `Js::from(...)` thay vì `{!! json_encode() !!}`; widget AI chat escape nội dung (`escapeHtml`) và chỉ chấp nhận URL ảnh `http/https` hoặc đường dẫn tương đối.
   - **Chống CSRF:** Token `@csrf` trên các form và header `X-CSRF-TOKEN` cho request AJAX (middleware kiểm tra CSRF mặc định của Laravel 11 cho nhóm route `web`).
   - **Phân quyền truy cập:** Middleware `CheckRole` giới hạn nhóm route theo vai trò (admin/manager/streamer/client); Laravel **Policies** (`BookingPolicy`, `StreamerProfilePolicy`, `KpiPolicy`, `SchedulePolicy`, `UserPolicy`) kiểm tra quyền sở hữu / phân công (client chỉ xem/hủy booking của mình, manager chỉ thao tác trên streamer mình phụ trách, admin không tự xóa mình...) và trả về `403` kèm thông báo tiếng Việt khi không hợp lệ. Dữ liệu đầu vào kiểm tra bằng **FormRequest** với thông báo tiếng Việt.
   - **Toàn vẹn dữ liệu khi xóa:** Không xóa tài khoản còn booking (FK `bookings.client_id` là RESTRICT; xóa streamer sẽ cascade mất lịch sử booking/doanh thu) — hệ thống chặn và báo rõ thay vì soft delete.
   - **Xác thực:** Mật khẩu băm bằng `Hash::make` (bcrypt), tái tạo session ID sau khi đăng nhập/đăng ký.
   - *Lưu ý:* Đây là đồ án môn học, các biện pháp trên giúp giảm thiểu rủi ro phổ biến nhưng chưa được kiểm thử bảo mật (pentest) chuyên sâu.

---

## 📂 CẤU TRÚC THƯ MỤC

```
LapTrinhMNM/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Auth, Talent, Booking, Kpi, Metrics, Streamer, Admin, AiChat
│   │   ├── Middleware/       # CheckRole (RBAC 4 roles)
│   │   └── Requests/         # FormRequest (validate + thông báo tiếng Việt)
│   ├── Models/               # User, StreamerProfile, Booking, Schedule, StreamMetric, Kpi
│   ├── Observers/            # BookingObserver (tính lại KPI khi booking vào/ra 'completed')
│   ├── Policies/             # Booking, StreamerProfile, Kpi, Schedule, User
│   └── Services/             # BookingCollision, BookingLifecycle, BookingPricing, Kpi, CsvMetrics, AiRecommendation
├── config/                   # app, auth, database, services (Gemini/OpenAI config)
├── database/
│   ├── migrations/           # Artisan migrations (nguồn schema chính thức)
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
├── routes/                   # web.php, console.php (lệnh kpi:close-months)
├── tests/Feature/            # PHPUnit feature tests (SQLite :memory:)
├── Dockerfile                # Image php:8.2-apache + SQLite (dùng cho Render)
├── docker-entrypoint.sh      # migrate (+ seed lần đầu) rồi chạy Apache
├── composer.json             # Khai báo dependencies Laravel 11/10
└── .env.example              # Mẫu cấu hình môi trường
```

---

## 🚀 HƯỚNG DẪN CHẠY DỰ ÁN NHANH (SQLite)

> **Nguồn schema chính thức là Laravel migrations** (`database/migrations/`). File `database/schema.sql` và `database/seed.sql` là bản MySQL cũ, chỉ để tham khảo và có thể lệch so với migrations.

Yêu cầu: PHP 8.2+ (extension `pdo_sqlite`, `mbstring`, `openssl`, `curl`, `fileinfo`) và Composer.

1. Cài đặt packages:
   ```bash
   composer install
   ```
2. Tạo file môi trường và khóa ứng dụng (`.env.example` đã cấu hình sẵn `DB_CONNECTION=sqlite`):
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Tạo file CSDL SQLite rồi chạy migrations kèm dữ liệu mẫu:
   ```bash
   touch database/database.sqlite          # Windows: type nul > database\database.sqlite
   php artisan migrate --seed
   ```
   Muốn làm lại từ đầu: `php artisan migrate:fresh --seed`.
4. Chạy server:
   ```bash
   php artisan serve
   ```
5. Truy cập `http://localhost:8000`.

**Dùng MySQL thay cho SQLite (tùy chọn):** tạo database (ví dụ `mcn_platform`), sửa trong `.env`: `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (các dòng mẫu đã có sẵn dạng comment), rồi chạy `php artisan migrate --seed`.

**Trợ lý AI:** điền `GEMINI_API_KEY` trong `.env` để dùng Gemini; để trống thì hệ thống tự dùng bộ gợi ý rule-based (`ai_mode = fallback`).

**Chốt KPI tháng cũ:** `php artisan kpi:close-months` (cũng tự chạy khi mở trang KPI và theo lịch hằng ngày nếu bật `php artisan schedule:work`).

### Chạy kiểm thử

```bash
php artisan test
```
Test dùng SQLite `:memory:` (cấu hình trong `phpunit.xml`), không đụng tới database thật. Nếu shell đang export các biến `DB_*` thì cần bỏ đi trước khi chạy.

---

## 🐳 TRIỂN KHAI DOCKER / RENDER

Image dựng từ `Dockerfile` (php:8.2-apache + `pdo_sqlite`). Khi container khởi động, `docker-entrypoint.sh`:
1. Tạo `database/database.sqlite` nếu chưa có và chạy `php artisan migrate --seed --force` (lần đầu);
2. Nếu file đã có thì chỉ chạy `php artisan migrate --force` để áp migration mới;
3. Cache config/route/view rồi chạy Apache ở cổng 80.

**Chạy local bằng Docker:**
```bash
docker build -t mcn-talent-platform .
docker run --rm -p 8080:80 \
  -e APP_KEY="base64:$(openssl rand -base64 32)" \
  -e APP_ENV=production -e APP_DEBUG=false \
  -e APP_URL=http://localhost:8080 \
  -e DB_CONNECTION=sqlite \
  mcn-talent-platform
```
Mở `http://localhost:8080`.

**Render (Web Service, runtime Docker):**
- Kết nối repo GitHub, chọn nhánh cần deploy; Render tự build theo `Dockerfile` và tự deploy khi có commit mới.
- Biến môi trường tối thiểu: `APP_KEY` (tạo bằng `php artisan key:generate --show`), `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<tên-service>.onrender.com`, `DB_CONNECTION=sqlite`; tùy chọn `GEMINI_API_KEY`.
- Ứng dụng đã `trustProxies` và tự ép `https` khi đứng sau proxy của Render.
- ⚠️ Không gắn persistent disk thì file SQLite nằm trong container: **mỗi lần deploy/restart dữ liệu được tạo lại từ seeder** (phù hợp demo đồ án). Muốn giữ dữ liệu: gắn disk cho thư mục `database/` hoặc dùng MySQL/PostgreSQL bên ngoài.

---

## 👤 TÀI KHOẢN DEMO

Tạo bởi `database/seeders/DatabaseSeeder.php`. **Mật khẩu chung: `password123`**.

| Vai trò | Email | Ghi chú |
|---|---|---|
| Admin | `admin@mcn.com` | Hệ Thống Admin MCN |
| Manager | `manager1@mcn.com` | Trần Minh Hoàng — phụ trách Độ Mixi, Rambo, Nhism, Dev Nguyễn, QNT |
| Manager | `manager2@mcn.com` | Lê Thị Thu Thảo — phụ trách KillerQueen, MisThy |
| Manager | `manager3@mcn.com` | Phạm Quốc Bảo — phụ trách Thầy Giáo Ba, Xemesis, PewPew |
| Streamer | `streamer1@mcn.com` … `streamer10@mcn.com` | 10 talent (streamer1 = Độ Mixi) |
| Client | `client1@mcn.com` | Nguyễn Anh Tuấn (GearVN Agency) |
| Client | `client2@mcn.com` | Lê Thùy Dung (Shopee Brand Mkt) |

> ⚠️ Các tài khoản demo **chỉ dùng cho môi trường local / chấm đồ án**. Nếu triển khai thật, hãy xóa hoặc đổi mật khẩu các tài khoản này.

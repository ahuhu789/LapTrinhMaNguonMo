# SƠ ĐỒ PHÂN RÃ CÔNG VIỆC (WORK BREAKDOWN STRUCTURE - WBS)

## DỰ ÁN: HỆ THỐNG QUẢN LÝ TALENT STREAMERS (MCN PLATFORM)

```
1.0 HỆ THỐNG QUẢN LÝ TALENT STREAMERS (MCN PLATFORM)
├── 1.1 KHỞI TẠO DỰ ÁN & CƠ SỞ DỮ LIỆU
│   ├── 1.1.1 Thiết lập cấu trúc dự án chuẩn Laravel 11 / PSR-4
│   ├── 1.1.2 Thiết kế lược đồ CSDL MySQL 8.0 (Users, Profiles, Bookings, Schedules, Metrics, KPIs)
│   ├── 1.1.3 Xây dựng Artisan Migrations & Raw SQL Schema
│   └── 1.1.4 Chuẩn bị Seeder Script & File CSV mẫu (50+ dòng số liệu)
│
├── 1.2 XÁC THỰC & PHÂN QUYỀN TRUY CẬP (RBAC & SECURITY)
│   ├── 1.2.1 Đăng nhập, đăng ký tài khoản Client, đăng xuất
│   ├── 1.2.2 Xây dựng Middleware CheckRole (RBAC 4 vai trò: Admin, Manager, Streamer, Client)
│   ├── 1.2.3 Cơ chế chống IDOR (Insecure Direct Object Reference)
│   └── 1.2.4 Bảo mật CSRF token và XSS escaping trên toàn bộ giao diện Blade
│
├── 1.3 QUẢN LÝ TALENT & MEDIA KIT (PUBLIC PRESS KIT)
│   ├── 1.3.1 Danh mục Talent Streamers công khai với bộ lọc đa tiêu chí (Category, Rate, Views)
│   ├── 1.3.2 Trang Public Talent Media Kit chuyên nghiệp (Avg Viewers, Peak Viewers, Giờ live)
│   ├── 1.3.3 Hiển thị phân bổ hiệu suất theo nền tảng (YouTube, Twitch, TikTok, Facebook)
│   └── 1.3.4 Lịch trình 7 ngày tới hiển thị khung giờ bận / trống
│
├── 1.4 QUY TRÌNH BOOKING & THUẬT TOÁN KHÓA LỊCH (COLLISION-FREE)
│   ├── 1.4.1 Form tạo yêu cầu booking từ Client kèm kiểm tra va chạm thời gian trực tiếp (AJAX)
│   ├── 1.4.2 Thuật toán Collision Detection: (start < existing_end) AND (end > existing_start)
│   ├── 1.4.3 Màn hình quản lý phê duyệt của Manager
│   └── 1.4.4 Tự động khóa lịch trình vào bảng `schedules` trong Database Transaction khi duyệt
│
├── 1.5 QUẢN LÝ KPI & NHẬP LIỆU METRICS
│   ├── 1.5.1 Giao diện upload tệp CSV số liệu buổi stream cho Manager
│   ├── 1.5.2 Bộ phân tích CSV Parser kiểm tra hợp lệ từng dòng (< 3 giây / 500 dòng)
│   ├── 1.5.3 Tiến trình tự động cộng dồn giờ stream & doanh thu cập nhật bảng `kpis`
│   └── 1.5.4 Vẽ biểu đồ trực quan tiến độ KPI & tăng trưởng view bằng Chart.js
│
├── 1.6 AI TALENT RECOMMENDATION ASSISTANT
│   ├── 1.6.1 Endpoint API nhận tiêu chí ngân sách, thể loại của nhãn hàng
│   ├── 1.6.2 Data Context Injection: Truy vấn Top Talent từ DB đóng gói vào Prompt
│   ├── 1.6.3 Tích hợp REST API Gemini 1.5 Flash / OpenAI
│   ├── 1.6.4 Thuật toán Fallback Rule-Based Scoring thông minh khi không có API key
│   └── 1.6.5 Widget Chatbot nổi ở góc màn hình phía Client
│
└── 1.7 QUẢN TRỊ TOÀN SÀN & ĐÓNG GÓI HỆ THỐNG
    ├── 1.7.1 Dashboard Admin thống kê doanh thu, hoa hồng agency (15%), thành viên
    ├── 1.7.2 Ma trận phân bổ Streamer cho Manager
    ├── 1.7.3 Workspace cá nhân tối giản cho Streamer
    └── 1.7.4 Đóng gói tài liệu báo cáo: WBS, DFD, ERD, Class Diagram, Hướng dẫn cài đặt
```

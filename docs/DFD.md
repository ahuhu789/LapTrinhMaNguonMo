# SƠ ĐỒ LUỒNG DỮ LIỆU (DATA FLOW DIAGRAM - DFD)

## 1. DFD CẤP 0 (CONTEXT DIAGRAM - SƠ ĐỒ NGỮ CẢNH)

Hệ thống Quản lý Talent Streamers (MCN Platform) tương tác với 4 tác nhân chính: **Admin**, **Manager**, **Streamer**, và **Client / Khách vãng lai**.

```mermaid
flowchart TD
    Client["Client / Nhãn Hàng"]
    Streamer["Talent Streamer"]
    Manager["Agency Manager"]
    Admin["Hệ Thống Admin"]
    
    System(("HỆ THỐNG MCN PLATFORM<br/>(Laravel MVC)"))

    Client -- "1. Đăng ký, gửi yêu cầu Booking<br/>2. Truy vấn tư vấn AI, xem Media Kit" --> System
    System -- "Phản hồi xác nhận booking, kết quả tư vấn AI,<br/>chi tiết hồ sơ talent" --> Client

    Streamer -- "1. Cập nhật Media Kit & Bio<br/>2. Đăng ký lịch phát sóng cá nhân" --> System
    System -- "Lịch booking đã duyệt, tiến độ KPI,<br/>thống kê thu nhập" --> Streamer

    Manager -- "1. Phê duyệt / Từ chối booking<br/>2. Thiết lập mục tiêu KPI<br/>3. Upload file CSV metrics" --> System
    System -- "Cảnh báo xung đột lịch trình,<br/>báo cáo tiến độ KPI nhóm" --> Manager

    Admin -- "1. Quản trị phân quyền tài khoản<br/>2. Phân bổ Streamer cho Manager" --> System
    System -- "Báo cáo doanh thu toàn sàn,<br/>hoa hồng agency, số liệu tổng quan" --> Admin
```

---

## 2. DFD CẤP 1 (SƠ ĐỒ PHÂN RÃ CHỨC NĂNG)

Phân rã hệ thống thành 6 tiến trình nghiệp vụ cốt lõi:

```mermaid
flowchart TD
    subgraph Dstores ["Kho Dữ Liệu (Data Stores)"]
        D1[("D1: users")]
        D2[("D2: streamer_profiles")]
        D3[("D3: bookings")]
        D4[("D4: schedules")]
        D5[("D5: stream_metrics")]
        D6[("D6: kpis")]
    end

    Client["Client / Khách hàng"]
    Manager["Manager"]
    Streamer["Streamer"]
    Admin["Admin"]

    %% Process 1: Xác thực & Phân quyền
    P1(("1.0 Xác thực &<br/>Phân quyền RBAC"))
    Client & Manager & Streamer & Admin -->|Thông tin đăng nhập/ký| P1
    P1 <-->|Kiểm tra & Ghi nhận| D1

    %% Process 2: Quản lý Media Kit & Khám phá Talent
    P2(("2.0 Quản lý Media Kit<br/>& Danh mục Talent"))
    Streamer -->|Cập nhật Bio, Thể loại, Giá| P2
    P2 <-->|Đọc & Ghi| D2
    P2 -->|Hiển thị Media Kit| Client

    %% Process 3: Booking & Khóa lịch Collision-Free
    P3(("3.0 Xử lý Booking &<br/>Khóa Lịch Collision-Free"))
    Client -->|Gửi yêu cầu booking| P3
    P3 -->|Kiểm tra trùng lịch| D4
    P3 -->|Lưu đơn pending| D3
    Manager -->|Phê duyệt booking| P3
    P3 -->|Khóa lịch tự động| D4

    %% Process 4: Quản lý Số liệu & Tính KPI
    P4(("4.0 Nhập CSV Metrics &<br/>Cộng Dồn KPI"))
    Manager -->|Upload file CSV metrics| P4
    P4 -->|Ghi số liệu buổi stream| D5
    P4 -->|Cộng dồn giờ & doanh thu| D6
    P4 -->|Hiển thị tiến độ KPI| Streamer & Manager

    %% Process 5: AI Talent Assistant
    P5(("5.0 AI Talent<br/>Recommendation"))
    Client -->|Nhu cầu ngân sách, thể loại| P5
    D2 -.->|Context Injection| P5
    P5 -->|Gợi ý Top Streamer kèm lý do| Client

    %% Process 6: Quản trị Hệ thống
    P6(("6.0 Quản trị Hệ thống &<br/>Phân bổ Quản lý"))
    Admin -->|Phân vai trò, gán streamer| P6
    P6 <-->|Cập nhật quan hệ| D1 & D2
```

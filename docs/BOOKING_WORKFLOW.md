# QUY TRÌNH NGHIỆP VỤ BOOKING (BOOKING WORKFLOW & COLLISION-FREE)

## DỰ ÁN: HỆ THỐNG QUẢN LÝ TALENT STREAMERS (MCN PLATFORM)

Tài liệu này mô tả chi tiết luồng hoạt động từ lúc **Khách hàng (Client)** vào đặt lịch, **Quản lý (Manager)** tiếp nhận xử lý và **Streamer (Talent)** nhận thông báo cập nhật lịch phát sóng.

---

## 1. SƠ ĐỒ LUỒNG HOẠT ĐỘNG PHÂN LÀN (SWIMLANE ACTIVITY DIAGRAM)

```mermaid
sequenceDiagram
    autonumber
    actor Client as 👤 Khách Hàng (Client)
    participant System as 🖥️ Hệ Thống (MCN Platform)
    participant DB as 🗄️ CSDL (MySQL/SQLite)
    actor Manager as 👔 Quản Lý (Manager)
    actor Streamer as 🎙️ Streamer (Talent)

    %% Giai đoạn 1: Khách hàng tìm kiếm & Gửi yêu cầu Booking
    Note over Client, System: GIAI ĐOẠN 1: KHÁCH HÀNG TẠO YÊU CẦU BOOKING
    Client->>System: 1. Xem Talent Media Kit & chọn khung giờ booking
    System->>DB: 2. API /api/check-collision kiểm tra lịch bận
    DB-->>System: Trả về kết quả (trống lịch / trùng giờ)
    alt Bị trùng lịch
        System-->>Client: Cảnh báo đỏ: "Streamer đã có lịch vào khung giờ này"
    else Khung giờ khả dụng
        System-->>Client: Hiển thị trạng thái hợp lệ (Màu xanh)
    end

    Client->>System: 3. Nhập kịch bản, ngân sách & Bấm "Gửi Yêu Cầu"
    System->>DB: 4. Chạy lại Collision Check & Lưu Booking (status = 'pending')
    DB-->>System: Ghi nhận đơn thành công
    System-->>Client: Thông báo: "Yêu cầu đã được gửi đến Manager phụ trách"

    %% Giai đoạn 2: Quản lý tiếp nhận & Đưa ra quyết định
    Note over System, Manager: GIAI ĐOẠN 2: MANAGER XỬ LÝ & PHÊ DUYỆT ĐƠN
    System->>Manager: 5. Hiển thị thông báo đơn mới trên Dashboard / Quản lý Booking
    Manager->>System: 6. Mở chi tiết đơn Booking (Xem kịch bản, ngân sách, hợp đồng)
    System->>DB: Truy vấn thông tin đơn & kiểm tra lại va chạm thời gian thực

    alt Manager Từ Chối (Reject)
        Manager->>System: Nhập lý do từ chối & Bấm "Từ Chối"
        System->>DB: Cập nhật status = 'cancelled' kèm lý do
        System-->>Client: Gửi thông báo từ chối kèm lý do phản hồi
    else Manager Phê Duyệt (Approve)
        Manager->>System: 7. Bấm nút "Phê Duyệt & Khóa Lịch"
        
        %% Giai đoạn 3: Khóa lịch & Streamer nhận thông báo
        Note over System, Streamer: GIAI ĐOẠN 3: KHÓA LỊCH & STREAMER NHẬN TIN
        rect rgb(240, 248, 255)
            System->>DB: 8. Kích hoạt DB::transaction
            System->>DB: 9. Cập nhật status = 'approved', manager_id = current_user
            System->>DB: 10. Chèn bản ghi mới vào bảng 'schedules' (event_type = 'booking')
            DB-->>System: Commit Transaction thành công
        end

        System-->>Manager: Báo duyệt thành công, lịch đã được khóa
        System-->>Client: Thông báo: "Hợp đồng đã được phê duyệt thành công!"
        
        System->>Streamer: 11. Bắn thông báo đơn mới lên Workspace Dashboard
        Streamer->>System: 12. Vào "Lịch Stream 7 ngày tới" & "Upcoming Bookings"
        System-->>Streamer: Hiển thị sự kiện booking đã khóa, thông tin nhãn hàng & kịch bản
    end
```

---

## 2. LƯU ĐỒ QUY TRÌNH RA QUYẾT ĐỊNH (FLOWCHART LOGIC)

```mermaid
flowchart TD
    Start([Khách hàng duyệt danh mục Talent]) --> Step1[Chọn Streamer & Xem Talent Media Kit]
    Step1 --> Step2[Mở Form Booking: Điền thời gian bắt đầu & kết thúc]
    
    Step2 --> AjaxCheck{API kiểm tra va chạm<br>/api/check-collision}
    AjaxCheck -- Trùng lịch --> ConflictMsg[Báo lỗi: Khung giờ đã bận. Yêu cầu chọn giờ khác]
    ConflictMsg --> Step2
    AjaxCheck -- Trống lịch --> Step3[Nhập kịch bản livestream, yêu cầu công việc & ngân sách]

    Step3 --> Submit[Khách hàng bấm Gửi Booking]
    Submit --> ServerCheck{Server kiểm tra Collision<br>lần cuối trước khi lưu}
    ServerCheck -- Xung đột phát sinh --> ConflictMsg
    ServerCheck -- Hợp lệ --> CreatePending[Tạo bản ghi bookings với status = 'pending']

    CreatePending --> NotifyManager[Đơn xuất hiện trong danh sách duyệt của Manager phụ trách]
    NotifyManager --> ManagerReview[Manager mở xem chi tiết đơn booking]

    ManagerReview --> Decision{Manager đưa ra<br>quyết định?}
    
    %% Nhánh từ chối
    Decision -- Từ chối --> InputReason[Manager nhập lý do từ chối]
    InputReason --> SaveReject[Cập nhật status = 'cancelled']
    SaveReject --> NotifyClientReject[Khách hàng nhận thông báo từ chối kèm lý do]
    NotifyClientReject --> EndReject([Kết thúc])

    %% Nhánh phê duyệt
    Decision -- Đồng ý --> Transaction[Kích hoạt Database Transaction an toàn]
    Transaction --> UpdateStatus[1. Cập nhật booking status = 'approved']
    UpdateStatus --> LockSchedule[2. Tạo bản ghi schedules loại 'booking' để khóa giờ]
    LockSchedule --> CommitDB[Commit Transaction]

    CommitDB --> SuccessClient[Khách hàng nhận thông báo đơn được duyệt]
    CommitDB --> StreamerWorkspace[Streamer nhận thông báo trên Streamer Workspace]
    StreamerWorkspace --> StreamerCalendar[Lịch tự động hiển thị trong Lịch stream 7 ngày tới]
    StreamerCalendar --> StreamPreparation[Streamer chuẩn bị kịch bản & sản phẩm để phát sóng]
    StreamPreparation --> EndSuccess([Hoàn tất quy trình đặt lịch])
```

---

## 3. CƠ CHẾ BẢO MẬT & TOÀN VẸN DỮ LIỆU ĐÃ CÀI ĐẶT TRONG CODE

1. **Kiểm tra va chạm 2 lớp (Two-tier Collision Detection):**
   - **Tầng Client (AJAX):** Kiểm tra tức thì khi đổi `datetime` để nâng cao trải nghiệm người dùng (UX).
   - **Tầng Server (BookingController + BookingCollisionService):** Ngăn chặn hoàn toàn việc bypass form hoặc gửi request trực tiếp bằng Postman/Curl.
2. **Database Transaction:**
   - Quá trình chuyển trạng thái booking sang `approved` và việc tạo lịch trình mới trong bảng `schedules` được bọc trong `DB::transaction()`. Nếu có lỗi xảy ra ở bất kỳ bước nào, toàn bộ sẽ tự động Rollback, bảo đảm không bao giờ xảy ra tình trạng "đơn đã duyệt nhưng lịch chưa khóa".
3. **Phân quyền chặt chẽ (RBAC + IDOR Protection):**
   - Chỉ Manager phụ trách trực tiếp Streamer đó (hoặc Admin tối cao) mới có quyền bấm **Phê duyệt** hoặc **Từ chối**.
   - Streamer chỉ xem được thông tin booking của chính bản thân mình.

-- =============================================================================
-- HỆ THỐNG QUẢN LÝ TALENT STREAMERS (MCN PLATFORM)
-- Dữ liệu mẫu ban đầu (Seeder Script)
-- Mật khẩu mặc định cho tất cả tài khoản: password123
-- Hash Bcrypt: $2y$10$j81Zrq5eK94/4zH.C8tJ0.1oI7Wb7zJk6zX5tF5qM0BqS7vE5Zg32
-- =============================================================================

USE `mcn_platform`;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `kpis`;
TRUNCATE TABLE `stream_metrics`;
TRUNCATE TABLE `schedules`;
TRUNCATE TABLE `bookings`;
TRUNCATE TABLE `streamer_profiles`;
TRUNCATE TABLE `users`;

-- 1. Thêm Users: 1 Admin, 3 Managers, 10 Streamers, 2 Clients
-- Lưu ý: Mật khẩu là password123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
-- Admin
(1, 'Hệ Thống Admin MCN', 'admin@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'admin', NOW(), NOW()),

-- 3 Managers
(2, 'Trần Minh Hoàng', 'manager1@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'manager', NOW(), NOW()),
(3, 'Lê Thị Thu Thảo', 'manager2@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'manager', NOW(), NOW()),
(4, 'Phạm Quốc Bảo', 'manager3@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'manager', NOW(), NOW()),

-- 10 Streamers
(5, 'Nguyễn Văn Đạt (Đạt Pro)', 'streamer1@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),
(6, 'Vũ Hải Yến (Yến Cinn)', 'streamer2@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),
(7, 'Đỗ Khắc Nam (Nam Gamer)', 'streamer3@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),
(8, 'Phạm Quỳnh Anh (Anh Thỏ)', 'streamer4@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),
(9, 'Lê Tuấn Hưng (Hưng Tech)', 'streamer5@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),
(10, 'Hoàng Mỹ Duyên (Duyên Duyên)', 'streamer6@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),
(11, 'Bùi Quang Sáng (Sáng MOBA)', 'streamer7@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),
(12, 'Ngô Bảo Trâm (Trâm Chill)', 'streamer8@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),
(13, 'Trần Đức Bo (Bo Entertainment)', 'streamer9@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),
(14, 'Đinh Gia Huy (Huy Esports)', 'streamer10@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'streamer', NOW(), NOW()),

-- 2 Clients
(15, 'Nguyễn Anh Tuấn (GearVN Agency)', 'client1@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'client', NOW(), NOW()),
(16, 'Lê Thùy Dung (Shopee Brand Mkt)', 'client2@mcn.com', '$2y$10$WqB87fK.3aH9.p/bE0s85.0jK4c6QjI0/G0eYh0sH8oD2a5/9C/7S', 'client', NOW(), NOW());

-- 2. Thêm Hồ sơ Talent Streamers (streamer_profiles)
INSERT INTO `streamer_profiles` (`id`, `user_id`, `manager_id`, `stage_name`, `category`, `bio`, `rate_per_hour`, `avatar_url`, `banner_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 2, 'Đạt Pro FPS', 'Gaming', 'Streamer chuyên game bắn súng CS2, Valorant, cựu tuyển thủ quốc gia với phong cách vui tính, kỹ năng cao.', 1500000.00, 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=400', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1200', 'active', NOW(), NOW()),
(2, 6, 2, 'Yến Cinn', 'Just Chatting', 'Streamer tâm sự đêm khuya, đàn hát acoustic, tương tác gắn kết người xem cực tốt với cộng đồng fan trung thành.', 800000.00, 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=1200', 'active', NOW(), NOW()),
(3, 7, 2, 'Nam Gamer', 'Gaming', 'Top 1 Thách Đấu Liên Minh Huyền Thoại Việt Nam, livestream phân tích chiến thuật và highlight meta game.', 2000000.00, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400', 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1200', 'active', NOW(), NOW()),
(4, 8, 3, 'Anh Thỏ Lifestyle', 'Lifestyle', 'Kênh chia sẻ phong cách sống, makeup, du lịch và ẩm thực lành mạnh cho giới trẻ Gen Z.', 1200000.00, 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400', 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200', 'active', NOW(), NOW()),
(5, 9, 3, 'Hưng Tech Review', 'Review', 'Chuyên gia mở hộp, đánh giá smartphone, PC gaming, tai nghe và đồ gia dụng công nghệ cao.', 1800000.00, 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200', 'active', NOW(), NOW()),
(6, 10, 3, 'Duyên Duyên ASMR', 'Just Chatting', 'Nội dung ASMR thư giãn, đọc truyện đêm khuya, voice ngọt ngào giúp người xem giảm stress sau giờ làm việc.', 600000.00, 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400', 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=1200', 'active', NOW(), NOW()),
(7, 11, 4, 'Sáng MOBA', 'Gaming', 'Cao thủ Liên Quân Mobile và Tốc Chiến, sáng tạo các giáo án dị hài hước thu hút hàng chục nghìn view.', 1000000.00, 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=400', 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=1200', 'active', NOW(), NOW()),
(8, 12, 4, 'Trâm Chill Music', 'Review', 'Review quán cafe chill, unboxing sách và sản phẩm decor phòng làm việc cho dân văn phòng.', 700000.00, 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400', 'https://images.unsplash.com/photo-1445384763658-0400939829cd?w=1200', 'active', NOW(), NOW()),
(9, 13, 4, 'Bo Entertainment', 'Just Chatting', 'Show giải trí, hài hước, phản ứng các video viral và tổ chức minigame tặng quà tương tác với khán giả.', 2500000.00, 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=400', 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200', 'active', NOW(), NOW()),
(10, 14, 2, 'Huy Esports', 'Gaming', 'Bình luận viên giải đấu Esports Dota 2 và PUBG, kiến thức chuyên sâu, giọng đọc truyền cảm.', 1600000.00, 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=400', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200', 'active', NOW(), NOW());

-- 3. Thêm Bookings mẫu
INSERT INTO `bookings` (`id`, `client_id`, `streamer_id`, `manager_id`, `start_time`, `end_time`, `job_description`, `budget`, `commission_rate`, `status`, `reject_reason`, `created_at`, `updated_at`) VALUES
(1, 15, 1, 2, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 3 HOUR, 'Livestream trải nghiệm chuột gaming Logitech G Pro X Superlight trong giải đấu nội bộ.', 4500000.00, 15.00, 'approved', NULL, NOW(), NOW()),
(2, 16, 2, 2, DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 2 HOUR, 'Livestream ca hát kết hợp phát mã giảm giá Shopee 10.10 cho viewer.', 2000000.00, 15.00, 'approved', NULL, NOW(), NOW()),
(3, 15, 3, 2, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 4 HOUR, 'Quảng bá màn hình Gaming Asus ROG Swift 360Hz trong trận đấu showmatch.', 8000000.00, 15.00, 'pending', NULL, NOW(), NOW()),
(4, 16, 4, 3, DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 2 HOUR, 'Review bộ sản phẩm chăm sóc da mùa thu từ nhãn hàng Innisfree.', 3000000.00, 15.00, 'pending', NULL, NOW(), NOW()),
(5, 15, 5, 3, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY) + INTERVAL 3 HOUR, 'Livestream unboxing bàn phím cơ không dây Custom Keychron Q1 Pro.', 5400000.00, 15.00, 'completed', NULL, NOW(), NOW()),
(6, 16, 1, 2, DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 2 HOUR, 'Booking quảng cáo game di động mới ra mắt.', 3000000.00, 15.00, 'rejected', 'Streamer đã có lịch thi đấu chuyên nghiệp cùng ngày.', NOW(), NOW());

-- 4. Thêm Schedules mẫu (gồm lịch Stream và lịch Booking đã duyệt)
INSERT INTO `schedules` (`id`, `streamer_id`, `event_type`, `reference_id`, `start_time`, `end_time`, `title`, `created_at`, `updated_at`) VALUES
-- Lịch booking số 1 (Đạt Pro)
(1, 1, 'booking', 1, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 3 HOUR, 'Booking: Hợp tác Logitech G Pro X (GearVN)', NOW(), NOW()),
-- Lịch stream định kỳ Đạt Pro
(2, 1, 'stream', NULL, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 19 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 23 HOUR, 'Live CS2 Leo Rank Global Elite', NOW(), NOW()),

-- Lịch booking số 2 (Yến Cinn)
(3, 2, 'booking', 2, DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 2 HOUR, 'Booking: Shopee Mega Sale 10.10', NOW(), NOW()),
-- Lịch cá nhân Yến Cinn
(4, 2, 'personal', NULL, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 14 HOUR, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 18 HOUR, 'Luyện thanh & tập nhạc mới', NOW(), NOW()),

-- Lịch stream Nam Gamer
(5, 3, 'stream', NULL, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 18 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 22 HOUR, 'Live rank Thách Đấu LMHT Hàn Quốc', NOW(), NOW());

-- 5. Thêm Stream Metrics mẫu
INSERT INTO `stream_metrics` (`id`, `streamer_id`, `platform`, `stream_date`, `duration_hours`, `avg_viewers`, `peak_viewers`, `followers_gained`, `source_type`, `created_at`, `updated_at`) VALUES
(1, 1, 'youtube', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 4.50, 12500, 18200, 450, 'csv_import', NOW(), NOW()),
(2, 1, 'youtube', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 3.50, 11000, 16000, 320, 'csv_import', NOW(), NOW()),
(3, 1, 'twitch', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 5.00, 9500, 14200, 280, 'manual_entry', NOW(), NOW()),
(4, 2, 'tiktok', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 2.00, 18500, 26000, 1500, 'csv_import', NOW(), NOW()),
(5, 2, 'youtube', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 3.00, 8200, 12000, 310, 'csv_import', NOW(), NOW()),
(6, 3, 'youtube', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 6.00, 24000, 38500, 1200, 'csv_import', NOW(), NOW()),
(7, 3, 'facebook', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 5.50, 21000, 32000, 980, 'csv_import', NOW(), NOW()),
(8, 4, 'tiktok', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 2.50, 14200, 21000, 850, 'csv_import', NOW(), NOW()),
(9, 5, 'youtube', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 3.00, 16500, 25000, 620, 'csv_import', NOW(), NOW()),
(10, 6, 'youtube', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 2.50, 7500, 11000, 190, 'csv_import', NOW(), NOW()),
(11, 7, 'facebook', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 4.00, 11500, 17000, 410, 'csv_import', NOW(), NOW()),
(12, 8, 'tiktok', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 2.00, 6800, 9500, 220, 'csv_import', NOW(), NOW()),
(13, 9, 'youtube', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 4.00, 28000, 45000, 1800, 'csv_import', NOW(), NOW()),
(14, 10, 'twitch', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 5.00, 13500, 22000, 520, 'csv_import', NOW(), NOW());

-- 6. Thêm KPIs mẫu cho tháng hiện tại (và tháng trước)
INSERT INTO `kpis` (`id`, `streamer_id`, `month_year`, `target_hours`, `target_revenue`, `target_avg_viewers`, `achieved_hours`, `achieved_revenue`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, DATE_FORMAT(CURDATE(), '%Y-%m'), 70.00, 25000000.00, 12000, 52.50, 18500000.00, 'in_progress', NOW(), NOW()),
(2, 2, DATE_FORMAT(CURDATE(), '%Y-%m'), 50.00, 15000000.00, 9000, 38.00, 12000000.00, 'in_progress', NOW(), NOW()),
(3, 3, DATE_FORMAT(CURDATE(), '%Y-%m'), 80.00, 35000000.00, 20000, 65.00, 28000000.00, 'in_progress', NOW(), NOW()),
(4, 4, DATE_FORMAT(CURDATE(), '%Y-%m'), 45.00, 18000000.00, 10000, 30.00, 11500000.00, 'in_progress', NOW(), NOW()),
(5, 5, DATE_FORMAT(CURDATE(), '%Y-%m'), 60.00, 22000000.00, 15000, 48.00, 19000000.00, 'in_progress', NOW(), NOW()),
(6, 6, DATE_FORMAT(CURDATE(), '%Y-%m'), 40.00, 10000000.00, 7000, 42.00, 10500000.00, 'achieved', NOW(), NOW()),
(7, 7, DATE_FORMAT(CURDATE(), '%Y-%m'), 60.00, 20000000.00, 12000, 45.00, 14000000.00, 'in_progress', NOW(), NOW()),
(8, 8, DATE_FORMAT(CURDATE(), '%Y-%m'), 40.00, 12000000.00, 6000, 28.00, 7500000.00, 'in_progress', NOW(), NOW()),
(9, 9, DATE_FORMAT(CURDATE(), '%Y-%m'), 75.00, 40000000.00, 25000, 58.00, 32000000.00, 'in_progress', NOW(), NOW()),
(10, 10, DATE_FORMAT(CURDATE(), '%Y-%m'), 65.00, 24000000.00, 13000, 50.00, 17500000.00, 'in_progress', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

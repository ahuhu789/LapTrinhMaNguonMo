<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with famous Vietnamese streamers.
     */
    public function run(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DB::table('kpis')->delete();
            DB::table('stream_metrics')->delete();
            DB::table('schedules')->delete();
            DB::table('bookings')->delete();
            DB::table('streamer_profiles')->delete();
            DB::table('users')->delete();
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('kpis')->truncate();
            DB::table('stream_metrics')->truncate();
            DB::table('schedules')->truncate();
            DB::table('bookings')->truncate();
            DB::table('streamer_profiles')->truncate();
            DB::table('users')->truncate();
        }

        $defaultPassword = Hash::make('password123');

        // 1. Users
        $users = [
            ['id' => 1, 'name' => 'Hệ Thống Admin MCN', 'email' => 'admin@mcn.com', 'password' => $defaultPassword, 'role' => 'admin'],
            ['id' => 2, 'name' => 'Trần Minh Hoàng (Manager Refund)', 'email' => 'manager1@mcn.com', 'password' => $defaultPassword, 'role' => 'manager'],
            ['id' => 3, 'name' => 'Lê Thị Thu Thảo (Manager Lifestyle)', 'email' => 'manager2@mcn.com', 'password' => $defaultPassword, 'role' => 'manager'],
            ['id' => 4, 'name' => 'Phạm Quốc Bảo (Manager Esports)', 'email' => 'manager3@mcn.com', 'password' => $defaultPassword, 'role' => 'manager'],
            ['id' => 5, 'name' => 'Phùng Thanh Độ (Độ Mixi)', 'email' => 'streamer1@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 6, 'name' => 'Lâm Đình Khoa (Rambo)', 'email' => 'streamer2@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 7, 'name' => 'Vũ Ngọc Kim Chi (KillerQueen)', 'email' => 'streamer3@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 8, 'name' => 'Trần Thái Linh (Nhism)', 'email' => 'streamer4@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 9, 'name' => 'Nguyễn Minh Hoàng (Dev Nguyễn)', 'email' => 'streamer5@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 10, 'name' => 'Trần Mạnh Quang (QNT)', 'email' => 'streamer6@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 11, 'name' => 'Phan Tấn Trung (Thầy Giáo Ba)', 'email' => 'streamer7@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 12, 'name' => 'Lê Thy Ngọc (MisThy)', 'email' => 'streamer8@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 13, 'name' => 'Nghiêm Anh Hiếu (Xemesis)', 'email' => 'streamer9@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 14, 'name' => 'Hoàng Văn Khoa (PewPew)', 'email' => 'streamer10@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 15, 'name' => 'Nguyễn Anh Tuấn (GearVN Agency)', 'email' => 'client1@mcn.com', 'password' => $defaultPassword, 'role' => 'client'],
            ['id' => 16, 'name' => 'Lê Thùy Dung (Shopee Brand Mkt)', 'email' => 'client2@mcn.com', 'password' => $defaultPassword, 'role' => 'client'],
        ];

        foreach ($users as $u) {
            DB::table('users')->insert([
                'id' => $u['id'],
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => $u['password'],
                'role' => $u['role'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Streamer Profiles (Các streamer nổi tiếng hàng đầu Việt Nam)
        $profiles = [
            [
                'id' => 1, 'user_id' => 5, 'manager_id' => 2, 'stage_name' => 'Độ Mixi (MixiGaming)', 'category' => 'Gaming',
                'bio' => 'Tộc trưởng Bộ Tộc MixiGaming, Founder Refund Gaming. Streamer hàng đầu Việt Nam chuyên CS2, PUBG, GTA V và talkshow đêm muộn với lượng tương tác kỷ lục.',
                'rate_per_hour' => 5000000.00, 'avatar_url' => '/images/streamers/domixi.jpg',
                'banner_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1200', 'status' => 'active',
            ],
            [
                'id' => 2, 'user_id' => 6, 'manager_id' => 2, 'stage_name' => 'Rambo (Khoa Mèo)', 'category' => 'Gaming',
                'bio' => 'Thành viên trụ cột Refund Gaming, cựu tuyển thủ PUBG vô địch quốc gia. Phong cách tấu hài tự nhiên, duyên dáng, chuyên CS2, PUBG và GTA V Roleplay.',
                'rate_per_hour' => 2500000.00, 'avatar_url' => '/images/streamers/rambo.jpg',
                'banner_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1200', 'status' => 'active',
            ],
            [
                'id' => 3, 'user_id' => 7, 'manager_id' => 3, 'stage_name' => 'KillerQueen (Kim Chi)', 'category' => 'Just Chatting',
                'bio' => 'Nữ streamer kiêm người mẫu ảnh quyến rũ, cá tính. Livestream giao lưu tương tác cùng viewer, chơi game giải trí và review thời trang, mỹ phẩm.',
                'rate_per_hour' => 1800000.00, 'avatar_url' => '/images/streamers/killerqueen.jpg',
                'banner_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=1200', 'status' => 'active',
            ],
            [
                'id' => 4, 'user_id' => 8, 'manager_id' => 2, 'stage_name' => 'Nhism (Thái Linh)', 'category' => 'Gaming',
                'bio' => 'Xạ thủ thiện xạ Refund Gaming, cựu pro player PUBG. Kỹ năng ngắm bắn đỉnh cao trong CS2, Valorant kết hợp gương mặt điển trai và tương tác dí dỏm.',
                'rate_per_hour' => 2200000.00, 'avatar_url' => '/images/streamers/nhism.jpg',
                'banner_url' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=1200', 'status' => 'active',
            ],
            [
                'id' => 5, 'user_id' => 9, 'manager_id' => 2, 'stage_name' => 'Dev Nguyễn', 'category' => 'Gaming',
                'bio' => 'Streamer CS2 đình đám, cựu vận động viên Dota 2. Biệt danh "Chúa tể Eco", phong cách stream chân chất, hài hước, sở hữu cộng đồng fan trung thành.',
                'rate_per_hour' => 2000000.00, 'avatar_url' => '/images/streamers/devnguyen.jpg',
                'banner_url' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200', 'status' => 'active',
            ],
            [
                'id' => 6, 'user_id' => 10, 'manager_id' => 2, 'stage_name' => 'QNT (Quang QNT)', 'category' => 'Just Chatting',
                'bio' => 'Rapper, nhạc sĩ, designer và streamer Refund Gaming. Livestream sản xuất âm nhạc, đàn hát acoustic, giao lưu nghệ thuật và chia sẻ phong cách sống.',
                'rate_per_hour' => 1900000.00, 'avatar_url' => '/images/streamers/qnt.jpg',
                'banner_url' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200', 'status' => 'active',
            ],
            [
                'id' => 7, 'user_id' => 11, 'manager_id' => 4, 'stage_name' => 'Thầy Giáo Ba (Ba Gà)', 'category' => 'Gaming',
                'bio' => 'Hiệu trưởng trường học SBTC, cây hài số 1 làng LMHT Việt Nam. Chuyên sáng tạo giáo án dị Susan 0175, phân tích giải đấu và tấu hài cười ra nước mắt.',
                'rate_per_hour' => 2600000.00, 'avatar_url' => '/images/streamers/thayba.jpg',
                'banner_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200', 'status' => 'active',
            ],
            [
                'id' => 8, 'user_id' => 12, 'manager_id' => 3, 'stage_name' => 'MisThy', 'category' => 'Lifestyle',
                'bio' => 'Nữ streamer hàng đầu Việt Nam với hơn 6 triệu subscribers. Đa tài trong các lĩnh vực ẩm thực, du lịch, vlog trải nghiệm, talkshow và show truyền hình.',
                'rate_per_hour' => 3500000.00, 'avatar_url' => '/images/streamers/misthy.jpg',
                'banner_url' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200', 'status' => 'active',
            ],
            [
                'id' => 9, 'user_id' => 13, 'manager_id' => 4, 'stage_name' => 'Xemesis (Hiếu Xem)', 'category' => 'Review',
                'bio' => 'Streamer giàu nhất Việt Nam, thành viên Tứ Hoàng. Chuyên review siêu xe, nhà đẹp, ẩm thực cao cấp, đồ chơi công nghệ hàng hiệu và cuộc sống thượng lưu.',
                'rate_per_hour' => 4500000.00, 'avatar_url' => '/images/streamers/xemesis.png',
                'banner_url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1200', 'status' => 'active',
            ],
            [
                'id' => 10, 'user_id' => 14, 'manager_id' => 4, 'stage_name' => 'PewPew', 'category' => 'Just Chatting',
                'bio' => 'Huyền thoại Talkshow, người tiên phong phong trào livestream Việt Nam. Chuyên tư vấn tình cảm, chia sẻ trải nghiệm khởi nghiệp, kinh doanh bánh mì và nông sản.',
                'rate_per_hour' => 3200000.00, 'avatar_url' => '/images/streamers/pewpew.png',
                'banner_url' => 'https://images.unsplash.com/photo-1445384763658-0400939829cd?w=1200', 'status' => 'active',
            ],
        ];

        foreach ($profiles as $p) {
            DB::table('streamer_profiles')->insert(array_merge($p, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 3. Bookings
        DB::table('bookings')->insert([
            [
                'id' => 1, 'client_id' => 15, 'streamer_id' => 1, 'manager_id' => 2,
                'start_time' => Carbon::now()->addDays(2)->setTime(20, 0), 'end_time' => Carbon::now()->addDays(2)->setTime(23, 0),
                'job_description' => 'Livestream trải nghiệm trọn bộ PC Gaming Asus ROG kết hợp chuột Logitech G Pro X Superlight cùng Bộ Tộc MixiGaming.',
                'budget' => 15000000.00, 'commission_rate' => 15.00, 'status' => 'approved', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 2, 'client_id' => 16, 'streamer_id' => 2, 'manager_id' => 2,
                'start_time' => Carbon::now()->addDays(3)->setTime(19, 0), 'end_time' => Carbon::now()->addDays(3)->setTime(22, 0),
                'job_description' => 'Livestream PUBG giải trí kết hợp phát voucher Shopee Mega Sale 11.11 trực tiếp cho viewer.',
                'budget' => 7500000.00, 'commission_rate' => 15.00, 'status' => 'approved', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 3, 'client_id' => 15, 'streamer_id' => 7, 'manager_id' => 4,
                'start_time' => Carbon::now()->addDay()->setTime(18, 0), 'end_time' => Carbon::now()->addDay()->setTime(21, 0),
                'job_description' => 'Showmatch Liên Minh Huyền Thoại cùng học viên SBTC quảng bá màn hình Samsung Odyssey OLED G9.',
                'budget' => 7800000.00, 'commission_rate' => 15.00, 'status' => 'pending', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 4, 'client_id' => 16, 'streamer_id' => 3, 'manager_id' => 3,
                'start_time' => Carbon::now()->addDays(4)->setTime(20, 0), 'end_time' => Carbon::now()->addDays(4)->setTime(22, 0),
                'job_description' => 'Talkshow giao lưu và review bộ sưu tập mỹ phẩm dưỡng da mùa đông Innisfree trên sóng stream.',
                'budget' => 3600000.00, 'commission_rate' => 15.00, 'status' => 'pending', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 5, 'client_id' => 15, 'streamer_id' => 9, 'manager_id' => 4,
                'start_time' => Carbon::now()->subDays(3)->setTime(14, 0), 'end_time' => Carbon::now()->subDays(3)->setTime(17, 0),
                'job_description' => 'Review góc làm việc & dàn PC Gaming custom trị giá 200 triệu từ GearVN tại biệt thự Xemesis.',
                'budget' => 13500000.00, 'commission_rate' => 15.00, 'status' => 'completed', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // 4. Schedules
        DB::table('schedules')->insert([
            [
                'id' => 1, 'streamer_id' => 1, 'event_type' => 'booking', 'reference_id' => 1,
                'start_time' => Carbon::now()->addDays(2)->setTime(20, 0), 'end_time' => Carbon::now()->addDays(2)->setTime(23, 0),
                'title' => 'Booking: Trải nghiệm ASUS ROG x GearVN (Độ Mixi)',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 2, 'streamer_id' => 1, 'event_type' => 'stream', 'reference_id' => null,
                'start_time' => Carbon::now()->addDay()->setTime(22, 0), 'end_time' => Carbon::now()->addDay()->addHours(4)->setTime(2, 0),
                'title' => 'Live CS2 & Talkshow đêm muộn cùng Bộ Tộc',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 3, 'streamer_id' => 2, 'event_type' => 'booking', 'reference_id' => 2,
                'start_time' => Carbon::now()->addDays(3)->setTime(19, 0), 'end_time' => Carbon::now()->addDays(3)->setTime(22, 0),
                'title' => 'Booking: Shopee Mega Sale 11.11 (Rambo)',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 4, 'streamer_id' => 7, 'event_type' => 'stream', 'reference_id' => null,
                'start_time' => Carbon::now()->addDays(2)->setTime(18, 0), 'end_time' => Carbon::now()->addDays(2)->setTime(22, 0),
                'title' => 'Live Rank Thách Đấu & Giáo án Susan SBTC',
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // 5. Stream Metrics (Số liệu view chân thực của các streamer hàng đầu)
        DB::table('stream_metrics')->insert([
            // Độ Mixi
            ['streamer_id' => 1, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 4.5, 'avg_viewers' => 125000, 'peak_viewers' => 245000, 'followers_gained' => 8500, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 1, 'platform' => 'youtube', 'stream_date' => Carbon::now()->subDays(2), 'duration_hours' => 5.0, 'avg_viewers' => 118000, 'peak_viewers' => 210000, 'followers_gained' => 6200, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            
            // Rambo
            ['streamer_id' => 2, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 3.5, 'avg_viewers' => 48000, 'peak_viewers' => 86000, 'followers_gained' => 2500, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            
            // KillerQueen
            ['streamer_id' => 3, 'platform' => 'tiktok', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 2.5, 'avg_viewers' => 22000, 'peak_viewers' => 43000, 'followers_gained' => 3200, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            
            // Nhism
            ['streamer_id' => 4, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 4.0, 'avg_viewers' => 36000, 'peak_viewers' => 69000, 'followers_gained' => 1800, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            
            // Dev Nguyễn
            ['streamer_id' => 5, 'platform' => 'youtube', 'stream_date' => Carbon::now()->subDays(2), 'duration_hours' => 4.0, 'avg_viewers' => 31000, 'peak_viewers' => 55000, 'followers_gained' => 1400, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            
            // QNT
            ['streamer_id' => 6, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 3.0, 'avg_viewers' => 24000, 'peak_viewers' => 45000, 'followers_gained' => 1100, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            
            // Thầy Giáo Ba
            ['streamer_id' => 7, 'platform' => 'youtube', 'stream_date' => Carbon::now()->subDays(2), 'duration_hours' => 4.5, 'avg_viewers' => 42000, 'peak_viewers' => 82000, 'followers_gained' => 2100, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            
            // MisThy
            ['streamer_id' => 8, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 2.5, 'avg_viewers' => 38000, 'peak_viewers' => 76000, 'followers_gained' => 2900, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            
            // Xemesis
            ['streamer_id' => 9, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 3.0, 'avg_viewers' => 55000, 'peak_viewers' => 115000, 'followers_gained' => 4500, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            
            // PewPew
            ['streamer_id' => 10, 'platform' => 'tiktok', 'stream_date' => Carbon::now()->subDays(2), 'duration_hours' => 3.5, 'avg_viewers' => 45000, 'peak_viewers' => 92000, 'followers_gained' => 3800, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 6. KPIs
        $currentMonth = Carbon::now()->format('Y-m');
        $kpiData = [
            ['streamer_id' => 1, 'target_hours' => 90, 'target_revenue' => 100000000, 'target_avg_viewers' => 120000, 'achieved_hours' => 72.5, 'achieved_revenue' => 75000000, 'status' => 'in_progress'],
            ['streamer_id' => 2, 'target_hours' => 70, 'target_revenue' => 45000000, 'target_avg_viewers' => 45000, 'achieved_hours' => 58.0, 'achieved_revenue' => 37500000, 'status' => 'in_progress'],
            ['streamer_id' => 3, 'target_hours' => 50, 'target_revenue' => 25000000, 'target_avg_viewers' => 20000, 'achieved_hours' => 42.0, 'achieved_revenue' => 21000000, 'status' => 'in_progress'],
            ['streamer_id' => 4, 'target_hours' => 65, 'target_revenue' => 35000000, 'target_avg_viewers' => 35000, 'achieved_hours' => 50.0, 'achieved_revenue' => 28000000, 'status' => 'in_progress'],
            ['streamer_id' => 5, 'target_hours' => 60, 'target_revenue' => 30000000, 'target_avg_viewers' => 30000, 'achieved_hours' => 48.0, 'achieved_revenue' => 24000000, 'status' => 'in_progress'],
            ['streamer_id' => 6, 'target_hours' => 50, 'target_revenue' => 25000000, 'target_avg_viewers' => 22000, 'achieved_hours' => 38.0, 'achieved_revenue' => 19000000, 'status' => 'in_progress'],
            ['streamer_id' => 7, 'target_hours' => 75, 'target_revenue' => 50000000, 'target_avg_viewers' => 40000, 'achieved_hours' => 62.0, 'achieved_revenue' => 41600000, 'status' => 'in_progress'],
            ['streamer_id' => 8, 'target_hours' => 60, 'target_revenue' => 40000000, 'target_avg_viewers' => 35000, 'achieved_hours' => 45.0, 'achieved_revenue' => 31500000, 'status' => 'in_progress'],
            ['streamer_id' => 9, 'target_hours' => 50, 'target_revenue' => 60000000, 'target_avg_viewers' => 50000, 'achieved_hours' => 40.0, 'achieved_revenue' => 54000000, 'status' => 'in_progress'],
            ['streamer_id' => 10, 'target_hours' => 55, 'target_revenue' => 35000000, 'target_avg_viewers' => 40000, 'achieved_hours' => 42.0, 'achieved_revenue' => 28800000, 'status' => 'in_progress'],
        ];

        foreach ($kpiData as $k) {
            DB::table('kpis')->insert(array_merge($k, [
                'month_year' => $currentMonth,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}

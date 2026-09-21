<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
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
            ['id' => 2, 'name' => 'Trần Minh Hoàng', 'email' => 'manager1@mcn.com', 'password' => $defaultPassword, 'role' => 'manager'],
            ['id' => 3, 'name' => 'Lê Thị Thu Thảo', 'email' => 'manager2@mcn.com', 'password' => $defaultPassword, 'role' => 'manager'],
            ['id' => 4, 'name' => 'Phạm Quốc Bảo', 'email' => 'manager3@mcn.com', 'password' => $defaultPassword, 'role' => 'manager'],
            ['id' => 5, 'name' => 'Nguyễn Văn Đạt (Đạt Pro)', 'email' => 'streamer1@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 6, 'name' => 'Vũ Hải Yến (Yến Cinn)', 'email' => 'streamer2@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 7, 'name' => 'Đỗ Khắc Nam (Nam Gamer)', 'email' => 'streamer3@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 8, 'name' => 'Phạm Quỳnh Anh (Anh Thỏ)', 'email' => 'streamer4@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 9, 'name' => 'Lê Tuấn Hưng (Hưng Tech)', 'email' => 'streamer5@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 10, 'name' => 'Hoàng Mỹ Duyên (Duyên Duyên)', 'email' => 'streamer6@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 11, 'name' => 'Bùi Quang Sáng (Sáng MOBA)', 'email' => 'streamer7@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 12, 'name' => 'Ngô Bảo Trâm (Trâm Chill)', 'email' => 'streamer8@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 13, 'name' => 'Trần Đức Bo (Bo Entertainment)', 'email' => 'streamer9@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
            ['id' => 14, 'name' => 'Đinh Gia Huy (Huy Esports)', 'email' => 'streamer10@mcn.com', 'password' => $defaultPassword, 'role' => 'streamer'],
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

        // 2. Streamer Profiles
        $profiles = [
            [
                'id' => 1, 'user_id' => 5, 'manager_id' => 2, 'stage_name' => 'Đạt Pro FPS', 'category' => 'Gaming',
                'bio' => 'Streamer chuyên game bắn súng CS2, Valorant, cựu tuyển thủ quốc gia với phong cách vui tính, kỹ năng cao.',
                'rate_per_hour' => 1500000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1200', 'status' => 'active',
            ],
            [
                'id' => 2, 'user_id' => 6, 'manager_id' => 2, 'stage_name' => 'Yến Cinn', 'category' => 'Just Chatting',
                'bio' => 'Streamer tâm sự đêm khuya, đàn hát acoustic, tương tác gắn kết người xem cực tốt với cộng đồng fan trung thành.',
                'rate_per_hour' => 800000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=1200', 'status' => 'active',
            ],
            [
                'id' => 3, 'user_id' => 7, 'manager_id' => 2, 'stage_name' => 'Nam Gamer', 'category' => 'Gaming',
                'bio' => 'Top 1 Thách Đấu Liên Minh Huyền Thoại Việt Nam, livestream phân tích chiến thuật và highlight meta game.',
                'rate_per_hour' => 2000000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1200', 'status' => 'active',
            ],
            [
                'id' => 4, 'user_id' => 8, 'manager_id' => 3, 'stage_name' => 'Anh Thỏ Lifestyle', 'category' => 'Lifestyle',
                'bio' => 'Kênh chia sẻ phong cách sống, makeup, du lịch và ẩm thực lành mạnh cho giới trẻ Gen Z.',
                'rate_per_hour' => 1200000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200', 'status' => 'active',
            ],
            [
                'id' => 5, 'user_id' => 9, 'manager_id' => 3, 'stage_name' => 'Hưng Tech Review', 'category' => 'Review',
                'bio' => 'Chuyên gia mở hộp, đánh giá smartphone, PC gaming, tai nghe và đồ gia dụng công nghệ cao.',
                'rate_per_hour' => 1800000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200', 'status' => 'active',
            ],
            [
                'id' => 6, 'user_id' => 10, 'manager_id' => 3, 'stage_name' => 'Duyên Duyên ASMR', 'category' => 'Just Chatting',
                'bio' => 'Nội dung ASMR thư giãn, đọc truyện đêm khuya, voice ngọt ngào giúp người xem giảm stress sau giờ làm việc.',
                'rate_per_hour' => 600000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=1200', 'status' => 'active',
            ],
            [
                'id' => 7, 'user_id' => 11, 'manager_id' => 4, 'stage_name' => 'Sáng MOBA', 'category' => 'Gaming',
                'bio' => 'Cao thủ Liên Quân Mobile và Tốc Chiến, sáng tạo các giáo án dị hài hước thu hút hàng chục nghìn view.',
                'rate_per_hour' => 1000000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=1200', 'status' => 'active',
            ],
            [
                'id' => 8, 'user_id' => 12, 'manager_id' => 4, 'stage_name' => 'Trâm Chill Music', 'category' => 'Review',
                'bio' => 'Review quán cafe chill, unboxing sách và sản phẩm decor phòng làm việc cho dân văn phòng.',
                'rate_per_hour' => 700000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1445384763658-0400939829cd?w=1200', 'status' => 'active',
            ],
            [
                'id' => 9, 'user_id' => 13, 'manager_id' => 4, 'stage_name' => 'Bo Entertainment', 'category' => 'Just Chatting',
                'bio' => 'Show giải trí, hài hước, phản ứng các video viral và tổ chức minigame tặng quà tương tác với khán giả.',
                'rate_per_hour' => 2500000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200', 'status' => 'active',
            ],
            [
                'id' => 10, 'user_id' => 14, 'manager_id' => 2, 'stage_name' => 'Huy Esports', 'category' => 'Gaming',
                'bio' => 'Bình luận viên giải đấu Esports Dota 2 và PUBG, kiến thức chuyên sâu, giọng đọc truyền cảm.',
                'rate_per_hour' => 1600000.00, 'avatar_url' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=400',
                'banner_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1200', 'status' => 'active',
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
                'start_time' => Carbon::now()->addDays(2), 'end_time' => Carbon::now()->addDays(2)->addHours(3),
                'job_description' => 'Livestream trải nghiệm chuột gaming Logitech G Pro X Superlight trong giải đấu nội bộ.',
                'budget' => 4500000.00, 'commission_rate' => 15.00, 'status' => 'approved', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 2, 'client_id' => 16, 'streamer_id' => 2, 'manager_id' => 2,
                'start_time' => Carbon::now()->addDays(3), 'end_time' => Carbon::now()->addDays(3)->addHours(2),
                'job_description' => 'Livestream ca hát kết hợp phát mã giảm giá Shopee 10.10 cho viewer.',
                'budget' => 2000000.00, 'commission_rate' => 15.00, 'status' => 'approved', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 3, 'client_id' => 15, 'streamer_id' => 3, 'manager_id' => 2,
                'start_time' => Carbon::now()->addDay(), 'end_time' => Carbon::now()->addDay()->addHours(4),
                'job_description' => 'Quảng bá màn hình Gaming Asus ROG Swift 360Hz trong trận đấu showmatch.',
                'budget' => 8000000.00, 'commission_rate' => 15.00, 'status' => 'pending', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 4, 'client_id' => 16, 'streamer_id' => 4, 'manager_id' => 3,
                'start_time' => Carbon::now()->addDays(5), 'end_time' => Carbon::now()->addDays(5)->addHours(2),
                'job_description' => 'Review bộ sản phẩm chăm sóc da mùa thu từ nhãn hàng Innisfree.',
                'budget' => 3000000.00, 'commission_rate' => 15.00, 'status' => 'pending', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 5, 'client_id' => 15, 'streamer_id' => 5, 'manager_id' => 3,
                'start_time' => Carbon::now()->subDays(3), 'end_time' => Carbon::now()->subDays(3)->addHours(3),
                'job_description' => 'Livestream unboxing bàn phím cơ không dây Custom Keychron Q1 Pro.',
                'budget' => 5400000.00, 'commission_rate' => 15.00, 'status' => 'completed', 'reject_reason' => null,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // 4. Schedules
        DB::table('schedules')->insert([
            [
                'id' => 1, 'streamer_id' => 1, 'event_type' => 'booking', 'reference_id' => 1,
                'start_time' => Carbon::now()->addDays(2), 'end_time' => Carbon::now()->addDays(2)->addHours(3),
                'title' => 'Booking: Hợp tác Logitech G Pro X (GearVN)',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 2, 'streamer_id' => 1, 'event_type' => 'stream', 'reference_id' => null,
                'start_time' => Carbon::now()->addDay()->setTime(19, 0), 'end_time' => Carbon::now()->addDay()->setTime(23, 0),
                'title' => 'Live CS2 Leo Rank Global Elite',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 3, 'streamer_id' => 2, 'event_type' => 'booking', 'reference_id' => 2,
                'start_time' => Carbon::now()->addDays(3), 'end_time' => Carbon::now()->addDays(3)->addHours(2),
                'title' => 'Booking: Shopee Mega Sale 10.10',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 4, 'streamer_id' => 3, 'event_type' => 'stream', 'reference_id' => null,
                'start_time' => Carbon::now()->addDays(2)->setTime(18, 0), 'end_time' => Carbon::now()->addDays(2)->setTime(22, 0),
                'title' => 'Live rank Thách Đấu LMHT Hàn Quốc',
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // 5. Stream Metrics
        DB::table('stream_metrics')->insert([
            ['streamer_id' => 1, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 4.5, 'avg_viewers' => 12500, 'peak_viewers' => 18200, 'followers_gained' => 450, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 1, 'platform' => 'youtube', 'stream_date' => Carbon::now()->subDays(2), 'duration_hours' => 3.5, 'avg_viewers' => 11000, 'peak_viewers' => 16000, 'followers_gained' => 320, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 2, 'platform' => 'tiktok', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 2.0, 'avg_viewers' => 18500, 'peak_viewers' => 26000, 'followers_gained' => 1500, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 3, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 6.0, 'avg_viewers' => 24000, 'peak_viewers' => 38500, 'followers_gained' => 1200, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 4, 'platform' => 'tiktok', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 2.5, 'avg_viewers' => 14200, 'peak_viewers' => 21000, 'followers_gained' => 850, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 5, 'platform' => 'youtube', 'stream_date' => Carbon::now()->subDays(2), 'duration_hours' => 3.0, 'avg_viewers' => 16500, 'peak_viewers' => 25000, 'followers_gained' => 620, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 6, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 2.5, 'avg_viewers' => 7500, 'peak_viewers' => 11000, 'followers_gained' => 190, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 7, 'platform' => 'facebook', 'stream_date' => Carbon::now()->subDays(2), 'duration_hours' => 4.0, 'avg_viewers' => 11500, 'peak_viewers' => 17000, 'followers_gained' => 410, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 8, 'platform' => 'tiktok', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 2.0, 'avg_viewers' => 6800, 'peak_viewers' => 9500, 'followers_gained' => 220, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 9, 'platform' => 'youtube', 'stream_date' => Carbon::yesterday(), 'duration_hours' => 4.0, 'avg_viewers' => 28000, 'peak_viewers' => 45000, 'followers_gained' => 1800, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
            ['streamer_id' => 10, 'platform' => 'twitch', 'stream_date' => Carbon::now()->subDays(2), 'duration_hours' => 5.0, 'avg_viewers' => 13500, 'peak_viewers' => 22000, 'followers_gained' => 520, 'source_type' => 'csv_import', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 6. KPIs
        $currentMonth = Carbon::now()->format('Y-m');
        $kpiData = [
            ['streamer_id' => 1, 'target_hours' => 70, 'target_revenue' => 25000000, 'target_avg_viewers' => 12000, 'achieved_hours' => 52.5, 'achieved_revenue' => 18500000, 'status' => 'in_progress'],
            ['streamer_id' => 2, 'target_hours' => 50, 'target_revenue' => 15000000, 'target_avg_viewers' => 9000, 'achieved_hours' => 38.0, 'achieved_revenue' => 12000000, 'status' => 'in_progress'],
            ['streamer_id' => 3, 'target_hours' => 80, 'target_revenue' => 35000000, 'target_avg_viewers' => 20000, 'achieved_hours' => 65.0, 'achieved_revenue' => 28000000, 'status' => 'in_progress'],
            ['streamer_id' => 4, 'target_hours' => 45, 'target_revenue' => 18000000, 'target_avg_viewers' => 10000, 'achieved_hours' => 30.0, 'achieved_revenue' => 11500000, 'status' => 'in_progress'],
            ['streamer_id' => 5, 'target_hours' => 60, 'target_revenue' => 22000000, 'target_avg_viewers' => 15000, 'achieved_hours' => 48.0, 'achieved_revenue' => 19000000, 'status' => 'in_progress'],
            ['streamer_id' => 6, 'target_hours' => 40, 'target_revenue' => 10000000, 'target_avg_viewers' => 7000, 'achieved_hours' => 42.0, 'achieved_revenue' => 10500000, 'status' => 'achieved'],
            ['streamer_id' => 7, 'target_hours' => 60, 'target_revenue' => 20000000, 'target_avg_viewers' => 12000, 'achieved_hours' => 45.0, 'achieved_revenue' => 14000000, 'status' => 'in_progress'],
            ['streamer_id' => 8, 'target_hours' => 40, 'target_revenue' => 12000000, 'target_avg_viewers' => 6000, 'achieved_hours' => 28.0, 'achieved_revenue' => 7500000, 'status' => 'in_progress'],
            ['streamer_id' => 9, 'target_hours' => 75, 'target_revenue' => 40000000, 'target_avg_viewers' => 25000, 'achieved_hours' => 58.0, 'achieved_revenue' => 32000000, 'status' => 'in_progress'],
            ['streamer_id' => 10, 'target_hours' => 65, 'target_revenue' => 24000000, 'target_avg_viewers' => 13000, 'achieved_hours' => 50.0, 'achieved_revenue' => 17500000, 'status' => 'in_progress'],
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

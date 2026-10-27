<?php

namespace Database\Seeders;

use App\Services\CsvMetricsService;
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
                'start_time' => Carbon::now()->addDay()->setTime(22, 0), 'end_time' => Carbon::now()->addDays(2)->setTime(2, 0), // kết thúc 02:00 ngày hôm sau
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

        // 5. Stream Metrics + booking đã hoàn thành trong tháng hiện tại (dữ liệu demo KPI)
        //    Hoàn toàn tất định (không random): mỗi streamer có số buổi, thời lượng/buổi và doanh thu cố định
        //    để KPI tính bởi aggregateKpis() rơi vào các mức khác nhau:
        //      - KillerQueen (#3), Xemesis (#9): vượt chỉ tiêu (>= 100%)
        //      - Nhism (#4): 75% | Thầy Giáo Ba (#7): 90% giờ / 70% doanh thu
        //      - QNT (#6): 40% | PewPew (#10): 57% giờ / 34% doanh thu
        //    Buổi stream rải đều từ ngày 1 đến ngày 26 của tháng; mỗi (streamer, platform, ngày) là duy nhất.
        $monthStart = Carbon::now()->startOfMonth();

        // streamer_id => [platform, số buổi, giờ/buổi, avg_viewers gốc, peak gốc, followers/buổi, [doanh thu booking hoàn thành]]
        $metricPlans = [
            1  => ['youtube', 16, 4.5, 125000, 245000, 8500, [25000000, 20000000]], // 72h / 90h = 80%
            2  => ['youtube', 14, 4.0, 48000, 86000, 2500, [20000000]],             // 56h / 70h = 80%
            3  => ['tiktok', 11, 5.0, 22000, 43000, 3200, [15000000, 13000000]],    // 55h / 50h = 110%, 28tr / 25tr = 112%
            4  => ['youtube', 13, 3.75, 36000, 69000, 1800, [14000000, 12250000]],  // 48.75h / 65h = 75%, 26.25tr / 35tr = 75%
            5  => ['youtube', 12, 4.0, 31000, 55000, 1400, [18000000]],             // 48h / 60h = 80%, 18tr / 30tr = 60%
            6  => ['youtube', 8, 2.5, 24000, 45000, 1100, [10000000]],              // 20h / 50h = 40%, 10tr / 25tr = 40%
            7  => ['youtube', 15, 4.5, 42000, 82000, 2100, [20000000, 15000000]],   // 67.5h / 75h = 90%, 35tr / 50tr = 70%
            8  => ['youtube', 10, 3.0, 38000, 76000, 2900, [22000000]],             // 30h / 60h = 50%, 22tr / 40tr = 55%
            9  => ['youtube', 12, 4.5, 55000, 115000, 4500, [30000000, 20000000]],  // 54h / 50h = 108%, >= 50tr / 60tr
            10 => ['tiktok', 9, 3.5, 45000, 92000, 3800, [12000000]],               // 31.5h / 55h = 57%, 12tr / 35tr = 34%
        ];

        $metricRows = [];
        $bookingId = 100; // tránh trùng id với các booking cố định ở trên
        foreach ($metricPlans as $streamerId => [$platform, $sessions, $hours, $avg, $peak, $followers, $completedBudgets]) {
            for ($n = 0; $n < $sessions; $n++) {
                // Ngày 1..26, phân bố đều và không trùng nhau
                $day = 1 + intdiv($n * 25, max($sessions - 1, 1));
                // Dao động lượt xem tất định quanh giá trị gốc: -5%, 0%, +5%
                $swing = (($n % 3) - 1) * 0.05;

                $metricRows[] = [
                    'streamer_id' => $streamerId,
                    'platform' => $platform,
                    'stream_date' => $monthStart->copy()->addDays($day - 1)->toDateString(),
                    'duration_hours' => $hours,
                    'avg_viewers' => (int) round($avg * (1 + $swing)),
                    'peak_viewers' => (int) round($peak * (1 + $swing)),
                    'followers_gained' => $followers,
                    'source_type' => 'csv_import',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Booking đã hoàn thành trong tháng (doanh thu KPI) + lịch khóa tương ứng
            foreach ($completedBudgets as $k => $budget) {
                $bookingId++;
                $start = $monthStart->copy()->addDays(4 + $k * 7 + ($streamerId % 3))->setTime(14, 0);
                $end = $start->copy()->addHours(3);

                DB::table('bookings')->insert([
                    'id' => $bookingId, 'client_id' => $bookingId % 2 ? 15 : 16, 'streamer_id' => $streamerId,
                    'manager_id' => DB::table('streamer_profiles')->where('id', $streamerId)->value('manager_id'),
                    'start_time' => $start, 'end_time' => $end,
                    'job_description' => 'Chiến dịch livestream quảng bá sản phẩm đã hoàn thành (dữ liệu demo).',
                    'budget' => $budget, 'commission_rate' => 15.00, 'status' => 'completed', 'reject_reason' => null,
                    'created_at' => now(), 'updated_at' => now(),
                ]);

                DB::table('schedules')->insert([
                    'streamer_id' => $streamerId, 'event_type' => 'booking', 'reference_id' => $bookingId,
                    'start_time' => $start, 'end_time' => $end,
                    'title' => 'Booking: Chiến dịch quảng bá (đã hoàn thành)',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        DB::table('stream_metrics')->insert($metricRows);

        // 6. KPIs: chỉ seed chỉ tiêu (target). Phần thực đạt (achieved_hours / achieved_revenue / status)
        //    được tính từ chính stream_metrics + bookings đã seed bằng CsvMetricsService::aggregateKpis()
        //    -> cùng logic với lúc import CSV thật, số liệu luôn khớp nhau.
        $currentMonth = Carbon::now()->format('Y-m');
        $kpiData = [
            ['streamer_id' => 1, 'target_hours' => 90, 'target_revenue' => 100000000, 'target_avg_viewers' => 120000],
            ['streamer_id' => 2, 'target_hours' => 70, 'target_revenue' => 45000000, 'target_avg_viewers' => 45000],
            ['streamer_id' => 3, 'target_hours' => 50, 'target_revenue' => 25000000, 'target_avg_viewers' => 20000],
            ['streamer_id' => 4, 'target_hours' => 65, 'target_revenue' => 35000000, 'target_avg_viewers' => 35000],
            ['streamer_id' => 5, 'target_hours' => 60, 'target_revenue' => 30000000, 'target_avg_viewers' => 30000],
            ['streamer_id' => 6, 'target_hours' => 50, 'target_revenue' => 25000000, 'target_avg_viewers' => 22000],
            ['streamer_id' => 7, 'target_hours' => 75, 'target_revenue' => 50000000, 'target_avg_viewers' => 40000],
            ['streamer_id' => 8, 'target_hours' => 60, 'target_revenue' => 40000000, 'target_avg_viewers' => 35000],
            ['streamer_id' => 9, 'target_hours' => 50, 'target_revenue' => 60000000, 'target_avg_viewers' => 50000],
            ['streamer_id' => 10, 'target_hours' => 55, 'target_revenue' => 35000000, 'target_avg_viewers' => 40000],
        ];

        foreach ($kpiData as $k) {
            DB::table('kpis')->insert(array_merge($k, [
                'month_year' => $currentMonth,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        app(CsvMetricsService::class)->aggregateKpis(
            collect($kpiData)->map(fn ($k) => [
                'streamer_id' => $k['streamer_id'],
                'month_year' => $currentMonth,
            ])->all()
        );

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}

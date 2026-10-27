<?php

namespace App\Http\Controllers;

use App\Models\StreamerProfile;
use App\Models\Schedule;
use App\Models\StreamMetric;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TalentController extends Controller
{
    /**
     * Danh sách Talent Streamers kèm bộ lọc đa tiêu chí
     */
    public function index(Request $request)
    {
        $query = StreamerProfile::where('status', 'active')->withViewerStats();

        // 1. Lọc theo từ khóa (Tên hoặc Bio)
        if ($request->filled('keyword')) {
            $kw = '%' . trim($request->keyword) . '%';
            $query->where(function ($q) use ($kw) {
                $q->where('stage_name', 'LIKE', $kw)
                  ->orWhere('bio', 'LIKE', $kw);
            });
        }

        // 2. Lọc theo Thể loại
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // 3. Lọc theo Khoảng giá Booking/giờ
        if ($request->filled('price_range')) {
            match ($request->price_range) {
                'under_1m' => $query->where('rate_per_hour', '<', 1000000),
                '1m_to_2m' => $query->whereBetween('rate_per_hour', [1000000, 2000000]),
                'above_2m' => $query->where('rate_per_hour', '>', 2000000),
                default => null,
            };
        }

        // 4. Sắp xếp & phân trang trực tiếp trong SQL
        match ($request->query('sort')) {
            'rate_asc' => $query->orderBy('rate_per_hour'),
            'rate_desc' => $query->orderByDesc('rate_per_hour'),
            'views_desc', null, '' => $query->orderByDesc('avg_viewers_calc'),
            default => null, // giá trị lạ -> chỉ sắp theo id bên dưới
        };

        $talents = $query->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        // Danh sách thể loại có sẵn cho bộ lọc dropdown
        $categories = StreamerProfile::distinct()->pluck('category');

        return view('talents.index', compact('talents', 'categories'));
    }

    /**
     * Trang chi tiết Hồ sơ Talent (Public Press Kit / Media Kit)
     */
    public function show($id)
    {
        $talent = StreamerProfile::with(['user', 'manager'])
            ->withViewerStats()
            ->findOrFail($id);

        // 1. Thống kê tổng hợp theo nền tảng stream
        $platformBreakdown = StreamMetric::where('streamer_id', $talent->id)
            ->selectRaw('platform, COUNT(*) as sessions, AVG(avg_viewers) as avg_viewers, MAX(peak_viewers) as peak_viewers, SUM(duration_hours) as total_hours')
            ->groupBy('platform')
            ->get();

        // 2. Lịch trình 7 ngày tới (giúp khách hàng dễ chọn khung giờ trống)
        $upcomingSchedules = Schedule::where('streamer_id', $talent->id)
            ->where('end_time', '>=', Carbon::now())
            ->where('start_time', '<=', Carbon::now()->addDays(7))
            ->orderBy('start_time')
            ->get();

        // 3. Các số liệu lịch sử 10 buổi gần nhất cho biểu đồ
        $recentMetrics = StreamMetric::where('streamer_id', $talent->id)
            ->orderBy('stream_date', 'asc')
            ->take(10)
            ->get();

        return view('talents.show', compact('talent', 'platformBreakdown', 'upcomingSchedules', 'recentMetrics'));
    }
}

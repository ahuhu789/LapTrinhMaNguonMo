<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleEventRequest;
use App\Http\Requests\UpdateStreamerProfileRequest;
use App\Models\Schedule;
use App\Models\Booking;
use App\Models\Kpi;
use App\Models\StreamMetric;
use App\Services\BookingCollisionService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StreamerController extends Controller
{
    /**
     * Dashboard Workspace cá nhân dành riêng cho Streamer
     */
    public function dashboard()
    {
        $user = Auth::user();
        $profile = $user->streamerProfile;

        if (!$profile) {
            return redirect()->route('home')->with('error', 'Bạn chưa được kích hoạt hồ sơ Talent.');
        }

        $currentMonth = Carbon::now()->format('Y-m');

        // 1. KPI tháng này
        $currentKpi = Kpi::where('streamer_id', $profile->id)
            ->where('month_year', $currentMonth)
            ->first();

        // 2. Bookings sắp tới
        $upcomingBookings = Booking::where('streamer_id', $profile->id)
            ->whereIn('status', ['approved', 'pending'])
            ->where('end_time', '>=', Carbon::now())
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // 3. Lịch stream 7 ngày tới
        $upcomingSchedules = Schedule::where('streamer_id', $profile->id)
            ->where('end_time', '>=', Carbon::now())
            ->orderBy('start_time')
            ->take(7)
            ->get();

        // 4. Số liệu metrics 14 ngày gần nhất để vẽ biểu đồ
        $chartMetrics = StreamMetric::where('streamer_id', $profile->id)
            ->orderBy('stream_date', 'asc')
            ->take(14)
            ->get();

        return view('streamer.dashboard', compact(
            'profile',
            'currentKpi',
            'upcomingBookings',
            'upcomingSchedules',
            'chartMetrics'
        ));
    }

    /**
     * Giao diện chỉnh sửa thông tin Media Kit cá nhân
     */
    public function editProfile()
    {
        $user = Auth::user();
        $profile = $user->streamerProfile;

        if (!$profile) {
            abort(404, 'Không tìm thấy hồ sơ streamer.');
        }

        $categories = config('mcn.categories');

        return view('streamer.profile_edit', compact('profile', 'categories'));
    }

    /**
     * Lưu cập nhật Media Kit cá nhân
     */
    public function updateProfile(UpdateStreamerProfileRequest $request)
    {
        $profile = $request->user()->streamerProfile;
        $validated = $request->validated();

        $profile->update($validated);

        return back()->with('success', 'Đã cập nhật Media Kit cá nhân thành công.');
    }

    /**
     * Xem và quản lý lịch trình cá nhân
     */
    public function schedule()
    {
        $user = Auth::user();
        $profile = $user->streamerProfile;

        if (!$profile) {
            abort(404);
        }

        $schedules = Schedule::where('streamer_id', $profile->id)
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        return view('streamer.schedule', compact('profile', 'schedules'));
    }

    /**
     * Streamer tự đăng ký lịch stream hoặc lịch cá nhân
     */
    public function addEvent(StoreScheduleEventRequest $request, BookingCollisionService $collisionService)
    {
        $profile = $request->user()->streamerProfile;
        $validated = $request->validated();

        // Kiểm tra xem có bị trùng với lịch nào không
        $collision = $collisionService->checkCollision($profile->id, $validated['start_time'], $validated['end_time']);
        if ($collision['has_conflict']) {
            return back()->withInput()->withErrors([
                'start_time' => 'Trùng lịch trình: ' . $collision['message']
            ]);
        }

        Schedule::create([
            'streamer_id' => $profile->id,
            'event_type' => $validated['event_type'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'title' => $validated['title'],
        ]);

        return back()->with('success', 'Đã thêm lịch mới vào thời gian biểu thành công.');
    }
}

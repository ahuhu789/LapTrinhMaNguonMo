<?php

namespace App\Http\Controllers;

use App\Models\Kpi;
use App\Models\StreamerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KpiController extends Controller
{
    /**
     * Danh sách KPI theo vai trò
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->query('month', Carbon::now()->format('Y-m'));

        $query = Kpi::with('streamer.user')->where('month_year', $selectedMonth);

        if ($user->isAdmin()) {
            $kpis = $query->paginate(20);
            $streamers = StreamerProfile::where('status', 'active')->orderBy('stage_name')->get();
        } elseif ($user->isManager()) {
            $streamerIds = $user->managedStreamers()->pluck('id');
            $kpis = $query->whereIn('streamer_id', $streamerIds)->paginate(20);
            $streamers = $user->managedStreamers;
        } elseif ($user->isStreamer()) {
            $profile = $user->streamerProfile;
            $kpis = $profile ? $query->where('streamer_id', $profile->id)->paginate(20) : collect();
            $streamers = collect();
        } else {
            abort(403);
        }

        return view('manager.kpis', compact('kpis', 'streamers', 'selectedMonth'));
    }

    /**
     * Thiết lập chỉ tiêu KPI cho Streamer (Dành cho Manager & Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'streamer_id' => 'required|exists:streamer_profiles,id',
            'month_year' => 'required|date_format:Y-m',
            'target_hours' => 'required|numeric|min:1',
            'target_revenue' => 'required|numeric|min:0',
            'target_avg_viewers' => 'required|integer|min:0',
        ], [
            'streamer_id.required' => 'Vui lòng chọn Streamer.',
            'month_year.required' => 'Vui lòng chọn tháng/năm áp dụng KPI.',
            'target_hours.min' => 'Số giờ stream mục tiêu tối thiểu là 1 giờ.',
            'target_revenue.min' => 'Doanh thu mục tiêu không thể là số âm.',
        ]);

        $user = Auth::user();
        $streamer = StreamerProfile::findOrFail($validated['streamer_id']);

        // IDOR Check: Manager chỉ được đặt KPI cho Streamer mình quản lý
        if (!$user->isAdmin() && $streamer->manager_id !== $user->id) {
            abort(403, 'Bạn chỉ có quyền đặt mục tiêu KPI cho Streamer thuộc nhóm mình phụ trách.');
        }

        Kpi::updateOrCreate(
            [
                'streamer_id' => $validated['streamer_id'],
                'month_year' => $validated['month_year'],
            ],
            [
                'target_hours' => $validated['target_hours'],
                'target_revenue' => $validated['target_revenue'],
                'target_avg_viewers' => $validated['target_avg_viewers'],
            ]
        );

        return back()->with('success', 'Đã thiết lập chỉ tiêu KPI tháng ' . $validated['month_year'] . ' cho streamer ' . $streamer->stage_name . ' thành công.');
    }
}

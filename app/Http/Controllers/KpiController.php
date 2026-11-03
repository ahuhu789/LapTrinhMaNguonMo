<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKpiRequest;
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

        // Route đã giới hạn role:manager,admin -> chỉ còn 2 nhánh
        if ($user->isAdmin()) {
            $kpis = $query->paginate(20);
            $streamers = StreamerProfile::where('status', 'active')->orderBy('stage_name')->get();
        } else {
            $streamers = $user->managedStreamers()->orderBy('stage_name')->get();
            $kpis = $query->whereIn('streamer_id', $streamers->pluck('id'))->paginate(20);
        }

        return view('manager.kpis', compact('kpis', 'streamers', 'selectedMonth'));
    }

    /**
     * Thiết lập chỉ tiêu KPI cho Streamer (Dành cho Manager & Admin)
     */
    public function store(StoreKpiRequest $request)
    {
        $validated = $request->validated();
        $streamer = StreamerProfile::findOrFail($validated['streamer_id']);

        // IDOR Check: Manager chỉ được đặt KPI cho Streamer mình quản lý
        $this->authorize('manage', $streamer);

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

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StreamerProfile;
use App\Models\Booking;
use App\Models\Kpi;
use App\Models\StreamMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Dashboard Quản trị cấp cao (Admin Control Center)
     */
    public function adminDashboard()
    {
        $currentMonth = Carbon::now()->format('Y-m');

        $stats = [
            'total_users' => User::count(),
            'total_managers' => User::where('role', 'manager')->count(),
            'total_streamers' => StreamerProfile::count(),
            'total_clients' => User::where('role', 'client')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'total_revenue' => Booking::whereIn('status', ['approved', 'completed'])->sum('budget'),
            'total_commission' => Booking::whereIn('status', ['approved', 'completed'])
                ->get()
                ->sum(fn($b) => $b->agency_commission),
        ];

        $recentBookings = Booking::with(['client', 'streamer', 'manager'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $activeTalents = StreamerProfile::with('manager')->get();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'activeTalents'));
    }

    /**
     * Dashboard Quản trị của Manager (Manager Control Center)
     */
    public function managerDashboard()
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->format('Y-m');

        // Danh sách Streamers thuộc quyền quản lý của Manager này
        $managedStreamers = StreamerProfile::where('manager_id', $user->id)
            ->with(['metrics', 'currentKpi'])
            ->get();

        $streamerIds = $managedStreamers->pluck('id');

        // Booking chờ duyệt của nhóm
        $pendingBookings = Booking::whereIn('streamer_id', $streamerIds)
            ->where('status', 'pending')
            ->with(['client', 'streamer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Thống kê doanh số tháng này của nhóm
        $teamRevenue = Booking::whereIn('streamer_id', $streamerIds)
            ->whereIn('status', ['approved', 'completed'])
            ->whereRaw("DATE_FORMAT(start_time, '%Y-%m') = ?", [$currentMonth])
            ->sum('budget');

        $kpis = Kpi::whereIn('streamer_id', $streamerIds)
            ->where('month_year', $currentMonth)
            ->with('streamer')
            ->get();

        return view('manager.dashboard', compact('managedStreamers', 'pendingBookings', 'teamRevenue', 'kpis'));
    }

    /**
     * Quản lý danh sách tài khoản toàn sàn (Dành cho Admin)
     */
    public function usersIndex(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function($q) use ($s) {
                $q->where('name', 'LIKE', $s)->orWhere('email', 'LIKE', $s);
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Thay đổi vai trò người dùng (Admin)
     */
    public function updateUserRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|in:admin,manager,streamer,client',
        ]);

        $user->role = $request->role;
        $user->save();

        // Nếu chuyển thành streamer mà chưa có profile thì tự tạo
        if ($user->role === 'streamer' && !$user->streamerProfile) {
            StreamerProfile::create([
                'user_id' => $user->id,
                'stage_name' => $user->name,
                'category' => 'Gaming',
                'rate_per_hour' => 500000,
                'status' => 'active',
            ]);
        }

        return back()->with('success', 'Đã cập nhật vai trò người dùng ' . $user->name . ' thành công.');
    }

    /**
     * Màn hình phân bổ Streamer cho Manager
     */
    public function assignStreamers()
    {
        $streamers = StreamerProfile::with(['user', 'manager'])->orderBy('stage_name')->get();
        $managers = User::where('role', 'manager')->orderBy('name')->get();

        return view('admin.assign_manager', compact('streamers', 'managers'));
    }

    /**
     * Cập nhật Manager phụ trách Streamer
     */
    public function updateStreamerManager(Request $request, $streamerId)
    {
        $request->validate([
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $streamer = StreamerProfile::findOrFail($streamerId);
        $streamer->manager_id = $request->manager_id;
        $streamer->save();

        return back()->with('success', 'Đã phân bổ Streamer ' . $streamer->stage_name . ' cho Manager thành công.');
    }
}

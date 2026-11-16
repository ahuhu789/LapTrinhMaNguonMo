<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStreamerManagerRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use App\Models\StreamerProfile;
use App\Models\Booking;
use App\Models\Kpi;
use App\Services\KpiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Dashboard Quản trị cấp cao (Admin Control Center)
     */
    public function adminDashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_managers' => User::where('role', 'manager')->count(),
            'total_streamers' => StreamerProfile::count(),
            'total_clients' => User::where('role', 'client')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            // Doanh thu THỰC = booking 'completed' (cùng định nghĩa với KPI)
            'total_revenue' => (float) KpiService::revenueQuery('completed')->sum('budget'),
            'total_commission' => round((float) KpiService::revenueQuery('completed')
                ->sum(DB::raw('budget * commission_rate / 100')), 2),
            // Doanh thu DỰ KIẾN = booking 'approved' (đã duyệt, chưa xác nhận hoàn thành)
            'projected_revenue' => (float) KpiService::revenueQuery('approved')->sum('budget'),
            'projected_commission' => round((float) KpiService::revenueQuery('approved')
                ->sum(DB::raw('budget * commission_rate / 100')), 2),
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

        // Admin xem TOÀN BỘ streamer; Manager chỉ xem streamer mình phụ trách
        $isAdminView = $user->isAdmin();
        $managedStreamers = StreamerProfile::query()
            ->unless($isAdminView, fn ($q) => $q->where('manager_id', $user->id))
            ->with(['currentKpi', 'manager'])
            ->orderBy('stage_name')
            ->get();

        $streamerIds = $managedStreamers->pluck('id');

        // Booking chờ xử lý của nhóm (chờ duyệt + đang xem xét)
        $pendingBookings = Booking::whereIn('streamer_id', $streamerIds)
            ->whereIn('status', Booking::APPROVABLE_STATUSES)
            ->with(['client', 'streamer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Doanh thu tháng này của nhóm: THỰC (completed, khớp KPI) và DỰ KIẾN (approved)
        $teamRevenue = (float) KpiService::revenueQuery('completed', $currentMonth)
            ->whereIn('streamer_id', $streamerIds)
            ->sum('budget');
        $teamProjectedRevenue = (float) KpiService::revenueQuery('approved', $currentMonth)
            ->whereIn('streamer_id', $streamerIds)
            ->sum('budget');

        $kpis = Kpi::whereIn('streamer_id', $streamerIds)
            ->where('month_year', $currentMonth)
            ->with('streamer')
            ->get();

        return view('manager.dashboard', compact('managedStreamers', 'pendingBookings', 'teamRevenue', 'teamProjectedRevenue', 'kpis', 'isAdminView'));
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
    public function updateUserRole(UpdateUserRoleRequest $request, User $user)
    {
        $user->update(['role' => $request->validated('role')]);

        // Chuyển thành streamer: tạo hồ sơ RỖNG nếu chưa có (không metrics, chỉ số hiển thị = 0 / "Chưa có dữ liệu")
        if ($user->role === 'streamer') {
            $user->streamerProfile()->firstOrCreate([], [
                'stage_name' => $user->name,
                'category' => config('mcn.default_category'),
                'rate_per_hour' => config('mcn.default_rate_per_hour'),
                'status' => 'active',
            ]);
        }

        return back()->with('success', 'Đã cập nhật vai trò người dùng ' . $user->name . ' thành công.');
    }

    /**
     * Xóa tài khoản (Admin). CHẶN (không soft delete) khi tài khoản còn booking:
     *  - bookings.client_id là FK RESTRICT -> không xóa được client có đơn;
     *  - xóa streamer sẽ CASCADE xóa hồ sơ + toàn bộ booking của talent -> mất lịch sử doanh thu/KPI.
     * Manager bị xóa: streamer_profiles.manager_id / bookings.manager_id tự về NULL (FK SET NULL).
     */
    public function destroyUser(User $user)
    {
        $this->authorize('delete', $user);

        $result = DB::transaction(function () use ($user) {
            // Khóa cùng thứ tự với tạo booking (client -> streamer) để không có booking mới chen vào
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $profile = StreamerProfile::where('user_id', $locked->id)->lockForUpdate()->first();

            $clientBookings = Booking::where('client_id', $locked->id)->count();
            $talentBookings = $profile ? Booking::where('streamer_id', $profile->id)->count() : 0;

            if ($clientBookings + $talentBookings > 0) {
                return [
                    'success' => false,
                    'message' => 'Không thể xóa tài khoản ' . $locked->name . ' vì đang có '
                        . ($clientBookings + $talentBookings) . ' booking liên quan (lịch sử hợp đồng & doanh thu). '
                        . 'Hãy đổi vai trò hoặc giữ lại tài khoản.',
                ];
            }

            $locked->delete();

            return ['success' => true, 'message' => 'Đã xóa tài khoản ' . $locked->name . ' (' . $locked->email . ').'];
        });

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
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
    public function updateStreamerManager(UpdateStreamerManagerRequest $request, StreamerProfile $streamer)
    {
        $streamer->update(['manager_id' => $request->validated('manager_id')]);

        return back()->with('success', 'Đã phân bổ Streamer ' . $streamer->stage_name . ' cho Manager thành công.');
    }
}

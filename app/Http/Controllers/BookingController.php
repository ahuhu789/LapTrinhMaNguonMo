<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\StreamerProfile;
use App\Models\Schedule;
use App\Services\BookingCollisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Màn hình danh sách booking phân quyền theo vai trò (RBAC)
     */
    public function index()
    {
        $user = Auth::user();
        $query = Booking::with(['client', 'streamer', 'manager'])->orderBy('created_at', 'desc');

        if ($user->isAdmin()) {
            // Admin xem toàn bộ
            $bookings = $query->paginate(15);
        } elseif ($user->isManager()) {
            // Manager xem booking của các Streamer mình quản lý hoặc được gán
            $streamerIds = $user->managedStreamers()->pluck('id');
            $bookings = $query->whereIn('streamer_id', $streamerIds)
                ->orWhere('manager_id', $user->id)
                ->paginate(15);
        } elseif ($user->isStreamer()) {
            // Streamer chỉ xem booking của chính mình
            $profile = $user->streamerProfile;
            $bookings = $profile ? $query->where('streamer_id', $profile->id)->paginate(15) : collect();
        } else {
            // Client chỉ xem các đơn booking do mình tạo
            $bookings = $query->where('client_id', $user->id)->paginate(15);
        }

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Giao diện gửi yêu cầu booking mới (Dành cho Client)
     */
    public function create(Request $request)
    {
        $selectedStreamerId = $request->query('streamer_id');
        $selectedStreamer = null;

        if ($selectedStreamerId) {
            $selectedStreamer = StreamerProfile::find($selectedStreamerId);
        }

        $streamers = StreamerProfile::where('status', 'active')->orderBy('stage_name')->get();

        return view('bookings.create', compact('streamers', 'selectedStreamer'));
    }

    /**
     * Xử lý gửi yêu cầu booking từ Client
     */
    public function store(Request $request, BookingCollisionService $collisionService)
    {
        $validated = $request->validate([
            'streamer_id' => 'required|exists:streamer_profiles,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'job_description' => 'required|string|min:10',
            'budget' => 'required|numeric|min:100000',
        ], [
            'streamer_id.required' => 'Vui lòng chọn Streamer muốn hợp tác.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_time.after' => 'Thời gian bắt đầu phải ở tương lai.',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'job_description.required' => 'Vui lòng mô tả chi tiết công việc hoặc kịch bản livestream.',
            'job_description.min' => 'Mô tả công việc tối thiểu 10 ký tự.',
            'budget.required' => 'Vui lòng nhập ngân sách dự kiến.',
            'budget.min' => 'Ngân sách tối thiểu là 100.000 VNĐ.',
        ]);

        $streamer = StreamerProfile::findOrFail($validated['streamer_id']);

        // Thuật toán kiểm tra xung đột thời gian (Collision Check) trước khi gửi
        $collisionCheck = $collisionService->checkCollision(
            $streamer->id,
            $validated['start_time'],
            $validated['end_time']
        );

        if ($collisionCheck['has_conflict']) {
            return back()->withInput()->withErrors([
                'start_time' => 'Khung giờ này đã bị trùng lịch! ' . $collisionCheck['message'],
            ]);
        }

        // Tạo đơn booking mới ở trạng thái 'pending'
        $booking = Booking::create([
            'client_id' => Auth::id(),
            'streamer_id' => $streamer->id,
            'manager_id' => $streamer->manager_id,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'job_description' => $validated['job_description'],
            'budget' => $validated['budget'],
            'commission_rate' => 15.00,
            'status' => 'pending',
        ]);

        return redirect()->route('bookings.show', $booking->id)
            ->with('success', 'Yêu cầu booking của bạn đã được gửi tới Manager phụ trách để phê duyệt.');
    }

    /**
     * Xem chi tiết một đơn booking (Kiểm tra IDOR chặt chẽ)
     */
    public function show($id, BookingCollisionService $collisionService)
    {
        $booking = Booking::with(['client', 'streamer.user', 'manager', 'schedule'])->findOrFail($id);
        $user = Auth::user();

        // Kiểm tra quyền sở hữu (Chống lỗ hổng IDOR)
        $isAuthorized = false;
        if ($user->isAdmin()) {
            $isAuthorized = true;
        } elseif ($user->isManager() && ($booking->manager_id === $user->id || $booking->streamer->manager_id === $user->id)) {
            $isAuthorized = true;
        } elseif ($user->isStreamer() && $booking->streamer->user_id === $user->id) {
            $isAuthorized = true;
        } elseif ($user->isClient() && $booking->client_id === $user->id) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'Bạn không có quyền truy cập hợp đồng booking này.');
        }

        // Kiểm tra xem hiện tại có đang bị trùng với lịch nào không (để hiển thị cảnh báo cho Manager)
        $conflictInfo = null;
        if (in_array($booking->status, ['pending', 'reviewing'])) {
            $conflictInfo = $collisionService->checkCollision(
                $booking->streamer_id,
                $booking->start_time,
                $booking->end_time,
                $booking->id
            );
        }

        return view('bookings.show', compact('booking', 'conflictInfo'));
    }

    /**
     * Manager / Admin phê duyệt booking & tự động khóa lịch
     */
    public function approve($id, BookingCollisionService $collisionService)
    {
        $booking = Booking::with('streamer')->findOrFail($id);
        $user = Auth::user();

        // Kiểm tra quyền duyệt (Admin hoặc Manager phụ trách)
        if (!$user->isAdmin() && $booking->streamer->manager_id !== $user->id && $booking->manager_id !== $user->id) {
            abort(403, 'Bạn không được phân công quản lý Talent này.');
        }

        $result = $collisionService->approveAndLockSchedule($booking, $user->id);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Manager / Admin từ chối booking kèm lý do
     */
    public function reject(Request $request, $id, BookingCollisionService $collisionService)
    {
        $request->validate([
            'reject_reason' => 'required|string|min:5|max:500',
        ], [
            'reject_reason.required' => 'Vui lòng cung cấp lý do từ chối để phản hồi đến khách hàng.',
        ]);

        $booking = Booking::with('streamer')->findOrFail($id);
        $user = Auth::user();

        if (!$user->isAdmin() && $booking->streamer->manager_id !== $user->id && $booking->manager_id !== $user->id) {
            abort(403, 'Bạn không có quyền từ chối booking này.');
        }

        $result = $collisionService->rejectBooking($booking, $user->id, $request->reject_reason);

        return back()->with('success', $result['message']);
    }
}

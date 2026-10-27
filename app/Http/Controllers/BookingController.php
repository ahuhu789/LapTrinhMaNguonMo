<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckCollisionRequest;
use App\Http\Requests\RejectBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\StreamerProfile;
use App\Services\BookingCollisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            $bookings = $query->where(function ($q) use ($streamerIds, $user) {
                $q->whereIn('streamer_id', $streamerIds)
                  ->orWhere('manager_id', $user->id);
            })->paginate(15);
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
    public function store(StoreBookingRequest $request, BookingCollisionService $collisionService)
    {
        $validated = $request->validated();
        $streamer = StreamerProfile::findOrFail($validated['streamer_id']);

        // Quy tắc lịch: booking trùng giờ vẫn được lưu nhưng bị từ chối tự động kèm lý do
        $booking = $collisionService->createBooking($streamer, $request->user(), $validated);

        if ($booking->status === 'rejected') {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Yêu cầu booking đã bị từ chối tự động do trùng lịch. ' . $booking->reject_reason);
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Yêu cầu booking của bạn đã được gửi tới Manager phụ trách để phê duyệt.');
    }

    /**
     * Kiểm tra nhanh xung đột lịch (AJAX live validation trên form booking)
     */
    public function checkCollision(CheckCollisionRequest $request, BookingCollisionService $collisionService): JsonResponse
    {
        $validated = $request->validated();

        // Quy tắc 1 (lịch talent) + quy tắc 2 (booking của chính người dùng đang đăng nhập)
        $result = $collisionService->checkCollision(
            (int) $validated['streamer_id'],
            $validated['start_time'],
            $validated['end_time'],
            null,
            $request->user()->id
        );

        return response()->json(['has_conflict' => (bool) $result['has_conflict']]);
    }

    /**
     * Xem chi tiết một đơn booking (chống IDOR qua BookingPolicy@view)
     */
    public function show(Booking $booking, BookingCollisionService $collisionService)
    {
        $booking->load(['client', 'streamer.user', 'manager', 'schedule']);

        $this->authorize('view', $booking);

        // Cảnh báo cho Manager nếu khung giờ đã bị chiếm bởi lịch đã khóa/duyệt (duyệt sẽ bị từ chối tự động)
        $conflictInfo = $booking->isApprovable()
            ? $collisionService->checkApprovalConflict($booking)
            : null;

        return view('bookings.show', compact('booking', 'conflictInfo'));
    }

    /**
     * Manager / Admin phê duyệt booking & tự động khóa lịch
     */
    public function approve(Booking $booking, BookingCollisionService $collisionService)
    {
        $booking->load('streamer');

        $this->authorize('approve', $booking);

        $result = $collisionService->approveAndLockSchedule($booking, Auth::id());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Manager / Admin từ chối booking kèm lý do (quyền kiểm tra trong RejectBookingRequest)
     */
    public function reject(RejectBookingRequest $request, Booking $booking, BookingCollisionService $collisionService)
    {
        $result = $collisionService->rejectBooking($booking, Auth::id(), $request->validated('reject_reason'));

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}

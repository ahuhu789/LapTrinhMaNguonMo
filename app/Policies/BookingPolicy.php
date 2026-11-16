<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Tạo booking (form, gửi đơn, check-collision): chỉ tài khoản client.
     * Khách chưa đăng nhập -> false (Gate trả false khi không có user).
     */
    public function create(User $user): Response
    {
        return $user->isClient()
            ? Response::allow()
            : Response::deny('Chỉ tài khoản Khách hàng (client) mới có thể đặt booking.');
    }

    /**
     * Xem chi tiết booking (chống IDOR): Admin, Manager phụ trách,
     * Streamer được booking hoặc Client đã tạo đơn.
     */
    public function view(User $user, Booking $booking): Response
    {
        $allowed = match (true) {
            $user->isAdmin() => true,
            $user->isManager() => $this->isResponsibleManager($user, $booking),
            $user->isStreamer() => $booking->streamer?->user_id === $user->id,
            $user->isClient() => $booking->client_id === $user->id,
            default => false,
        };

        return $allowed
            ? Response::allow()
            : Response::deny('Bạn không có quyền truy cập hợp đồng booking này.');
    }

    /**
     * Phê duyệt booking: Admin hoặc Manager phụ trách Talent.
     */
    public function approve(User $user, Booking $booking): Response
    {
        return $this->canManage($user, $booking)
            ? Response::allow()
            : Response::deny('Bạn không được phân công quản lý Talent này.');
    }

    /**
     * Từ chối booking: Admin hoặc Manager phụ trách Talent.
     */
    public function reject(User $user, Booking $booking): Response
    {
        return $this->canManage($user, $booking)
            ? Response::allow()
            : Response::deny('Bạn không có quyền từ chối booking này.');
    }

    /**
     * Chuyển pending -> reviewing: Admin hoặc Manager phụ trách Talent.
     */
    public function review(User $user, Booking $booking): Response
    {
        return $this->canManage($user, $booking)
            ? Response::allow()
            : Response::deny('Bạn không có quyền xử lý booking này.');
    }

    /**
     * Đánh dấu hoàn thành (approved -> completed): Admin hoặc Manager phụ trách Talent.
     * Điều kiện trạng thái/thời gian kiểm tra trong BookingLifecycleService (dưới khóa hàng).
     */
    public function complete(User $user, Booking $booking): Response
    {
        return $this->canManage($user, $booking)
            ? Response::allow()
            : Response::deny('Bạn không có quyền xác nhận hoàn thành booking này.');
    }

    /**
     * Hủy booking: Client chủ đơn, hoặc Admin / Manager phụ trách Talent.
     */
    public function cancel(User $user, Booking $booking): Response
    {
        $allowed = $user->isClient()
            ? $booking->client_id === $user->id
            : $this->canManage($user, $booking);

        return $allowed
            ? Response::allow()
            : Response::deny('Bạn không có quyền hủy booking này.');
    }

    /**
     * Xóa booking: chỉ Client chủ đơn (trạng thái rejected/cancelled kiểm tra ở controller).
     */
    public function delete(User $user, Booking $booking): Response
    {
        return $user->isClient() && $booking->client_id === $user->id
            ? Response::allow()
            : Response::deny('Chỉ khách hàng tạo đơn mới được xóa booking này.');
    }

    private function canManage(User $user, Booking $booking): bool
    {
        return $user->isAdmin()
            || ($user->isManager() && $this->isResponsibleManager($user, $booking));
    }

    private function isResponsibleManager(User $user, Booking $booking): bool
    {
        return $booking->manager_id === $user->id
            || $booking->streamer?->manager_id === $user->id;
    }
}

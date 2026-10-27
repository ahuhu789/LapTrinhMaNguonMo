<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
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

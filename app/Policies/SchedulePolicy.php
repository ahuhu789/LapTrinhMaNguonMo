<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SchedulePolicy
{
    /**
     * Xóa lịch: chỉ Streamer sở hữu lịch đó.
     */
    public function delete(User $user, Schedule $schedule): Response
    {
        return $user->isStreamer() && $user->streamerProfile?->id === $schedule->streamer_id
            ? Response::allow()
            : Response::deny('Bạn chỉ có thể xóa lịch trình của chính mình.');
    }
}

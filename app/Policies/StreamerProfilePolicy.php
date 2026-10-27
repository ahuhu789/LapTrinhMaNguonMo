<?php

namespace App\Policies;

use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StreamerProfilePolicy
{
    /**
     * Quản lý vận hành Talent (đặt KPI, ...): Admin hoặc Manager được phân công.
     */
    public function manage(User $user, StreamerProfile $streamer): Response
    {
        return $user->isAdmin() || ($user->isManager() && $streamer->manager_id === $user->id)
            ? Response::allow()
            : Response::deny('Bạn chỉ có quyền thao tác trên Streamer thuộc nhóm mình phụ trách.');
    }
}

<?php

namespace App\Policies;

use App\Models\Kpi;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KpiPolicy
{
    /**
     * Xóa mục tiêu KPI: Admin, hoặc Manager phụ trách streamer của KPI đó.
     */
    public function delete(User $user, Kpi $kpi): Response
    {
        return $user->isAdmin() || ($user->isManager() && $kpi->streamer?->manager_id === $user->id)
            ? Response::allow()
            : Response::deny('Bạn chỉ có quyền xóa KPI của Streamer thuộc nhóm mình phụ trách.');
    }
}

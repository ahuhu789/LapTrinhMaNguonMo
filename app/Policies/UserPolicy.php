<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Xóa tài khoản: chỉ Admin, và không được tự xóa chính mình.
     * Điều kiện dữ liệu (còn booking) kiểm tra trong AdminController@destroyUser dưới khóa hàng.
     */
    public function delete(User $actor, User $target): Response
    {
        if (!$actor->isAdmin()) {
            return Response::deny('Chỉ Admin mới có quyền xóa tài khoản.');
        }

        return $actor->is($target)
            ? Response::deny('Bạn không thể tự xóa tài khoản của chính mình.')
            : Response::allow();
    }
}

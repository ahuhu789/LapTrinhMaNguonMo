<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['admin', 'manager', 'streamer', 'client'])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò không hợp lệ.',
        ];
    }

    /**
     * Chặn Admin tự thay đổi vai trò của chính mình (tránh tự khóa quyền quản trị).
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $target = $this->route('user');

                if ($target && $target->is($this->user())) {
                    $validator->errors()->add('role', 'Bạn không thể tự thay đổi vai trò của chính mình.');
                }
            },
        ];
    }
}

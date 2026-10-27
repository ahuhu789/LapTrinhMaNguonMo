<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStreamerManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'manager_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'manager'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'manager_id.exists' => 'Người được chọn không tồn tại hoặc không phải là Manager.',
        ];
    }
}

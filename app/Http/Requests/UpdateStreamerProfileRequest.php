<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStreamerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->streamerProfile !== null;
    }

    public function rules(): array
    {
        return [
            'stage_name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'rate_per_hour' => ['required', 'numeric', 'min:100000'],
            'avatar_url' => ['nullable', 'url', 'max:255'],
            'banner_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'stage_name.required' => 'Nghệ danh không được để trống.',
            'rate_per_hour.min' => 'Giá tối thiểu là 100.000 VNĐ/giờ.',
        ];
    }
}

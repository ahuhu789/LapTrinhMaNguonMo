<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiRecommendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Vui lòng nhập nhu cầu booking của bạn.',
            'message.string' => 'Nội dung không hợp lệ.',
            'message.min' => 'Nội dung quá ngắn.',
            'message.max' => 'Nội dung tối đa 500 ký tự.',
        ];
    }
}

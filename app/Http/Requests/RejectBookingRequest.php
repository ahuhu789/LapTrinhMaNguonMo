<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RejectBookingRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('reject', $this->route('booking'));
    }

    public function rules(): array
    {
        return [
            'reject_reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reject_reason.required' => 'Vui lòng cung cấp lý do từ chối để phản hồi đến khách hàng.',
            'reject_reason.string' => 'Lý do từ chối không hợp lệ.',
            'reject_reason.min' => 'Lý do từ chối tối thiểu 5 ký tự.',
            'reject_reason.max' => 'Lý do từ chối tối đa 500 ký tự.',
        ];
    }
}

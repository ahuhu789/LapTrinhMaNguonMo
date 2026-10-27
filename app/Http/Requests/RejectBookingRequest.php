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
        ];
    }
}

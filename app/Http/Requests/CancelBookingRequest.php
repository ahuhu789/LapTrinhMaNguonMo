<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CancelBookingRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('cancel', $this->route('booking'));
    }

    /**
     * Manager/Admin bắt buộc ghi lý do (thông báo cho khách hàng); Client được bỏ trống.
     */
    public function rules(): array
    {
        return [
            'cancel_reason' => [
                Rule::requiredIf(fn () => !$this->user()->isClient()),
                'nullable', 'string', 'min:5', 'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cancel_reason.required' => 'Vui lòng nhập lý do hủy booking để thông báo đến khách hàng.',
            'cancel_reason.string' => 'Lý do hủy không hợp lệ.',
            'cancel_reason.min' => 'Lý do hủy tối thiểu 5 ký tự.',
            'cancel_reason.max' => 'Lý do hủy tối đa 500 ký tự.',
        ];
    }
}

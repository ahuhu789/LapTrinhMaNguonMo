<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Concerns\NormalizesDateTimes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckCollisionRequest extends FormRequest
{
    use NormalizesDateTimes;

    public function authorize(): bool
    {
        return Gate::allows('create', Booking::class);
    }

    /** Endpoint AJAX: luôn trả 403 dạng JSON. */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'message' => Gate::inspect('create', Booking::class)->message()
                ?? 'Chỉ tài khoản Khách hàng (client) mới có thể đặt booking.',
        ], 403));
    }

    public function rules(): array
    {
        return [
            'streamer_id' => ['required', 'integer', 'exists:streamer_profiles,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'streamer_id.required' => 'Vui lòng chọn Streamer.',
            'streamer_id.integer' => 'Streamer không hợp lệ.',
            'streamer_id.exists' => 'Streamer không tồn tại.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_time.date' => 'Thời gian bắt đầu không hợp lệ.',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_time.date' => 'Thời gian kết thúc không hợp lệ.',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
        ];
    }

    /**
     * Endpoint AJAX: luôn trả lỗi validation dạng JSON 422 (kể cả khi client không gửi Accept: application/json).
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}

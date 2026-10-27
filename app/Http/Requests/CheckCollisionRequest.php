<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckCollisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
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

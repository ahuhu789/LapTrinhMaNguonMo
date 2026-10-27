<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'streamer_id' => ['required', 'integer', 'exists:streamer_profiles,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'job_description' => ['required', 'string', 'min:10'],
            'budget' => ['required', 'numeric', 'min:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'streamer_id.required' => 'Vui lòng chọn Streamer muốn hợp tác.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_time.after' => 'Thời gian bắt đầu phải ở tương lai.',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'job_description.required' => 'Vui lòng mô tả chi tiết công việc hoặc kịch bản livestream.',
            'job_description.min' => 'Mô tả công việc tối thiểu 10 ký tự.',
            'budget.required' => 'Vui lòng nhập ngân sách dự kiến.',
            'budget.min' => 'Ngân sách tối thiểu là 100.000 VNĐ.',
        ];
    }
}

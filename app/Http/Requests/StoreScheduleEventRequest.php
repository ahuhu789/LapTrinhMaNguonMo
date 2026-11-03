<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesDateTimes;
use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleEventRequest extends FormRequest
{
    use NormalizesDateTimes;

    public function authorize(): bool
    {
        return $this->user()?->streamerProfile !== null;
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', 'in:stream,personal,training'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'title' => ['required', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_type.required' => 'Vui lòng chọn loại lịch.',
            'event_type.in' => 'Loại lịch không hợp lệ.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_time.date' => 'Thời gian bắt đầu không hợp lệ.',
            'start_time.after' => 'Thời gian bắt đầu phải ở tương lai.',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_time.date' => 'Thời gian kết thúc không hợp lệ.',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'title.required' => 'Vui lòng nhập tiêu đề lịch.',
            'title.string' => 'Tiêu đề không hợp lệ.',
            'title.max' => 'Tiêu đề tối đa 150 ký tự.',
        ];
    }
}

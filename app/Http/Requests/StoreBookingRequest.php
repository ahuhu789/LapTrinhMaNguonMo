<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesDateTimes;
use App\Models\Booking;
use App\Models\StreamerProfile;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use App\Services\BookingPricing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    use NormalizesDateTimes;

    public function authorize(): Response
    {
        // BookingPolicy@create: chỉ client (streamer/manager/admin -> 403 kèm thông báo tiếng Việt)
        return Gate::inspect('create', Booking::class);
    }

    public function rules(): array
    {
        return [
            'streamer_id' => ['required', 'integer', 'exists:streamer_profiles,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'job_description' => ['required', 'string', 'min:10'],
            // số nguyên đồng bất kỳ; mức tối thiểu phụ thuộc thời lượng × giá/giờ (kiểm tra trong after())
            'budget' => ['required', 'integer', 'min:0', 'max:999999999999'],
        ];
    }

    /**
     * Ngân sách >= số giờ (tính theo phút) × rate_per_hour của talent.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['streamer_id', 'start_time', 'end_time', 'budget'])) {
                    return;
                }

                $streamer = StreamerProfile::find($this->input('streamer_id'));
                $quote = app(BookingPricing::class)->quote(
                    $streamer,
                    Carbon::parse($this->input('start_time')),
                    Carbon::parse($this->input('end_time'))
                );

                if ((int) $this->input('budget') < $quote['min_budget']) {
                    $validator->errors()->add('budget', $quote['label']);
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'streamer_id.required' => 'Vui lòng chọn Streamer muốn hợp tác.',
            'streamer_id.integer' => 'Streamer không hợp lệ.',
            'streamer_id.exists' => 'Streamer không tồn tại.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_time.date' => 'Thời gian bắt đầu không hợp lệ.',
            'start_time.after' => 'Thời gian bắt đầu phải ở tương lai.',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_time.date' => 'Thời gian kết thúc không hợp lệ.',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'job_description.required' => 'Vui lòng mô tả chi tiết công việc hoặc kịch bản livestream.',
            'job_description.string' => 'Mô tả công việc không hợp lệ.',
            'job_description.min' => 'Mô tả công việc tối thiểu 10 ký tự.',
            'budget.required' => 'Vui lòng nhập ngân sách dự kiến.',
            'budget.integer' => 'Ngân sách phải là số nguyên (đơn vị đồng).',
            'budget.min' => 'Ngân sách không được âm.',
            'budget.max' => 'Ngân sách vượt quá giới hạn cho phép.',
        ];
    }
}

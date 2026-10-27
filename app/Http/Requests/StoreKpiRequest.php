<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKpiRequest extends FormRequest
{
    /**
     * Quyền trên streamer cụ thể được kiểm tra bằng StreamerProfilePolicy@manage
     * trong controller (sau khi streamer_id đã được validate).
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'streamer_id' => ['required', 'integer', 'exists:streamer_profiles,id'],
            'month_year' => ['required', 'date_format:Y-m'],
            'target_hours' => ['required', 'numeric', 'min:1'],
            'target_revenue' => ['required', 'numeric', 'min:0'],
            'target_avg_viewers' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'streamer_id.required' => 'Vui lòng chọn Streamer.',
            'month_year.required' => 'Vui lòng chọn tháng/năm áp dụng KPI.',
            'target_hours.min' => 'Số giờ stream mục tiêu tối thiểu là 1 giờ.',
            'target_revenue.min' => 'Doanh thu mục tiêu không thể là số âm.',
        ];
    }
}

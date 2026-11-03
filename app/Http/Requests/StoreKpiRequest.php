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
            'streamer_id.integer' => 'Streamer không hợp lệ.',
            'streamer_id.exists' => 'Streamer không tồn tại.',
            'month_year.required' => 'Vui lòng chọn tháng/năm áp dụng KPI.',
            'month_year.date_format' => 'Tháng/năm phải có định dạng YYYY-MM.',
            'target_hours.required' => 'Vui lòng nhập số giờ stream mục tiêu.',
            'target_hours.numeric' => 'Số giờ stream mục tiêu phải là số.',
            'target_hours.min' => 'Số giờ stream mục tiêu tối thiểu là 1 giờ.',
            'target_revenue.required' => 'Vui lòng nhập doanh thu mục tiêu.',
            'target_revenue.numeric' => 'Doanh thu mục tiêu phải là số.',
            'target_revenue.min' => 'Doanh thu mục tiêu không thể là số âm.',
            'target_avg_viewers.required' => 'Vui lòng nhập số người xem trung bình mục tiêu.',
            'target_avg_viewers.integer' => 'Số người xem trung bình mục tiêu phải là số nguyên.',
            'target_avg_viewers.min' => 'Số người xem trung bình mục tiêu không thể là số âm.',
        ];
    }
}

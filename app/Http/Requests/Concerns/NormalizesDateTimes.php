<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Carbon;
use Throwable;

/**
 * Chuẩn hóa các trường thời gian trước khi validate:
 *  - "2026-10-29T14:00" (input datetime-local) được hiểu là giờ địa phương (config app.timezone)
 *  - chuỗi có offset/Z (vd từ Date.toISOString()) được quy đổi về giờ địa phương
 *  - kết quả luôn có dạng "Y-m-d H:i:s" - trùng định dạng lưu trong DB, so sánh chuỗi trên SQLite chính xác
 * Giá trị không parse được giữ nguyên để rule 'date' báo lỗi tiếng Việt.
 */
trait NormalizesDateTimes
{
    /** @return array<string> */
    protected function dateTimeFields(): array
    {
        return ['start_time', 'end_time'];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ($this->dateTimeFields() as $field) {
            $value = $this->input($field);

            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            try {
                $normalized[$field] = Carbon::parse(trim($value))
                    ->setTimezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s');
            } catch (Throwable) {
                // để rule 'date' xử lý
            }
        }

        $this->merge($normalized);
    }
}

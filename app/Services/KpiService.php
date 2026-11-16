<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Kpi;
use App\Models\StreamMetric;
use Illuminate\Support\Carbon;

/**
 * Nguồn tính KPI DUY NHẤT (import CSV, cập nhật chỉ tiêu, booking hoàn thành, seeder):
 *  - achieved_hours   = tổng duration_hours của stream_metrics trong tháng
 *  - achieved_revenue = tổng budget của booking 'completed' có start_time trong tháng
 *  - status           = 'achieved'    ("Hoàn thành") khi CẢ giờ và doanh thu >= 100% chỉ tiêu;
 *                       'failed'      ("Không đạt")  khi tháng đã kết thúc mà chưa đạt;
 *                       'in_progress' ("Đang thực hiện") trong các trường hợp còn lại.
 */
class KpiService
{
    /**
     * Tính lại KPI của một streamer trong tháng (Y-m). Trả về null nếu chưa đặt chỉ tiêu tháng đó.
     */
    public function recompute(int $streamerId, string $monthYear): ?Kpi
    {
        $kpi = Kpi::where('streamer_id', $streamerId)->where('month_year', $monthYear)->first();

        if (!$kpi) {
            return null;
        }

        $month = Carbon::createFromFormat('!Y-m', $monthYear);

        $hours = StreamMetric::where('streamer_id', $streamerId)
            ->whereBetween('stream_date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->sum('duration_hours');

        $revenue = Booking::where('streamer_id', $streamerId)
            ->where('status', 'completed')
            ->where('start_time', '>=', $month->copy()->startOfMonth())
            ->where('start_time', '<', $month->copy()->addMonthNoOverflow()->startOfMonth())
            ->sum('budget');

        // Làm tròn 2 chữ số thập phân dạng chuỗi -> khớp cột decimal(…,2), tránh sai số float khi so sánh
        $kpi->achieved_hours = number_format((float) $hours, 2, '.', '');
        $kpi->achieved_revenue = number_format((float) $revenue, 2, '.', '');
        $kpi->status = self::statusFor($kpi, $monthYear);
        $kpi->save();

        return $kpi;
    }

    /**
     * Chốt các KPI của tháng ĐÃ KẾT THÚC còn kẹt ở 'in_progress' (không có sự kiện nào kích hoạt recompute
     * sau khi sang tháng mới). Gọi khi mở trang KPI / dashboard manager và theo lịch (kpi:close-months).
     *
     * @return int Số KPI đã được tính lại
     */
    public function closeEndedMonths(): int
    {
        return $this->recomputeMany(
            Kpi::where('status', 'in_progress')
                ->where('month_year', '<', Carbon::now()->format('Y-m'))
                ->get(['streamer_id', 'month_year'])
        );
    }

    public static function statusFor(Kpi $kpi, string $monthYear): string
    {
        if (self::isAchieved($kpi)) {
            return 'achieved';
        }

        return self::monthHasEnded($monthYear) ? 'failed' : 'in_progress';
    }

    /** Tháng (Y-m) đã kết thúc theo giờ ứng dụng: bây giờ >= 00:00 ngày 1 tháng sau */
    public static function monthHasEnded(string $monthYear): bool
    {
        return Carbon::now()->gte(Carbon::createFromFormat('!Y-m', $monthYear)->addMonthNoOverflow()->startOfMonth());
    }

    /**
     * @param iterable<array{streamer_id: int, month_year: string}> $targets
     * @return int Số KPI đã cập nhật
     */
    public function recomputeMany(iterable $targets): int
    {
        $count = 0;

        foreach ($targets as $t) {
            if ($this->recompute((int) $t['streamer_id'], $t['month_year'])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * So sánh theo đơn vị "phần trăm" (cents) bằng số nguyên -> 100% đúng bằng chỉ tiêu được tính là đạt.
     */
    public static function isAchieved(Kpi $kpi): bool
    {
        return self::meets($kpi->achieved_hours, $kpi->target_hours)
            && self::meets($kpi->achieved_revenue, $kpi->target_revenue);
    }

    public static function meets($actual, $target): bool
    {
        return self::cents($actual) >= self::cents($target);
    }

    private static function cents($value): int
    {
        return (int) round(((float) $value) * 100);
    }
}

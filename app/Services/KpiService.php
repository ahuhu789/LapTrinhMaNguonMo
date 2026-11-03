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
 *  - status           = 'achieved' khi CẢ giờ và doanh thu >= 100% chỉ tiêu, ngược lại 'in_progress'
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
        $kpi->status = self::isAchieved($kpi) ? 'achieved' : 'in_progress';
        $kpi->save();

        return $kpi;
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

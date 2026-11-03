<?php

namespace App\Services;

use App\Models\StreamerProfile;
use Carbon\CarbonInterface;

/**
 * Ngân sách tối thiểu của một booking = số giờ × giá/giờ của talent.
 * Số giờ tính chính xác theo PHÚT (không làm tròn lên giờ), kết quả làm tròn LÊN tới 1 đồng.
 * Tính bằng số nguyên (xu) để không có sai số float.
 */
class BookingPricing
{
    /**
     * @return array{minutes: int, hours: float, rate_per_hour: int, min_budget: int, label: string}
     */
    public function quote(StreamerProfile|int|float|string $rate, CarbonInterface $start, CarbonInterface $end): array
    {
        $ratePerHour = $rate instanceof StreamerProfile ? $rate->rate_per_hour : $rate;
        $rateCents = (int) round(((float) $ratePerHour) * 100);
        $minutes = max(0, (int) $start->diffInMinutes($end, false));

        // ceil(minutes * rate / 60) tính theo xu: chia cho 60 phút * 100 xu
        $minBudget = intdiv($minutes * $rateCents + 5999, 6000);
        $hours = round($minutes / 60, 2);

        return [
            'minutes' => $minutes,
            'hours' => $hours,
            'rate_per_hour' => (int) round($rateCents / 100),
            'min_budget' => $minBudget,
            'label' => sprintf(
                'Tối thiểu %s (%s giờ × %s/giờ)',
                self::money($minBudget),
                self::hours($hours),
                self::money($rateCents / 100)
            ),
        ];
    }

    public static function money(int|float $amount): string
    {
        return number_format($amount, 0, ',', '.') . 'đ';
    }

    /** 2.5 -> "2,5", 2 -> "2", 1.3333 -> "1,33" */
    public static function hours(float $hours): string
    {
        return rtrim(rtrim(number_format($hours, 2, ',', '.'), '0'), ',');
    }
}

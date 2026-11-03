<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\KpiService;
use Illuminate\Support\Carbon;

/**
 * Doanh thu KPI = tổng budget booking 'completed' -> tính lại KPI ngay khi một booking
 * trở thành / thôi là 'completed' (hoặc booking completed đổi budget / thời gian / talent).
 */
class BookingObserver
{
    public function __construct(private KpiService $kpiService)
    {
    }

    public function created(Booking $booking): void
    {
        if ($booking->status === 'completed') {
            $this->recompute([[$booking->streamer_id, $booking->start_time]]);
        }
    }

    public function updated(Booking $booking): void
    {
        $relevant = $booking->wasChanged(['status', 'budget', 'start_time', 'streamer_id'])
            && ($booking->status === 'completed' || $booking->getOriginal('status') === 'completed');

        if ($relevant) {
            $this->recompute([
                [$booking->streamer_id, $booking->start_time],
                [$booking->getOriginal('streamer_id'), $booking->getOriginal('start_time')],
            ]);
        }
    }

    public function deleted(Booking $booking): void
    {
        if ($booking->status === 'completed') {
            $this->recompute([[$booking->streamer_id, $booking->start_time]]);
        }
    }

    /** @param array<int, array{0: int|null, 1: mixed}> $targets */
    private function recompute(array $targets): void
    {
        collect($targets)
            ->filter(fn ($t) => $t[0] && $t[1])
            ->map(fn ($t) => ['streamer_id' => (int) $t[0], 'month_year' => Carbon::parse($t[1])->format('Y-m')])
            ->unique(fn ($t) => $t['streamer_id'] . '|' . $t['month_year'])
            ->each(fn ($t) => $this->kpiService->recompute($t['streamer_id'], $t['month_year']));
    }
}

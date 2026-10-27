<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kpi;
use App\Models\Schedule;
use App\Models\StreamMetric;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_schedules_have_end_after_start(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, Schedule::count());
        Schedule::all()->each(fn (Schedule $s) => $this->assertTrue(
            $s->end_time->greaterThan($s->start_time),
            "Schedule #{$s->id} kết thúc trước khi bắt đầu"
        ));
    }

    public function test_seeded_kpi_actuals_match_seeded_metrics_and_bookings(): void
    {
        $this->seed(DatabaseSeeder::class);

        $month = now()->format('Y-m');
        $kpis = Kpi::where('month_year', $month)->get();
        $this->assertCount(10, $kpis);

        foreach ($kpis as $kpi) {
            $hours = StreamMetric::where('streamer_id', $kpi->streamer_id)
                ->whereBetween('stream_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('duration_hours');
            $revenue = Booking::where('streamer_id', $kpi->streamer_id)
                ->whereIn('status', ['approved', 'completed'])
                ->whereBetween('start_time', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('budget');

            $this->assertEquals((float) $hours, (float) $kpi->achieved_hours, "KPI giờ streamer {$kpi->streamer_id}");
            $this->assertEquals((float) $revenue, (float) $kpi->achieved_revenue, "KPI doanh thu streamer {$kpi->streamer_id}");
        }
    }

    public function test_seeded_stream_dates_are_plain_dates(): void
    {
        $this->seed(DatabaseSeeder::class);

        StreamMetric::all()->each(fn ($m) => $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/', $m->getRawOriginal('stream_date')
        ));
    }
}

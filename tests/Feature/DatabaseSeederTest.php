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
                ->where('status', 'completed')
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

    private function ratios(Kpi $kpi): array
    {
        return [
            'hours' => (float) $kpi->achieved_hours / (float) $kpi->target_hours * 100,
            'revenue' => (float) $kpi->achieved_revenue / (float) $kpi->target_revenue * 100,
        ];
    }

    public function test_seeded_kpis_cover_over_mid_and_low_achievement_bands(): void
    {
        $this->seed(DatabaseSeeder::class);
        $kpis = Kpi::where('month_year', now()->format('Y-m'))->get()->keyBy('streamer_id');

        // KillerQueen (#3): vượt chỉ tiêu cả giờ lẫn doanh thu -> achieved
        $over = $this->ratios($kpis[3]);
        $this->assertGreaterThanOrEqual(100, $over['hours']);
        $this->assertGreaterThanOrEqual(100, $over['revenue']);
        $this->assertSame('achieved', $kpis[3]->status);

        // Nhism (#4): 60-90%
        $mid = $this->ratios($kpis[4]);
        foreach ($mid as $value) {
            $this->assertGreaterThanOrEqual(60, $value);
            $this->assertLessThanOrEqual(90, $value);
        }
        $this->assertSame('in_progress', $kpis[4]->status);

        // QNT (#6): < 50%
        $low = $this->ratios($kpis[6]);
        $this->assertLessThan(50, $low['hours']);
        $this->assertLessThan(50, $low['revenue']);
        $this->assertSame('in_progress', $kpis[6]->status);
    }

    public function test_seeded_metrics_are_spread_over_many_days_of_the_month(): void
    {
        $this->seed(DatabaseSeeder::class);

        $days = StreamMetric::whereBetween('stream_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->distinct()->count('stream_date');

        $this->assertGreaterThanOrEqual(20, $days);
        $this->assertSame(
            StreamMetric::count(),
            StreamMetric::select('streamer_id', 'platform', 'stream_date')->distinct()->get()->count()
        );
    }

    public function test_seeded_active_bookings_respect_booking_rules(): void
    {
        $this->seed(DatabaseSeeder::class);
        $service = app(\App\Services\BookingCollisionService::class);

        Booking::whereIn('status', ['pending', 'reviewing'])->get()->each(function (Booking $b) use ($service) {
            $check = $service->checkCollision($b->streamer_id, $b->start_time, $b->end_time, $b->id, $b->client_id);
            $this->assertFalse($check['has_conflict'], "Booking #{$b->id}: {$check['message']}");
        });
    }

    public function test_seeder_has_completable_approved_bookings_with_locked_schedules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $completable = Booking::with('streamer')->get()->filter->isCompletable();
        $this->assertGreaterThanOrEqual(1, $completable->count());

        foreach ($completable as $b) {
            $this->assertTrue(
                Schedule::where('reference_id', $b->id)->where('event_type', 'booking')
                    ->where('start_time', $b->start_time)->where('end_time', $b->end_time)->exists(),
                "Booking #{$b->id} approved nhưng thiếu lịch khóa"
            );
            $this->assertNotNull($b->streamer->manager_id);

            // Manager phụ trách và Admin thấy nút xác nhận hoàn thành
            $manager = \App\Models\User::find($b->streamer->manager_id);
            $admin = \App\Models\User::where('role', 'admin')->first();
            foreach ([$manager, $admin] as $user) {
                $this->actingAs($user)->get(route('bookings.show', $b))
                    ->assertOk()->assertSee(route('bookings.complete', $b), false);
            }
        }
    }

    public function test_every_seeded_approved_booking_has_a_schedule(): void
    {
        $this->seed(DatabaseSeeder::class);

        Booking::where('status', 'approved')->get()->each(fn (Booking $b) => $this->assertTrue(
            Schedule::where('reference_id', $b->id)->exists(), "Booking approved #{$b->id} thiếu schedule"
        ));
    }

    public function test_seeded_bookings_and_schedules_never_overlap_per_talent(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Khoảng bận của mỗi talent: booking chiếm lịch (approved/completed) + schedule không gắn với chính booking đó
        $busy = collect();
        Booking::whereIn('status', ['approved', 'completed', 'pending', 'reviewing'])->get()
            ->each(fn ($b) => $busy->push(['streamer' => $b->streamer_id, 'start' => $b->start_time, 'end' => $b->end_time, 'key' => "booking#{$b->id}", 'ref' => $b->id]));
        Schedule::all()->each(fn ($s) => $busy->push(['streamer' => $s->streamer_id, 'start' => $s->start_time, 'end' => $s->end_time, 'key' => "schedule#{$s->id}", 'ref' => $s->reference_id]));

        foreach ($busy->groupBy('streamer') as $streamerId => $items) {
            $items = $items->values();
            for ($i = 0; $i < $items->count(); $i++) {
                for ($j = $i + 1; $j < $items->count(); $j++) {
                    [$a, $b] = [$items[$i], $items[$j]];
                    if ($a['ref'] !== null && $a['ref'] === $b['ref']) {
                        continue; // booking và lịch khóa của chính nó
                    }
                    $overlap = $a['start']->lt($b['end']) && $a['end']->gt($b['start']);
                    $this->assertFalse($overlap, "Talent #{$streamerId}: {$a['key']} trùng {$b['key']}");
                }
            }
        }
    }

    public function test_approved_bookings_are_not_counted_as_kpi_revenue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kpi = Kpi::where('streamer_id', 1)->where('month_year', now()->format('Y-m'))->first();
        $completed = Booking::where('streamer_id', 1)->where('status', 'completed')
            ->whereBetween('start_time', [now()->startOfMonth(), now()->endOfMonth()])->sum('budget');

        $this->assertEquals((float) $completed, (float) $kpi->achieved_revenue);
    }
}

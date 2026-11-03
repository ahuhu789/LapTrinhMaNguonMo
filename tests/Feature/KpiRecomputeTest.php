<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kpi;
use App\Models\StreamerProfile;
use App\Models\StreamMetric;
use App\Models\User;
use App\Services\KpiService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class KpiRecomputeTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private StreamerProfile $talent;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-03 09:00:00'));
        $this->manager = User::factory()->manager()->create();
        $this->talent = StreamerProfile::factory()->managedBy($this->manager)->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function kpi(float $hours, float $revenue): Kpi
    {
        return Kpi::create([
            'streamer_id' => $this->talent->id, 'month_year' => '2026-11',
            'target_hours' => $hours, 'target_revenue' => $revenue, 'target_avg_viewers' => 0,
            'achieved_hours' => 0, 'achieved_revenue' => 0, 'status' => 'in_progress',
        ]);
    }

    private function stream(string $date, float $hours): void
    {
        StreamMetric::create([
            'streamer_id' => $this->talent->id, 'platform' => 'youtube', 'stream_date' => $date,
            'duration_hours' => $hours, 'avg_viewers' => 100, 'peak_viewers' => 200, 'followers_gained' => 0,
        ]);
    }

    private function completed(int $budget, string $start = '2026-11-10 14:00:00'): Booking
    {
        return Booking::factory()->for($this->talent, 'streamer')
            ->between($start, Carbon::parse($start)->addHours(2)->toDateTimeString())
            ->create(['status' => 'completed', 'budget' => $budget]);
    }

    public static function cases(): array
    {
        // [giờ thực, doanh thu thực, status, % giờ thực, % doanh thu thực]
        return [
            'exactly 100% both' => [30.0, 10_000_000, 'achieved', 100, 100],
            'over 100% both' => [45.0, 15_000_000, 'achieved', 150, 150],
            'revenue 100%, hours exceeded (Mixi case)' => [33.5, 10_000_000, 'achieved', 111, 100],
            'hours short' => [29.99, 20_000_000, 'in_progress', 99, 200],
            'revenue short by 1 dong' => [40.0, 9_999_999, 'in_progress', 133, 99],
        ];
    }

    #[DataProvider('cases')]
    public function test_status_and_progress(float $hours, int $revenue, string $status, int $hPct, int $rPct): void
    {
        $this->kpi(30, 10_000_000);
        $this->stream('2026-11-02', $hours);
        $this->completed($revenue);

        $kpi = app(KpiService::class)->recompute($this->talent->id, '2026-11');

        $this->assertSame($status, $kpi->status);
        $this->assertSame($hPct, $kpi->hours_progress_raw);
        $this->assertSame($rPct, $kpi->revenue_progress_raw);
        $this->assertSame(min(100, $hPct), $kpi->hours_progress_percentage);
        $this->assertSame(min(100, $rPct), $kpi->revenue_progress_percentage);
    }

    public function test_only_completed_bookings_in_month_count_as_revenue(): void
    {
        $this->kpi(1, 1_000_000);
        $this->completed(1_000_000);
        $this->completed(5_000_000, '2026-12-01 00:00:00'); // tháng sau
        foreach (['approved', 'pending', 'rejected', 'cancelled'] as $st) {
            Booking::factory()->for($this->talent, 'streamer')
                ->between('2026-11-12 10:00:00', '2026-11-12 11:00:00')->create(['status' => $st, 'budget' => 9_000_000]);
        }

        $kpi = app(KpiService::class)->recompute($this->talent->id, '2026-11');
        $this->assertEquals(1_000_000, (float) $kpi->achieved_revenue);
    }

    public function test_updating_targets_recomputes_status_immediately(): void
    {
        // Lỗi cũ: status chỉ được tính khi import CSV -> đổi chỉ tiêu xong vẫn 'in_progress'
        $this->kpi(90, 100_000_000);
        $this->stream('2026-11-02', 72);
        $this->completed(60_000_000);
        app(KpiService::class)->recompute($this->talent->id, '2026-11');
        $this->assertSame('in_progress', Kpi::first()->status);

        $this->actingAs($this->manager)->post(route('manager.kpis.store'), [
            'streamer_id' => $this->talent->id, 'month_year' => '2026-11',
            'target_hours' => 70, 'target_revenue' => 60_000_000, 'target_avg_viewers' => 0,
        ])->assertSessionHas('success');

        $kpi = Kpi::first();
        $this->assertSame('achieved', $kpi->status);
        $this->assertSame(102, $kpi->hours_progress_raw);
        $this->assertSame(100, $kpi->revenue_progress_raw);
        $this->assertSame('Hoàn thành', $kpi->status_label);
    }

    public function test_new_kpi_created_via_form_gets_actuals(): void
    {
        $this->stream('2026-11-02', 10);
        $this->actingAs($this->manager)->post(route('manager.kpis.store'), [
            'streamer_id' => $this->talent->id, 'month_year' => '2026-11',
            'target_hours' => 10, 'target_revenue' => 0, 'target_avg_viewers' => 0,
        ]);

        $this->assertSame('achieved', Kpi::first()->status);
        $this->assertEquals(10, (float) Kpi::first()->achieved_hours);
    }

    public function test_booking_completion_recomputes_kpi(): void
    {
        $this->kpi(10, 5_000_000);
        $this->stream('2026-11-02', 12);
        $booking = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-10 14:00:00', '2026-11-10 16:00:00')->approved()->create(['budget' => 5_000_000]);

        $this->assertNotSame('achieved', Kpi::first()->fresh()->status);

        $booking->update(['status' => 'completed']);
        $kpi = Kpi::first();
        $this->assertSame('achieved', $kpi->status);
        $this->assertEquals(5_000_000, (float) $kpi->achieved_revenue);

        // hủy hoàn thành -> tính lại
        $booking->update(['status' => 'cancelled']);
        $this->assertSame('in_progress', Kpi::first()->status);
        $this->assertEquals(0, (float) Kpi::first()->achieved_revenue);
    }

    public function test_seed_has_kpis_over_100_percent_with_consistent_status(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kpis = Kpi::all();
        $this->assertTrue($kpis->contains(fn (Kpi $k) => $k->hours_progress_raw > 100 || $k->revenue_progress_raw > 100));
        $this->assertTrue($kpis->contains(fn (Kpi $k) => $k->status === 'achieved'));

        foreach ($kpis as $k) {
            $expected = $k->hours_progress_raw >= 100 && $k->revenue_progress_raw >= 100 ? 'achieved' : 'in_progress';
            $this->assertSame($expected, $k->status, "KPI streamer {$k->streamer_id}");
        }
    }
}

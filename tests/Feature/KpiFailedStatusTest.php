<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kpi;
use App\Models\StreamerProfile;
use App\Models\User;
use App\Services\KpiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KpiFailedStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private StreamerProfile $talent;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-17 09:00:00'));
        $this->manager = $this->makeUser('manager');
        $this->talent = $this->makeStreamer($this->manager);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function kpi(string $month, float $targetRevenue = 5_000_000, string $status = 'in_progress'): Kpi
    {
        return Kpi::create([
            'streamer_id' => $this->talent->id, 'month_year' => $month,
            'target_hours' => 0, 'target_revenue' => $targetRevenue, 'target_avg_viewers' => 0, 'status' => $status,
        ]);
    }

    private function completed(string $start, int $budget): void
    {
        Booking::factory()->for($this->talent, 'streamer')
            ->between($start, Carbon::parse($start)->addHours(2)->toDateTimeString())
            ->create(['status' => 'completed', 'budget' => $budget]);
    }

    public function test_ended_month_not_achieved_is_failed(): void
    {
        $this->kpi('2026-10');
        $this->completed('2026-10-05 10:00:00', 1_000_000);

        $kpi = app(KpiService::class)->recompute($this->talent->id, '2026-10');

        $this->assertSame('failed', $kpi->status);
        $this->assertSame('Không đạt', $kpi->status_label);
    }

    public function test_ended_month_achieved_stays_achieved(): void
    {
        $this->kpi('2026-10');
        $this->completed('2026-10-05 10:00:00', 5_000_000);

        $kpi = app(KpiService::class)->recompute($this->talent->id, '2026-10');

        $this->assertSame('achieved', $kpi->status);
        $this->assertSame('Hoàn thành', $kpi->status_label);
    }

    public function test_current_month_not_achieved_is_in_progress(): void
    {
        $this->kpi('2026-11');

        $kpi = app(KpiService::class)->recompute($this->talent->id, '2026-11');

        $this->assertSame('in_progress', $kpi->status);
        $this->assertSame('Đang thực hiện', $kpi->status_label);
    }

    public function test_month_boundary_uses_app_timezone(): void
    {
        $this->kpi('2026-11');

        Carbon::setTestNow(Carbon::parse('2026-11-30 23:59:59'));
        $this->assertSame('in_progress', app(KpiService::class)->recompute($this->talent->id, '2026-11')->status);

        Carbon::setTestNow(Carbon::parse('2026-12-01 00:00:00'));
        $this->assertSame('failed', app(KpiService::class)->recompute($this->talent->id, '2026-11')->status);
    }

    public function test_stale_in_progress_kpis_are_closed_when_opening_kpi_page(): void
    {
        $old = $this->kpi('2026-09');
        $current = $this->kpi('2026-11');

        $this->actingAs($this->manager)->get(route('manager.kpis', ['month' => '2026-09']))
            ->assertOk()->assertSee('Không đạt');

        $this->assertSame('failed', $old->fresh()->status);
        $this->assertSame('in_progress', $current->fresh()->status);
    }

    public function test_close_months_command(): void
    {
        $old = $this->kpi('2026-08');

        $this->artisan('kpi:close-months')->assertSuccessful();

        $this->assertSame('failed', $old->fresh()->status);
    }

    public function test_completing_late_booking_can_turn_failed_month_into_achieved(): void
    {
        $kpi = $this->kpi('2026-10', 2_000_000, 'failed');
        $this->completed('2026-10-20 10:00:00', 2_000_000);

        $this->assertSame('achieved', $kpi->fresh()->status);
    }
}

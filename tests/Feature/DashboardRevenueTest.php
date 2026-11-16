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

class DashboardRevenueTest extends TestCase
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

        foreach ([
            ['completed', '2026-11-05 10:00:00', 3_000_000],
            ['completed', '2026-11-30 23:00:00', 1_000_000],  // vẫn thuộc tháng 11 (giờ VN)
            ['completed', '2026-10-31 23:00:00', 4_000_000],  // tháng trước
            ['approved', '2026-11-25 10:00:00', 2_000_000],
            ['pending', '2026-11-26 10:00:00', 9_000_000],
            ['cancelled', '2026-11-27 10:00:00', 8_000_000],
        ] as [$status, $start, $budget]) {
            Booking::factory()->for($this->talent, 'streamer')
                ->between($start, Carbon::parse($start)->addMinutes(30)->toDateTimeString())
                ->create(['status' => $status, 'budget' => $budget, 'commission_rate' => 10]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_dashboard_real_revenue_is_completed_only_and_projected_is_approved(): void
    {
        $stats = $this->actingAs($this->makeUser('admin'))->get(route('admin.dashboard'))
            ->assertOk()->viewData('stats');

        $this->assertEquals(8_000_000, $stats['total_revenue']);
        $this->assertEquals(800_000, $stats['total_commission']);
        $this->assertEquals(2_000_000, $stats['projected_revenue']);
        $this->assertEquals(200_000, $stats['projected_commission']);
    }

    public function test_manager_team_revenue_matches_kpi_achieved_revenue(): void
    {
        Kpi::create([
            'streamer_id' => $this->talent->id, 'month_year' => '2026-11',
            'target_hours' => 0, 'target_revenue' => 10_000_000, 'target_avg_viewers' => 0,
        ]);
        app(KpiService::class)->recompute($this->talent->id, '2026-11');

        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'))->assertOk();

        $this->assertEquals(4_000_000, $response->viewData('teamRevenue'));
        $this->assertEquals(2_000_000, $response->viewData('teamProjectedRevenue'));

        $kpi = Kpi::where('streamer_id', $this->talent->id)->where('month_year', '2026-11')->first();
        $this->assertEquals((float) $kpi->achieved_revenue, $response->viewData('teamRevenue'));
    }
}

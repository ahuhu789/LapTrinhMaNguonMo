<?php

namespace Tests\Feature;

use App\Models\Kpi;
use App\Models\StreamMetric;
use App\Services\CsvMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAndKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->from(route('admin.users'))
            ->post(route('admin.users.role', $admin), ['role' => 'client'])
            ->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_admin_can_change_other_users_role(): void
    {
        $admin = $this->makeUser('admin');
        $client = $this->makeUser('client');

        $this->actingAs($admin)
            ->post(route('admin.users.role', $client), ['role' => 'manager'])
            ->assertSessionHas('success');

        $this->assertSame('manager', $client->fresh()->role);
    }

    public function test_streamer_can_only_be_assigned_to_a_manager(): void
    {
        $admin = $this->makeUser('admin');
        $client = $this->makeUser('client');
        $manager = $this->makeUser('manager');
        $streamer = $this->makeStreamer();

        $this->actingAs($admin)
            ->post(route('admin.assign.update', $streamer), ['manager_id' => $client->id])
            ->assertSessionHasErrors('manager_id');
        $this->assertNull($streamer->fresh()->manager_id);

        $this->actingAs($admin)
            ->post(route('admin.assign.update', $streamer), ['manager_id' => $manager->id])
            ->assertSessionHas('success');
        $this->assertSame($manager->id, $streamer->fresh()->manager_id);
    }

    public function test_manager_can_only_set_kpi_for_own_streamers(): void
    {
        $manager = $this->makeUser('manager');
        $own = $this->makeStreamer($manager);
        $foreign = $this->makeStreamer($this->makeUser('manager'));

        $payload = fn ($s) => [
            'streamer_id' => $s->id,
            'month_year' => now()->format('Y-m'),
            'target_hours' => 40,
            'target_revenue' => 1000000,
            'target_avg_viewers' => 500,
        ];

        $this->actingAs($manager)->post(route('manager.kpis.store'), $payload($foreign))->assertForbidden();
        $this->actingAs($manager)->post(route('manager.kpis.store'), $payload($own))->assertSessionHas('success');

        $this->assertSame(1, Kpi::count());
        $this->assertSame($own->id, Kpi::first()->streamer_id);
    }

    public function test_csv_import_only_accepts_allowed_streamers(): void
    {
        $manager = $this->makeUser('manager');
        $own = $this->makeStreamer($manager);
        $foreign = $this->makeStreamer();

        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, implode("\n", [
            'streamer_id,platform,stream_date,duration_hours,avg_viewers,peak_viewers,followers_gained',
            "{$own->id},youtube,2026-10-01,3,1000,2000,10",
            "{$foreign->id},youtube,2026-10-01,3,1000,2000,10",
        ]));

        $result = app(CsvMetricsService::class)->importCsv($path, [$own->id]);
        @unlink($path);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['imported_count']);
        $this->assertCount(1, $result['errors']);
        $this->assertSame([$own->id], StreamMetric::pluck('streamer_id')->all());
    }

    public function test_manager_cannot_access_admin_area(): void
    {
        $this->actingAs($this->makeUser('manager'))->get(route('admin.users'))->assertForbidden();
    }

    public function test_csv_reimport_updates_existing_metric_instead_of_duplicating(): void
    {
        $streamer = $this->makeStreamer();
        $service = app(CsvMetricsService::class);
        $header = 'streamer_id,platform,stream_date,duration_hours,avg_viewers,peak_viewers,followers_gained';

        foreach ([1000, 1500] as $avg) {
            $path = tempnam(sys_get_temp_dir(), 'csv');
            file_put_contents($path, $header . "\n{$streamer->id},twitch,2026-10-05,2,{$avg},3000,5");
            $result = $service->importCsv($path, [$streamer->id]);
            @unlink($path);
            $this->assertTrue($result['success']);
        }

        $this->assertSame(1, $result['updated_count']);
        $this->assertSame(1, StreamMetric::count());
        $this->assertSame(1500, StreamMetric::first()->avg_viewers);
        $this->assertSame('2026-10-05', StreamMetric::first()->getRawOriginal('stream_date'));
    }

    public function test_manager_dashboard_renders_without_metrics_eager_load(): void
    {
        $manager = $this->makeUser('manager');
        $streamer = $this->makeStreamer($manager);

        $managed = $this->actingAs($manager)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee($streamer->stage_name)
            ->viewData('managedStreamers');

        $this->assertFalse($managed->first()->relationLoaded('metrics'));
        $this->assertTrue($managed->first()->relationLoaded('currentKpi'));
    }
}

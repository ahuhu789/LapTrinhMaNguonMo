<?php

namespace Tests\Feature;

use App\Models\StreamerProfile;
use App\Models\StreamMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RecentMetricsChartTest extends TestCase
{
    use RefreshDatabase;

    private function seedStreams(StreamerProfile $talent, int $days): void
    {
        // chèn theo thứ tự ngẫu nhiên để không phụ thuộc id
        foreach (collect(range(1, $days))->shuffle() as $d) {
            StreamMetric::create([
                'streamer_id' => $talent->id, 'platform' => 'youtube',
                'stream_date' => Carbon::parse('2026-10-01')->addDays($d - 1)->toDateString(),
                'duration_hours' => 2, 'avg_viewers' => 100 + $d, 'peak_viewers' => 200, 'followers_gained' => 0,
            ]);
        }
    }

    private function dates($metrics): array
    {
        return $metrics->map(fn ($m) => Carbon::parse($m->stream_date)->toDateString())->all();
    }

    public function test_media_kit_chart_uses_10_most_recent_streams_in_ascending_order(): void
    {
        $talent = $this->makeStreamer();
        $this->seedStreams($talent, 15);

        $metrics = $this->get(route('talents.show', $talent->id))->assertOk()->viewData('recentMetrics');

        $expected = collect(range(6, 15))->map(fn ($d) => Carbon::parse('2026-10-01')->addDays($d - 1)->toDateString())->all();
        $this->assertSame($expected, $this->dates($metrics));
    }

    public function test_streamer_dashboard_chart_uses_14_most_recent_streams_in_ascending_order(): void
    {
        $talent = $this->makeStreamer();
        $this->seedStreams($talent, 20);

        $metrics = $this->actingAs($talent->user)->get(route('streamer.dashboard'))->assertOk()->viewData('chartMetrics');

        $this->assertCount(14, $metrics);
        $this->assertSame('2026-10-07', $this->dates($metrics)[0]);
        $this->assertSame('2026-10-20', $this->dates($metrics)[13]);
    }
}

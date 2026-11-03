<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\StreamMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TalentListingPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function seedTalents(int $count): array
    {
        $profiles = [];
        for ($i = 1; $i <= $count; $i++) {
            $p = $this->makeStreamer(null, ['rate_per_hour' => 500000 + $i * 100000]);
            foreach (['2026-10-01' => $i * 100, '2026-10-02' => $i * 300] as $date => $avg) {
                StreamMetric::create([
                    'streamer_id' => $p->id,
                    'platform' => 'youtube',
                    'stream_date' => $date,
                    'duration_hours' => 2,
                    'avg_viewers' => $avg,
                    'peak_viewers' => $avg * 2,
                ]);
            }
            $profiles[] = $p;
        }

        return $profiles;
    }

    public function test_talents_index_is_paginated_and_sorted_in_sql(): void
    {
        $profiles = $this->seedTalents(15);

        $response = $this->get(route('talents.index'))->assertOk();
        $talents = $response->viewData('talents');

        $this->assertInstanceOf(LengthAwarePaginator::class, $talents);
        $this->assertSame(12, $talents->perPage());
        $this->assertSame(15, $talents->total());
        // Streamer có lượt xem cao nhất đứng đầu: avg = (15*100 + 15*300)/2 = 3000, peak = 9000
        $this->assertSame(end($profiles)->id, $talents->first()->id);
        $this->assertEquals(3000, $talents->first()->avg_viewers_calc);
        $this->assertSame(9000, $talents->first()->peak_viewers_calc);
        $this->assertSame(3000, $talents->first()->avg_viewers);

        $page = $this->get(route('talents.index', ['sort' => 'rate_asc', 'page' => 2]))->viewData('talents');
        $this->assertCount(3, $page);
        $this->assertStringContainsString('sort=rate_asc', $page->nextPageUrl() ?? $page->url(1));
    }

    public function test_listing_query_count_does_not_grow_with_talents(): void
    {
        $this->seedTalents(12);

        DB::enableQueryLog();
        $this->get(route('talents.index'))->assertOk();
        $talentsQueries = count(DB::getQueryLog());
        DB::flushQueryLog();
        $this->get(route('home'))->assertOk();
        $homeQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Trước đây: 2 query / streamer (avg + peak). Giờ phải là hằng số nhỏ.
        $this->assertLessThan(10, $talentsQueries, "talents.index ran $talentsQueries queries");
        $this->assertLessThan(12, $homeQueries, "home ran $homeQueries queries");
    }

    public function test_home_featured_talents_are_top_six_by_avg_viewers(): void
    {
        $profiles = $this->seedTalents(8);

        $featured = $this->get(route('home'))->assertOk()->viewData('featuredTalents');

        $this->assertCount(6, $featured);
        $this->assertSame(end($profiles)->id, $featured->first()->id);
        $this->assertNotNull($featured->first()->avg_viewers_calc);
    }

    public function test_talent_show_still_renders(): void
    {
        [$p] = $this->seedTalents(1);

        $this->get(route('talents.show', $p->id))->assertOk()->assertSee('300');
    }

    public function test_admin_total_commission_is_computed_in_sql(): void
    {
        $streamer = $this->makeStreamer();
        $client = $this->makeUser('client');
        foreach ([['approved', 1000000, 15], ['completed', 2000000, 10], ['pending', 9000000, 15]] as [$status, $budget, $rate]) {
            Booking::create([
                'client_id' => $client->id,
                'streamer_id' => $streamer->id,
                'start_time' => now()->addDay(),
                'end_time' => now()->addDay()->addHour(),
                'job_description' => 'Livestream test',
                'budget' => $budget,
                'commission_rate' => $rate,
                'status' => $status,
            ]);
        }

        $stats = $this->actingAs($this->makeUser('admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->viewData('stats');

        $this->assertEquals(350000, $stats['total_commission']); // 150k + 200k
    }
}

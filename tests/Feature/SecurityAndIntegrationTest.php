<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\StreamerProfile;
use App\Models\User;
use App\Services\AiRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityAndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookings_show_idor_matrix(): void
    {
        $manager = User::factory()->manager()->create();
        $booking = Booking::factory()
            ->for(StreamerProfile::factory()->managedBy($manager), 'streamer')
            ->create();

        $outsiders = [
            User::factory()->client()->create(),
            User::factory()->manager()->create(),
            StreamerProfile::factory()->create()->user, // streamer khác
        ];

        foreach ($outsiders as $user) {
            $this->actingAs($user)
                ->get(route('bookings.show', $booking))
                ->assertForbidden()
                ->assertSee('Bạn không có quyền truy cập hợp đồng booking này.');
        }

        $insiders = [
            $booking->client,
            $manager,
            $booking->streamer->user,
            User::factory()->admin()->create(),
        ];

        foreach ($insiders as $user) {
            $this->actingAs($user)->get(route('bookings.show', $booking))->assertOk();
        }
    }

    public function test_guest_is_redirected_to_login_for_booking_pages(): void
    {
        $booking = Booking::factory()->create();

        $this->get(route('bookings.show', $booking))->assertRedirect(route('login'));
    }

    public function test_register_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('register.post'), [])->assertRedirect();
        }

        $this->post(route('register.post'), [])->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_successful_login_redirects_by_role(): void
    {
        $manager = User::factory()->manager()->create(['email' => 'm@test.local']);

        $this->post(route('login.post'), ['email' => 'm@test.local', 'password' => 'password123'])
            ->assertRedirect(route('manager.dashboard'));

        $this->assertAuthenticatedAs($manager);
    }

    public function test_client_cannot_delete_while_having_bookings_restrict_fk(): void
    {
        $booking = Booking::factory()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);
        $booking->client->delete();
    }

    public function test_streamer_without_profile_cannot_add_schedule_event(): void
    {
        $this->actingAs(User::factory()->streamer()->create())
            ->post(route('streamer.schedule.store'), [
                'event_type' => 'stream',
                'start_time' => now()->addDay()->toDateTimeString(),
                'end_time' => now()->addDay()->addHour()->toDateTimeString(),
                'title' => 'Stream',
            ])
            ->assertForbidden();
    }

    public function test_profile_category_must_be_from_config(): void
    {
        $profile = StreamerProfile::factory()->create();

        $this->actingAs($profile->user)
            ->post(route('streamer.profile.update'), [
                'stage_name' => 'New Name',
                'category' => 'Hacking',
                'rate_per_hour' => 600000,
            ])
            ->assertSessionHasErrors(['category' => 'Thể loại không hợp lệ.']);
    }

    public function test_profile_update_accepts_integer_rate_and_relative_image_paths(): void
    {
        $profile = StreamerProfile::factory()->create(['rate_per_hour' => 5000000]);

        $this->actingAs($profile->user)->get(route('streamer.profile'))
            ->assertOk()
            ->assertSee('value="5000000"', false)
            ->assertDontSee('5000000.00');

        $this->actingAs($profile->user)
            ->post(route('streamer.profile.update'), [
                'stage_name' => 'New Name',
                'category' => config('mcn.categories')[0],
                'rate_per_hour' => '5500000',
                'avatar_url' => '/images/streamers/domixi.jpg',
                'banner_url' => 'https://example.com/banner.jpg',
            ])
            ->assertSessionHasNoErrors();

        $this->assertEquals(5500000, (float) $profile->fresh()->rate_per_hour);
        $this->assertSame('/images/streamers/domixi.jpg', $profile->fresh()->avatar_url);

        $this->actingAs($profile->user)
            ->post(route('streamer.profile.update'), [
                'stage_name' => 'New Name',
                'category' => config('mcn.categories')[0],
                'rate_per_hour' => 600000,
                'avatar_url' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('avatar_url');
    }

    public function test_gemini_key_is_sent_as_header_not_query_string(): void
    {
        config([
            'services.ai.gemini_key' => 'test-gemini-key',
            'services.ai.openai_key' => '',
            'services.ai.gemini_models' => ['gemini-test-model'],
        ]);
        StreamerProfile::factory()->create(['stage_name' => 'DoMixi']);

        Http::fake([
            '*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Gợi ý: DoMixi']]]]]]),
        ]);

        $result = app(AiRecommendationService::class)->recommendTalents('Tìm streamer gaming');

        $this->assertSame('gemini_ai', $result['source']);
        $this->assertSame('gemini-test-model', $result['model']);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-goog-api-key', 'test-gemini-key')
                && !str_contains($request->url(), 'key=')
                && str_ends_with($request->url(), '/gemini-test-model:generateContent');
        });
    }

    public function test_ai_falls_back_to_rule_based_when_provider_fails(): void
    {
        config(['services.ai.gemini_key' => 'k', 'services.ai.openai_key' => '', 'services.ai.gemini_models' => ['m1']]);
        StreamerProfile::factory()->count(2)->create();
        Http::fake(['*' => Http::response('boom', 500)]);

        $this->postJson(route('ai.recommend'), ['message' => 'streamer review công nghệ'])
            ->assertOk()
            ->assertJsonPath('source', 'rule_based_fallback');
    }

    public function test_throttle_works_with_default_database_cache_store(): void
    {
        config(['cache.default' => 'database']);

        $this->postJson(route('ai.recommend'), [])->assertStatus(422);

        $this->assertGreaterThan(0, \DB::table('cache')->count()); // limiter counter + timer
    }
}

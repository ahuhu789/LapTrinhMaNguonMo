<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeBooking($streamer, $client, array $attrs = []): Booking
    {
        return Booking::create(array_merge([
            'client_id' => $client->id,
            'streamer_id' => $streamer->id,
            'manager_id' => $streamer->manager_id,
            'start_time' => now()->addDays(2)->setTime(14, 0),
            'end_time' => now()->addDays(2)->setTime(16, 0),
            'job_description' => 'Livestream review sản phẩm',
            'budget' => 2000000,
            'commission_rate' => 15,
            'status' => 'pending',
        ], $attrs));
    }

    public function test_view_policy_allows_owner_parties_and_denies_others(): void
    {
        $manager = $this->makeUser('manager');
        $otherManager = $this->makeUser('manager');
        $streamer = $this->makeStreamer($manager);
        $client = $this->makeUser('client');
        $otherClient = $this->makeUser('client');
        $booking = $this->makeBooking($streamer, $client);

        $this->actingAs($client)->get(route('bookings.show', $booking))->assertOk();
        $this->actingAs($manager)->get(route('bookings.show', $booking))->assertOk();
        $this->actingAs($streamer->user)->get(route('bookings.show', $booking))->assertOk();
        $this->actingAs($this->makeUser('admin'))->get(route('bookings.show', $booking))->assertOk();

        $this->actingAs($otherClient)->get(route('bookings.show', $booking))->assertForbidden();
        $this->actingAs($otherManager)->get(route('bookings.show', $booking))->assertForbidden();
    }

    public function test_missing_booking_returns_404_via_route_model_binding(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/bookings/999999')->assertNotFound();
    }

    public function test_only_responsible_manager_can_approve(): void
    {
        $manager = $this->makeUser('manager');
        $otherManager = $this->makeUser('manager');
        $streamer = $this->makeStreamer($manager);
        $booking = $this->makeBooking($streamer, $this->makeUser('client'));

        $this->actingAs($otherManager)->post(route('bookings.approve', $booking))->assertForbidden();
        $this->assertSame('pending', $booking->fresh()->status);

        $this->actingAs($manager)->post(route('bookings.approve', $booking))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('approved', $booking->fresh()->status);
        $this->assertTrue(Schedule::where('reference_id', $booking->id)->exists());
    }

    public function test_reject_requires_authorization_and_reason(): void
    {
        $manager = $this->makeUser('manager');
        $streamer = $this->makeStreamer($manager);
        $booking = $this->makeBooking($streamer, $this->makeUser('client'));

        $this->actingAs($this->makeUser('manager'))
            ->post(route('bookings.reject', $booking), ['reject_reason' => 'Không phù hợp'])
            ->assertForbidden();

        $this->actingAs($manager)
            ->post(route('bookings.reject', $booking), ['reject_reason' => ''])
            ->assertSessionHasErrors('reject_reason');

        $this->actingAs($manager)
            ->post(route('bookings.reject', $booking), ['reject_reason' => 'Lịch không phù hợp'])
            ->assertSessionHas('success');

        $this->assertSame('rejected', $booking->fresh()->status);
    }

    public function test_check_collision_requires_auth_validates_and_returns_only_flag(): void
    {
        $streamer = $this->makeStreamer();
        $client = $this->makeUser('client');
        $start = now()->addDay()->setTime(10, 0);

        Schedule::create([
            'streamer_id' => $streamer->id,
            'event_type' => 'stream',
            'start_time' => $start,
            'end_time' => $start->copy()->addHours(2),
            'title' => 'Stream tối',
        ]);

        $this->postJson(route('bookings.check-collision'), [])->assertUnauthorized();

        // 422 JSON kể cả khi không gửi header Accept: application/json
        $this->actingAs($client)
            ->post(route('bookings.check-collision'), [
                'streamer_id' => 999999,
                'start_time' => $start->toDateTimeString(),
                'end_time' => $start->copy()->subHour()->toDateTimeString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['streamer_id', 'end_time']);

        $this->actingAs($client)
            ->postJson(route('bookings.check-collision'), [
                'streamer_id' => $streamer->id,
                'start_time' => $start->copy()->addHour()->toDateTimeString(),
                'end_time' => $start->copy()->addHours(3)->toDateTimeString(),
            ])
            ->assertOk()
            ->assertExactJson(['has_conflict' => true]);

        $this->actingAs($client)
            ->postJson(route('bookings.check-collision'), [
                'streamer_id' => $streamer->id,
                'start_time' => $start->copy()->addHours(5)->toDateTimeString(),
                'end_time' => $start->copy()->addHours(6)->toDateTimeString(),
            ])
            ->assertOk()
            ->assertExactJson(['has_conflict' => false]);
    }

    public function test_legacy_api_routes_are_gone(): void
    {
        $this->postJson('/api/check-collision')->assertNotFound();
        $this->postJson('/api/ai-recommend')->assertNotFound();
        $this->getJson('/api/user')->assertNotFound();
    }

    public function test_login_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.post'), ['email' => 'x@test.local', 'password' => 'wrong'])
                ->assertRedirect();
        }

        $this->post(route('login.post'), ['email' => 'x@test.local', 'password' => 'wrong'])
            ->assertStatus(429);
    }
}

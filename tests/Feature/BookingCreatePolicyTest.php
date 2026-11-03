<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingCreatePolicyTest extends TestCase
{
    use RefreshDatabase;

    private StreamerProfile $talent;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-03 09:00:00'));
        $this->talent = StreamerProfile::factory()->create(['rate_per_hour' => 500000]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function payload(): array
    {
        return [
            'streamer_id' => $this->talent->id,
            'start_time' => '2026-11-05 14:00',
            'end_time' => '2026-11-05 16:00',
            'job_description' => 'Livestream giới thiệu sản phẩm mới',
            'budget' => 1_000_000,
        ];
    }

    public static function nonClientRoles(): array
    {
        return [['streamer'], ['manager'], ['admin']];
    }

    #[DataProvider('nonClientRoles')]
    public function test_non_clients_get_403_on_create_store_and_check_collision(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get(route('bookings.create'))->assertForbidden();
        $this->actingAs($user)->post(route('bookings.store'), $this->payload())->assertForbidden();
        $this->actingAs($user)->post(route('bookings.check-collision'), $this->payload())
            ->assertForbidden()
            ->assertExactJson(['message' => 'Chỉ tài khoản Khách hàng (client) mới có thể đặt booking.']);

        $this->assertSame(0, Booking::count());
        $this->assertFalse($user->can('create', Booking::class));
    }

    public function test_client_can_create(): void
    {
        $client = User::factory()->client()->create();

        $this->actingAs($client)->get(route('bookings.create'))->assertOk()->assertViewHas('canBook', true);
        $this->actingAs($client)->post(route('bookings.store'), $this->payload())->assertSessionHasNoErrors();
        $this->assertSame(1, Booking::count());
    }

    public function test_can_book_is_shared_with_views(): void
    {
        $this->get(route('talents.show', $this->talent->id))->assertOk()->assertViewHas('canBook', false);
        $this->actingAs(User::factory()->create(['role' => 'manager']))
            ->get(route('talents.show', $this->talent->id))->assertViewHas('canBook', false);
        $this->actingAs(User::factory()->client()->create())
            ->get(route('talents.show', $this->talent->id))->assertViewHas('canBook', true);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('bookings.create'))->assertRedirect(route('login'));
    }
}

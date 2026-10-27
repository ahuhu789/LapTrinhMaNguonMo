<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApprovalConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_is_refused_when_schedule_conflicts(): void
    {
        $manager = User::factory()->manager()->create();
        $streamer = StreamerProfile::factory()->managedBy($manager)->create();
        $start = now()->addDays(3)->setTime(14, 0);

        Schedule::create([
            'streamer_id' => $streamer->id,
            'event_type' => 'training',
            'start_time' => $start->copy()->addHour(),
            'end_time' => $start->copy()->addHours(3),
            'title' => 'Họp team',
        ]);

        $booking = Booking::factory()->for($streamer, 'streamer')
            ->between($start, $start->copy()->addHours(2))
            ->create();

        $this->actingAs($manager)
            ->from(route('bookings.show', $booking))
            ->post(route('bookings.approve', $booking))
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('error', fn ($msg) => str_starts_with($msg, 'Không thể phê duyệt!'))
            ->assertSessionMissing('success');

        $this->assertSame('pending', $booking->fresh()->status);
        $this->assertFalse(Schedule::where('reference_id', $booking->id)->exists());
    }

    public function test_second_overlapping_booking_cannot_be_approved_after_first(): void
    {
        $manager = User::factory()->manager()->create();
        $streamer = StreamerProfile::factory()->managedBy($manager)->create();
        $start = now()->addDays(5)->setTime(9, 0);

        $first = Booking::factory()->for($streamer, 'streamer')->between($start, $start->copy()->addHours(3))->create();
        $second = Booking::factory()->for($streamer, 'streamer')->between($start->copy()->addHour(), $start->copy()->addHours(4))->create();

        $this->actingAs($manager)->post(route('bookings.approve', $first))->assertSessionHas('success');
        $this->actingAs($manager)->post(route('bookings.approve', $second))->assertSessionHas('error');

        $this->assertSame('approved', $first->fresh()->status);
        $this->assertSame('pending', $second->fresh()->status);
        $this->assertSame(1, Schedule::where('streamer_id', $streamer->id)->count());
    }

    public function test_already_processed_booking_cannot_be_approved_again(): void
    {
        $admin = User::factory()->admin()->create();
        $booking = Booking::factory()->approved()->create();

        $this->actingAs($admin)
            ->post(route('bookings.approve', $booking))
            ->assertSessionHas('error', fn ($msg) => str_contains($msg, 'đã được xử lý từ trước'));
    }

    public function test_admin_approval_sets_manager_to_admin(): void
    {
        // Hành vi hiện tại được giữ nguyên: người duyệt được ghi vào manager_id
        $admin = User::factory()->admin()->create();
        $booking = Booking::factory()->create();

        $this->actingAs($admin)->post(route('bookings.approve', $booking))->assertSessionHas('success');

        $this->assertSame($admin->id, $booking->fresh()->manager_id);
    }
}

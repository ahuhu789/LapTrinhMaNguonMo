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

    public function test_approve_auto_rejects_booking_when_schedule_conflicts(): void
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
            ->assertSessionHas('error', fn ($msg) => str_starts_with($msg, 'Không thể phê duyệt!')
                && str_contains($msg, 'tự động từ chối'))
            ->assertSessionMissing('success');

        $fresh = $booking->fresh();
        $this->assertSame('rejected', $fresh->status);
        $this->assertStringContainsString('Trùng với lịch bận của talent', $fresh->reject_reason);
        $this->assertSame($fresh->reject_reason, $fresh->rejection_reason);
        $this->assertFalse(Schedule::where('reference_id', $booking->id)->exists());
    }

    public function test_approving_first_booking_auto_rejects_overlapping_second(): void
    {
        $manager = User::factory()->manager()->create();
        $streamer = StreamerProfile::factory()->managedBy($manager)->create();
        $start = now()->addDays(5)->setTime(9, 0);

        $first = Booking::factory()->for($streamer, 'streamer')->between($start, $start->copy()->addHours(3))->create();
        $second = Booking::factory()->for($streamer, 'streamer')->between($start->copy()->addHour(), $start->copy()->addHours(4))->create();

        $this->actingAs($manager)->post(route('bookings.approve', $first))
            ->assertSessionHas('success', fn ($msg) => str_contains($msg, 'Đã tự động từ chối 1 booking'));
        $this->actingAs($manager)->post(route('bookings.approve', $second))
            ->assertSessionHas('error', fn ($msg) => str_contains($msg, 'đã được xử lý từ trước'));

        $this->assertSame('approved', $first->fresh()->status);
        $this->assertSame('rejected', $second->fresh()->status);
        $this->assertSame('Xung đột với lịch đã được duyệt', $second->fresh()->reject_reason);
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

    public static function nonRejectableStatusProvider(): array
    {
        return [['approved'], ['rejected'], ['completed'], ['cancelled'], ['reviewing']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nonRejectableStatusProvider')]
    public function test_only_pending_bookings_can_be_rejected(string $status): void
    {
        $manager = User::factory()->manager()->create();
        $booking = Booking::factory()
            ->for(StreamerProfile::factory()->managedBy($manager), 'streamer')
            ->create(['status' => $status, 'reject_reason' => null]);

        $this->actingAs($manager)
            ->from(route('bookings.show', $booking))
            ->post(route('bookings.reject', $booking), ['reject_reason' => 'Đổi kế hoạch truyền thông'])
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('error', fn ($msg) => str_starts_with($msg, 'Không thể từ chối! Chỉ booking đang chờ duyệt'))
            ->assertSessionMissing('success');

        $fresh = $booking->fresh();
        $this->assertSame($status, $fresh->status);
        $this->assertNull($fresh->reject_reason);
    }

    public function test_approved_booking_keeps_its_schedule_when_reject_is_attempted(): void
    {
        $manager = User::factory()->manager()->create();
        $booking = Booking::factory()->for(StreamerProfile::factory()->managedBy($manager), 'streamer')->create();

        $this->actingAs($manager)->post(route('bookings.approve', $booking))->assertSessionHas('success');
        $this->actingAs($manager)
            ->post(route('bookings.reject', $booking), ['reject_reason' => 'Muốn hủy sau khi duyệt'])
            ->assertSessionHas('error');

        $this->assertSame('approved', $booking->fresh()->status);
        $this->assertTrue(Schedule::where('reference_id', $booking->id)->exists());
    }

    public function test_pending_booking_can_be_rejected(): void
    {
        $manager = User::factory()->manager()->create();
        $booking = Booking::factory()->for(StreamerProfile::factory()->managedBy($manager), 'streamer')->create();

        $this->actingAs($manager)
            ->post(route('bookings.reject', $booking), ['reject_reason' => 'Ngân sách chưa phù hợp'])
            ->assertSessionHas('success');

        $this->assertSame('rejected', $booking->fresh()->status);
        $this->assertSame('Ngân sách chưa phù hợp', $booking->fresh()->reject_reason);
    }
}

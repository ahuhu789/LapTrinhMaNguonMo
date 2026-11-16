<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kpi;
use App\Models\Schedule;
use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteActionsTest extends TestCase
{
    use RefreshDatabase;

    // ---- Admin xóa user ----

    public function test_admin_deletes_user_without_bookings(): void
    {
        $admin = $this->makeUser('admin');
        $client = $this->makeUser('client');

        $this->actingAs($admin)->delete(route('admin.users.destroy', $client))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertModelMissing($client);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();
        $this->assertModelExists($admin);
    }

    public function test_client_with_bookings_is_blocked_with_clear_message(): void
    {
        $admin = $this->makeUser('admin');
        $booking = Booking::factory()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $booking->client))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Không thể xóa tài khoản') && str_contains($m, '1 booking'));

        $this->assertModelExists($booking->client);
        $this->assertModelExists($booking);
    }

    public function test_streamer_with_bookings_is_blocked_so_history_is_not_cascaded_away(): void
    {
        $admin = $this->makeUser('admin');
        $booking = Booking::factory()->create(['status' => 'completed']);
        $streamerUser = $booking->streamer->user;

        $this->actingAs($admin)->delete(route('admin.users.destroy', $streamerUser))->assertSessionHas('error');

        $this->assertModelExists($streamerUser);
        $this->assertModelExists($booking);
    }

    public function test_deleting_manager_unassigns_streamers(): void
    {
        $admin = $this->makeUser('admin');
        $manager = $this->makeUser('manager');
        $talent = $this->makeStreamer($manager);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $manager))->assertSessionHas('success');

        $this->assertModelMissing($manager);
        $this->assertNull($talent->fresh()->manager_id);
    }

    public function test_non_admin_cannot_delete_users(): void
    {
        $victim = $this->makeUser('client');

        $this->actingAs($this->makeUser('manager'))->delete(route('admin.users.destroy', $victim))->assertForbidden();
        $this->assertModelExists($victim);
    }

    // ---- Streamer xóa lịch bận ----

    private function schedule(StreamerProfile $talent, string $type = 'personal'): Schedule
    {
        return Schedule::create([
            'streamer_id' => $talent->id, 'event_type' => $type, 'title' => 'Lịch cá nhân',
            'start_time' => now()->addDay(), 'end_time' => now()->addDay()->addHours(2),
        ]);
    }

    public function test_streamer_deletes_own_schedule(): void
    {
        $talent = $this->makeStreamer();
        $schedule = $this->schedule($talent);

        $this->actingAs($talent->user)->delete(route('streamer.schedule.destroy', $schedule))
            ->assertSessionHas('success');

        $this->assertModelMissing($schedule);
    }

    public function test_streamer_cannot_delete_others_schedule(): void
    {
        $schedule = $this->schedule($this->makeStreamer());

        $this->actingAs($this->makeStreamer()->user)->delete(route('streamer.schedule.destroy', $schedule))->assertForbidden();
        $this->assertModelExists($schedule);
    }

    public function test_booking_locked_schedule_cannot_be_deleted_by_streamer(): void
    {
        $talent = $this->makeStreamer();
        $schedule = $this->schedule($talent, 'booking');

        $this->actingAs($talent->user)->delete(route('streamer.schedule.destroy', $schedule))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'hủy booking'));

        $this->assertModelExists($schedule);
    }

    // ---- Manager/Admin xóa KPI ----

    private function kpi(StreamerProfile $talent): Kpi
    {
        return Kpi::create(['streamer_id' => $talent->id, 'month_year' => '2026-11', 'target_hours' => 10, 'target_revenue' => 1000, 'target_avg_viewers' => 0]);
    }

    public function test_responsible_manager_and_admin_delete_kpi(): void
    {
        $manager = $this->makeUser('manager');
        $a = $this->kpi($this->makeStreamer($manager));
        $b = $this->kpi($this->makeStreamer());

        $this->actingAs($manager)->delete(route('manager.kpis.destroy', $a))->assertSessionHas('success');
        $this->actingAs($this->makeUser('admin'))->delete(route('manager.kpis.destroy', $b))->assertSessionHas('success');

        $this->assertModelMissing($a);
        $this->assertModelMissing($b);
    }

    public function test_other_manager_and_streamer_cannot_delete_kpi(): void
    {
        $talent = $this->makeStreamer($this->makeUser('manager'));
        $kpi = $this->kpi($talent);

        $this->actingAs($this->makeUser('manager'))->delete(route('manager.kpis.destroy', $kpi))->assertForbidden();
        $this->actingAs($talent->user)->delete(route('manager.kpis.destroy', $kpi))->assertForbidden();
        $this->assertModelExists($kpi);
    }

    // ---- Client xóa booking ----

    public function test_client_deletes_own_rejected_or_cancelled_booking(): void
    {
        foreach (['rejected', 'cancelled'] as $status) {
            $booking = Booking::factory()->create(['status' => $status]);

            $this->actingAs($booking->client)->delete(route('bookings.destroy', $booking))
                ->assertRedirect(route('bookings.index'))->assertSessionHas('success');

            $this->assertModelMissing($booking);
        }
    }

    public function test_client_cannot_delete_active_or_completed_booking(): void
    {
        foreach (['pending', 'reviewing', 'approved', 'completed'] as $status) {
            $booking = Booking::factory()->create(['status' => $status]);

            $this->actingAs($booking->client)->delete(route('bookings.destroy', $booking))
                ->assertSessionHas('error', fn ($m) => str_contains($m, 'Chỉ được xóa booking đã bị từ chối hoặc đã hủy'));

            $this->assertModelExists($booking);
        }
    }

    public function test_only_owner_client_can_delete_booking(): void
    {
        $booking = Booking::factory()->create(['status' => 'rejected']);

        $this->actingAs($this->makeUser('client'))->delete(route('bookings.destroy', $booking))->assertForbidden();
        $this->actingAs($this->makeUser('admin'))->delete(route('bookings.destroy', $booking))->assertForbidden();
        $this->assertModelExists($booking);
    }
}

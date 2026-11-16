<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerDashboardScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_all_streamers_and_their_pending_bookings(): void
    {
        $managerA = $this->makeUser('manager');
        $a = $this->makeStreamer($managerA);
        $b = $this->makeStreamer($this->makeUser('manager'));
        $unassigned = $this->makeStreamer();
        Booking::factory()->for($b, 'streamer')->create(['status' => 'pending']);
        Booking::factory()->for($unassigned, 'streamer')->create(['status' => 'reviewing']);

        $response = $this->actingAs($this->makeUser('admin'))->get(route('manager.dashboard'))->assertOk();

        $this->assertTrue($response->viewData('isAdminView'));
        $this->assertEqualsCanonicalizing(
            [$a->id, $b->id, $unassigned->id],
            $response->viewData('managedStreamers')->pluck('id')->all()
        );
        $this->assertCount(2, $response->viewData('pendingBookings'));
    }

    public function test_manager_sees_only_own_streamers(): void
    {
        $manager = $this->makeUser('manager');
        $mine = $this->makeStreamer($manager);
        $other = $this->makeStreamer($this->makeUser('manager'));
        Booking::factory()->for($other, 'streamer')->create(['status' => 'pending']);

        $response = $this->actingAs($manager)->get(route('manager.dashboard'))->assertOk();

        $this->assertFalse($response->viewData('isAdminView'));
        $this->assertSame([$mine->id], $response->viewData('managedStreamers')->pluck('id')->all());
        $this->assertCount(0, $response->viewData('pendingBookings'));
    }
}

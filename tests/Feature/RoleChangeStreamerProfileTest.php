<?php

namespace Tests\Feature;

use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleChangeStreamerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_promoting_user_to_streamer_creates_empty_profile_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->client()->create(['name' => 'Tân Binh']);

        $this->actingAs($admin)->post(route('admin.users.role', $user), ['role' => 'streamer'])->assertSessionHas('success');
        $this->actingAs($admin)->post(route('admin.users.role', $user), ['role' => 'streamer']);

        $this->assertSame(1, StreamerProfile::where('user_id', $user->id)->count());
        $profile = $user->fresh()->streamerProfile;
        $this->assertSame('Tân Binh', $profile->stage_name);
        $this->assertSame(config('mcn.default_category'), $profile->category);
        $this->assertEquals(config('mcn.default_rate_per_hour'), (float) $profile->rate_per_hour);
        $this->assertSame(0, $profile->metrics()->count());

        // không có metrics -> chỉ số = 0, cột tổng hợp = null ("Chưa có dữ liệu")
        $this->assertSame(0, $profile->avg_viewers);
        $this->assertSame(0, $profile->peak_viewers);
        $this->assertSame(0.0, $profile->monthly_stream_hours);
        $withStats = StreamerProfile::withViewerStats()->find($profile->id);
        $this->assertNull($withStats->avg_viewers_calc);
        $this->assertNull($withStats->peak_viewers_calc);
        $this->assertSame(0, $withStats->avg_viewers);

        $this->get(route('talents.show', $profile->id))->assertOk();
        $this->get(route('home'))->assertOk();
    }
}

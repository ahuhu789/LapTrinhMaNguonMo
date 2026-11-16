<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kpi;
use App\Models\Schedule;
use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $client;
    private StreamerProfile $talent;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00'));
        $this->manager = $this->makeUser('manager');
        $this->client = $this->makeUser('client');
        $this->talent = $this->makeStreamer($this->manager);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function booking(string $status, string $start = '2026-11-12 14:00:00', int $hours = 2, array $attrs = []): Booking
    {
        return Booking::factory()->for($this->talent, 'streamer')->for($this->client, 'client')
            ->between($start, Carbon::parse($start)->addHours($hours)->toDateTimeString())
            ->create(array_merge(['status' => $status, 'budget' => 3_000_000], $attrs));
    }

    // ---- pending -> reviewing ----

    public function test_manager_moves_pending_to_reviewing(): void
    {
        $b = $this->booking('pending');

        $this->actingAs($this->manager)->post(route('bookings.review', $b))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSame('reviewing', $b->fresh()->status);
    }

    public function test_review_rejects_non_pending_with_message(): void
    {
        $b = $this->booking('approved');

        $this->actingAs($this->makeUser('admin'))->post(route('bookings.review', $b))
            ->assertSessionHas('error');

        $this->assertSame('approved', $b->fresh()->status);
    }

    public function test_review_forbidden_for_other_manager_and_client(): void
    {
        $b = $this->booking('pending');

        $this->actingAs($this->makeUser('manager'))->post(route('bookings.review', $b))->assertForbidden();
        $this->actingAs($this->client)->post(route('bookings.review', $b))->assertForbidden();
        $this->assertSame('pending', $b->fresh()->status);
    }

    // ---- approved -> completed ----

    public function test_complete_after_end_time_recomputes_kpi(): void
    {
        $kpi = Kpi::create([
            'streamer_id' => $this->talent->id, 'month_year' => '2026-11',
            'target_hours' => 0, 'target_revenue' => 3_000_000, 'target_avg_viewers' => 0,
        ]);
        $b = $this->booking('approved', '2026-11-05 14:00:00');

        $this->actingAs($this->manager)->post(route('bookings.complete', $b))
            ->assertSessionHas('success');

        $b->refresh();
        $this->assertSame('completed', $b->status);
        $this->assertNotNull($b->completed_at);
        $kpi->refresh();
        $this->assertEquals(3_000_000, (float) $kpi->achieved_revenue);
        $this->assertSame('achieved', $kpi->status);
    }

    public function test_complete_before_end_time_is_refused_with_clear_message(): void
    {
        // đang diễn ra: 11:00 - 13:00, bây giờ 12:00
        $b = $this->booking('approved', '2026-11-10 11:00:00');

        $this->actingAs($this->manager)->post(route('bookings.complete', $b))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Chưa thể đánh dấu hoàn thành') && str_contains($m, '13:00 10/11/2026'));

        $this->assertSame('approved', $b->fresh()->status);
    }

    public function test_complete_requires_approved_status(): void
    {
        $b = $this->booking('pending', '2026-11-01 10:00:00');

        $this->actingAs($this->manager)->post(route('bookings.complete', $b))->assertSessionHas('error');
        $this->assertSame('pending', $b->fresh()->status);
    }

    public function test_complete_forbidden_for_client_and_streamer(): void
    {
        $b = $this->booking('approved', '2026-11-01 10:00:00');

        $this->actingAs($this->client)->post(route('bookings.complete', $b))->assertForbidden();
        $this->actingAs($this->talent->user)->post(route('bookings.complete', $b))->assertForbidden();
    }

    // ---- hủy bởi client ----

    public function test_client_cancels_pending_reviewing_and_future_approved(): void
    {
        foreach (['pending', 'reviewing', 'approved'] as $i => $status) {
            $b = $this->booking($status, '2026-11-2' . $i . ' 14:00:00');
            if ($status === 'approved') {
                Schedule::create([
                    'streamer_id' => $this->talent->id, 'event_type' => 'booking', 'reference_id' => $b->id,
                    'start_time' => $b->start_time, 'end_time' => $b->end_time, 'title' => 'Booking',
                ]);
            }

            $this->actingAs($this->client)->post(route('bookings.cancel', $b))
                ->assertSessionHas('success');

            $b->refresh();
            $this->assertSame('cancelled', $b->status);
            $this->assertNull($b->cancel_reason);
            $this->assertSame($this->client->id, $b->cancelled_by);
            $this->assertNotNull($b->cancelled_at);
        }

        // lịch bận đã khóa của booking approved được giải phóng
        $this->assertSame(0, Schedule::where('event_type', 'booking')->count());
    }

    public function test_client_cannot_cancel_started_approved_booking(): void
    {
        $b = $this->booking('approved', '2026-11-10 11:00:00');

        $this->actingAs($this->client)->post(route('bookings.cancel', $b))
            ->assertSessionHas('error', 'Không thể hủy: buổi live đã bắt đầu hoặc đã diễn ra.');

        $this->assertSame('approved', $b->fresh()->status);
    }

    public function test_client_cannot_cancel_completed_or_others_booking(): void
    {
        $completed = $this->booking('completed', '2026-11-01 10:00:00');
        $this->actingAs($this->client)->post(route('bookings.cancel', $completed))->assertSessionHas('error');
        $this->assertSame('completed', $completed->fresh()->status);

        $b = $this->booking('pending');
        $this->actingAs($this->makeUser('client'))->post(route('bookings.cancel', $b))->assertForbidden();
        $this->actingAs($this->talent->user)->post(route('bookings.cancel', $b))->assertForbidden();
        $this->assertSame('pending', $b->fresh()->status);
    }

    // ---- hủy bởi manager / admin ----

    public function test_manager_cancels_future_approved_with_required_reason(): void
    {
        $b = $this->booking('approved');

        $this->actingAs($this->manager)->post(route('bookings.cancel', $b), [])
            ->assertSessionHasErrors(['cancel_reason' => 'Vui lòng nhập lý do hủy booking để thông báo đến khách hàng.']);
        $this->assertSame('approved', $b->fresh()->status);

        $this->actingAs($this->manager)->post(route('bookings.cancel', $b), ['cancel_reason' => 'Talent bị ốm đột xuất'])
            ->assertSessionHas('success');

        $b->refresh();
        $this->assertSame('cancelled', $b->status);
        $this->assertSame('Talent bị ốm đột xuất', $b->cancel_reason);
        $this->assertSame($this->manager->id, $b->cancelled_by);
        $this->assertNull($b->reject_reason);
    }

    public function test_admin_cancels_any_future_approved(): void
    {
        $b = $this->booking('approved');

        $this->actingAs($this->makeUser('admin'))->post(route('bookings.cancel', $b), ['cancel_reason' => 'Nhãn hàng đổi kế hoạch'])
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $b->fresh()->status);
    }

    public function test_manager_cannot_cancel_started_or_pending_booking(): void
    {
        $started = $this->booking('approved', '2026-11-10 11:00:00');
        $this->actingAs($this->manager)->post(route('bookings.cancel', $started), ['cancel_reason' => 'Lý do bất kỳ'])
            ->assertSessionHas('error', 'Không thể hủy: buổi live đã bắt đầu hoặc đã diễn ra.');
        $this->assertSame('approved', $started->fresh()->status);

        $pending = $this->booking('pending');
        $this->actingAs($this->manager)->post(route('bookings.cancel', $pending), ['cancel_reason' => 'Lý do bất kỳ'])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Từ chối'));
        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_other_manager_cannot_cancel(): void
    {
        $b = $this->booking('approved');

        $this->actingAs($this->makeUser('manager'))->post(route('bookings.cancel', $b), ['cancel_reason' => 'Không phải talent của tôi'])
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $b = $this->booking('pending');

        $this->post(route('bookings.cancel', $b))->assertRedirect(route('login'));
        $this->post(route('bookings.review', $b))->assertRedirect(route('login'));
    }
}

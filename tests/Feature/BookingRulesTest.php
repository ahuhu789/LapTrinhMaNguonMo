<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\StreamerProfile;
use App\Models\User;
use App\Services\BookingCollisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Quy tắc đặt lịch:
 *  1. cùng talent không có 2 lịch chồng nhau (schedules + booking pending/reviewing/approved)
 *  2. cùng client không có 2 booking còn hiệu lực chồng nhau
 *  3. vi phạm -> lưu với status rejected + lý do; duyệt -> tự động từ chối booking chồng giờ
 */
class BookingRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $client;
    private StreamerProfile $talent;
    private Carbon $day;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-27 09:00:00', 'Asia/Ho_Chi_Minh'));
        $this->manager = User::factory()->manager()->create();
        $this->client = User::factory()->client()->create();
        $this->talent = StreamerProfile::factory()->managedBy($this->manager)->create();
        $this->day = Carbon::parse('2026-11-05 00:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function at(string $time): string
    {
        // định dạng của input <input type="datetime-local">
        return $this->day->format('Y-m-d') . 'T' . $time;
    }

    private function book(User $client, StreamerProfile $talent, string $from, string $to, array $extra = [])
    {
        return $this->actingAs($client)->post(route('bookings.store'), array_merge([
            'streamer_id' => $talent->id,
            'start_time' => $this->at($from),
            'end_time' => $this->at($to),
            'job_description' => 'Livestream giới thiệu sản phẩm mới',
            'budget' => 2000000,
        ], $extra));
    }

    private function lastBooking(): Booking
    {
        return Booking::latest('id')->first();
    }

    private function busySlot(string $from, string $to, StreamerProfile $talent = null): Schedule
    {
        return Schedule::create([
            'streamer_id' => ($talent ?? $this->talent)->id,
            'event_type' => 'personal',
            'start_time' => $this->day->copy()->setTimeFromTimeString($from),
            'end_time' => $this->day->copy()->setTimeFromTimeString($to),
            'title' => 'Lịch riêng tư của streamer',
        ]);
    }

    // ---------------------------------------------------------------- tạo booking

    public function test_free_slot_creates_pending_booking_stored_in_local_time(): void
    {
        $this->book($this->client, $this->talent, '14:00', '16:00')
            ->assertRedirect(route('bookings.show', $this->lastBooking()))
            ->assertSessionHas('success');

        $booking = $this->lastBooking();
        $this->assertSame('pending', $booking->status);
        $this->assertNull($booking->reject_reason);
        // không lệch múi giờ: giờ nhập 14:00 được lưu đúng 14:00 dạng chuỗi chuẩn
        $this->assertSame('2026-11-05 14:00:00', $booking->getRawOriginal('start_time'));
        $this->assertSame('2026-11-05 16:00:00', $booking->getRawOriginal('end_time'));
        $this->assertSame('Asia/Ho_Chi_Minh', config('app.timezone'));
    }

    public function test_iso_utc_input_is_converted_to_local_time(): void
    {
        $this->actingAs($this->client)->post(route('bookings.store'), [
            'streamer_id' => $this->talent->id,
            'start_time' => '2026-11-05T07:00:00Z', // = 14:00 giờ Việt Nam
            'end_time' => '2026-11-05T09:00:00Z',
            'job_description' => 'Livestream giới thiệu sản phẩm mới',
            'budget' => 2000000,
        ])->assertSessionHas('success');

        $this->assertSame('2026-11-05 14:00:00', $this->lastBooking()->getRawOriginal('start_time'));
    }

    public function test_talent_schedule_conflict_saves_rejected_booking_with_reason(): void
    {
        $this->busySlot('15:00', '18:00');

        $this->book($this->client, $this->talent, '14:00', '16:00')
            ->assertRedirect(route('bookings.show', $this->lastBooking()))
            ->assertSessionHas('error', fn ($m) => str_starts_with($m, 'Yêu cầu booking đã bị từ chối tự động do trùng lịch.'));

        $booking = $this->lastBooking();
        $this->assertSame('rejected', $booking->status);
        $this->assertSame(
            'Tự động từ chối: Trùng với lịch bận của talent từ 15:00 05/11/2026 đến 18:00 05/11/2026.',
            $booking->rejection_reason
        );
        // không lộ tiêu đề lịch riêng của streamer
        $this->assertStringNotContainsString('riêng tư', $booking->reject_reason);
    }

    public function test_other_clients_pending_booking_blocks_talent_without_leaking_details(): void
    {
        $other = User::factory()->client()->create(['name' => 'Khách Bí Mật']);
        $this->book($other, $this->talent, '14:00', '16:00', ['job_description' => 'Chiến dịch tuyệt mật XYZ']);
        $otherBooking = $this->lastBooking();

        $this->book($this->client, $this->talent, '15:00', '17:00');
        $mine = $this->lastBooking();

        $this->assertSame('rejected', $mine->status);
        $this->assertSame(
            'Tự động từ chối: Trùng với một booking khác đang chờ duyệt của talent từ 14:00 05/11/2026 đến 16:00 05/11/2026.',
            $mine->reject_reason
        );
        foreach (['#' . $otherBooking->id, 'Khách Bí Mật', 'tuyệt mật', $other->email] as $secret) {
            $this->assertStringNotContainsString($secret, $mine->reject_reason);
        }

        // client xem trang booking của mình cũng không thấy thông tin booking kia
        $this->actingAs($this->client)->get(route('bookings.show', $mine))
            ->assertOk()
            ->assertDontSee('tuyệt mật')
            ->assertDontSee('Khách Bí Mật');
    }

    public function test_touching_edges_are_not_conflicts(): void
    {
        $this->busySlot('10:00', '12:00');
        $this->book($this->client, $this->talent, '14:00', '16:00');

        $this->book($this->client, $this->talent, '12:00', '14:00'); // chạm cả 2 mép
        $this->assertSame('pending', $this->lastBooking()->status);

        $this->book($this->client, $this->talent, '16:00', '18:00');
        $this->assertSame('pending', $this->lastBooking()->status);
    }

    public static function invalidRangeProvider(): array
    {
        return ['start == end' => ['14:00', '14:00'], 'start > end' => ['16:00', '14:00']];
    }

    #[DataProvider('invalidRangeProvider')]
    public function test_invalid_ranges_are_rejected_by_validation_and_not_saved(string $from, string $to): void
    {
        $this->book($this->client, $this->talent, $from, $to)
            ->assertSessionHasErrors(['end_time' => 'Thời gian kết thúc phải sau thời gian bắt đầu.']);

        $this->assertSame(0, Booking::count());
    }

    public function test_service_treats_non_positive_ranges_as_conflicts(): void
    {
        $service = app(BookingCollisionService::class);

        $this->assertTrue($service->checkCollision($this->talent->id, '2026-11-05 14:00', '2026-11-05 14:00')['has_conflict']);
        $this->assertTrue($service->checkCollision($this->talent->id, '2026-11-05 16:00', '2026-11-05 14:00')['has_conflict']);
    }

    public function test_exact_duplicate_booking_is_auto_rejected(): void
    {
        $this->book($this->client, $this->talent, '14:00', '16:00');
        $first = $this->lastBooking();

        $this->book($this->client, $this->talent, '14:00', '16:00');
        $dup = $this->lastBooking();

        $this->assertSame('pending', $first->status);
        $this->assertSame('rejected', $dup->status);
        $this->assertSame(
            "Tự động từ chối: Trùng với booking #{$first->id} của bạn từ 14:00 05/11/2026 đến 16:00 05/11/2026.",
            $dup->reject_reason
        );
    }

    public function test_same_client_cannot_overlap_with_different_talent(): void
    {
        $otherTalent = StreamerProfile::factory()->create();
        $this->book($this->client, $this->talent, '14:00', '16:00');
        $first = $this->lastBooking();

        $this->book($this->client, $otherTalent, '15:00', '17:00');

        $this->assertSame('rejected', $this->lastBooking()->status);
        $this->assertStringContainsString("Trùng với booking #{$first->id} của bạn", $this->lastBooking()->reject_reason);
    }

    public function test_different_clients_on_different_talents_do_not_conflict(): void
    {
        $this->book($this->client, $this->talent, '14:00', '16:00');
        $this->book(User::factory()->client()->create(), StreamerProfile::factory()->create(), '14:00', '16:00');

        $this->assertSame(['pending', 'pending'], Booking::orderBy('id')->pluck('status')->all());
    }

    public static function inactiveStatusProvider(): array
    {
        return [['rejected'], ['cancelled'], ['completed']];
    }

    #[DataProvider('inactiveStatusProvider')]
    public function test_inactive_bookings_do_not_block(string $status): void
    {
        Booking::factory()->for($this->talent, 'streamer')->for($this->client, 'client')
            ->between('2026-11-05 14:00:00', '2026-11-05 16:00:00')
            ->create(['status' => $status]);

        $this->book($this->client, $this->talent, '14:00', '16:00');

        $this->assertSame('pending', $this->lastBooking()->status);
    }

    // ---------------------------------------------------------------- check-collision API

    public function test_check_collision_applies_talent_and_client_rules(): void
    {
        $otherTalent = StreamerProfile::factory()->create();
        $this->book($this->client, $this->talent, '14:00', '16:00');

        $check = fn (User $u, StreamerProfile $t, string $a, string $b) => $this->actingAs($u)
            ->postJson(route('bookings.check-collision'), [
                'streamer_id' => $t->id, 'start_time' => $this->at($a), 'end_time' => $this->at($b),
            ])->assertOk()->json();

        // rule 1: talent bận (booking pending của chính client)
        $this->assertSame(['has_conflict' => true], $check($this->client, $this->talent, '15:00', '17:00'));
        // rule 2: cùng client, khác talent
        $this->assertSame(['has_conflict' => true], $check($this->client, $otherTalent, '15:00', '17:00'));
        // client khác, talent khác -> trống
        $this->assertSame(['has_conflict' => false], $check(User::factory()->client()->create(), $otherTalent, '15:00', '17:00'));
        // chạm mép -> trống
        $this->assertSame(['has_conflict' => false], $check($this->client, $this->talent, '16:00', '17:00'));
    }

    // ---------------------------------------------------------------- phê duyệt

    public function test_approve_cascades_auto_reject_to_overlapping_bookings(): void
    {
        $target = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 14:00:00', '2026-11-05 16:00:00')->create();
        $overlapPending = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 15:00:00', '2026-11-05 17:00:00')->create();
        $overlapReviewing = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 13:00:00', '2026-11-05 14:30:00')->create(['status' => 'reviewing']);
        $touching = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 16:00:00', '2026-11-05 18:00:00')->create();
        $otherTalent = Booking::factory()->between('2026-11-05 14:00:00', '2026-11-05 16:00:00')->create();
        $sameClientOtherTalent = Booking::factory()->for($target->client, 'client')
            ->between('2026-11-05 15:00:00', '2026-11-05 16:00:00')->create();

        $this->actingAs($this->manager)->post(route('bookings.approve', $target))
            ->assertSessionHas('success', 'Đã phê duyệt booking và tự động khóa lịch trình thành công. Đã tự động từ chối 3 booking khác bị trùng lịch.');

        $this->assertSame('approved', $target->fresh()->status);
        foreach ([$overlapPending, $overlapReviewing] as $b) {
            $this->assertSame('rejected', $b->fresh()->status);
            $this->assertSame('Tự động từ chối: Xung đột với lịch đã được duyệt (14:00 05/11/2026 – 16:00 05/11/2026)', $b->fresh()->reject_reason);
        }
        $this->assertSame('rejected', $sameClientOtherTalent->fresh()->status);
        $this->assertSame("Tự động từ chối: Xung đột với booking #{$target->id} của bạn đã được duyệt.", $sameClientOtherTalent->fresh()->reject_reason);
        $this->assertSame('pending', $touching->fresh()->status);
        $this->assertSame('pending', $otherTalent->fresh()->status);
        $this->assertSame(1, Schedule::where('reference_id', $target->id)->count());
    }

    public function test_race_approve_against_already_approved_booking_auto_rejects(): void
    {
        // Mô phỏng race: 2 booking chồng giờ cùng tồn tại (tạo thẳng DB), booking A đã được duyệt
        // nhưng chưa kịp cascade; khi duyệt B, transaction phát hiện xung đột và tự từ chối B.
        $a = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 14:00:00', '2026-11-05 16:00:00')->approved()->create();
        $b = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 15:00:00', '2026-11-05 17:00:00')->create();

        $this->actingAs($this->manager)->post(route('bookings.approve', $b))
            ->assertSessionHas('error', 'Không thể phê duyệt! Trùng với lịch bận của talent từ 14:00 05/11/2026 đến 16:00 05/11/2026. Booking đã được tự động từ chối.');

        $this->assertSame('rejected', $b->fresh()->status);
        $this->assertSame('Tự động từ chối: Trùng với lịch bận của talent từ 14:00 05/11/2026 đến 16:00 05/11/2026.', $b->fresh()->reject_reason);
        $this->assertSame('approved', $a->fresh()->status);
        $this->assertFalse(Schedule::where('reference_id', $b->id)->exists());
    }

    public function test_show_page_warns_only_about_blocking_conflicts(): void
    {
        $target = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 14:00:00', '2026-11-05 16:00:00')->create();
        Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 15:00:00', '2026-11-05 17:00:00')->create(); // pending: không chặn duyệt

        $info = $this->actingAs($this->manager)->get(route('bookings.show', $target))->viewData('conflictInfo');
        $this->assertFalse($info['has_conflict']);

        $this->busySlot('15:30', '16:30');
        $info = $this->actingAs($this->manager)->get(route('bookings.show', $target))->viewData('conflictInfo');
        $this->assertTrue($info['has_conflict']);
    }

    public function test_reviewing_booking_can_be_approved_or_manually_rejected(): void
    {
        $toReject = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 08:00:00', '2026-11-05 09:00:00')->create(['status' => 'reviewing']);
        $toApprove = Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 10:00:00', '2026-11-05 11:00:00')->create(['status' => 'reviewing']);

        $this->actingAs($this->manager)
            ->post(route('bookings.reject', $toReject), ['reject_reason' => 'Không phù hợp định hướng'])
            ->assertSessionHas('success');
        $this->actingAs($this->manager)->post(route('bookings.approve', $toApprove))->assertSessionHas('success');

        $this->assertSame('rejected', $toReject->fresh()->status);
        $this->assertSame('Không phù hợp định hướng', $toReject->fresh()->reject_reason);
        $this->assertSame('approved', $toApprove->fresh()->status);
    }

    public function test_conflict_with_locked_or_approved_slot_says_busy_not_pending(): void
    {
        Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 14:00:00', '2026-11-05 16:00:00')->approved()->create();
        Booking::factory()->for($this->talent, 'streamer')
            ->between('2026-11-05 18:00:00', '2026-11-05 19:00:00')->create(['status' => 'reviewing']);

        $service = app(BookingCollisionService::class);

        $this->assertSame(
            'Trùng với lịch bận của talent từ 14:00 05/11/2026 đến 16:00 05/11/2026.',
            $service->checkCollision($this->talent->id, '2026-11-05 15:00', '2026-11-05 15:30', null, $this->client->id)['message']
        );
        $this->assertSame(
            'Trùng với một booking khác đang chờ duyệt của talent từ 18:00 05/11/2026 đến 19:00 05/11/2026.',
            $service->checkCollision($this->talent->id, '2026-11-05 18:30', '2026-11-05 20:00', null, $this->client->id)['message']
        );
        // vừa trùng booking đã duyệt vừa trùng booking chờ duyệt -> báo lịch bận
        $this->assertStringStartsWith(
            'Trùng với lịch bận của talent',
            $service->checkCollision($this->talent->id, '2026-11-05 15:00', '2026-11-05 18:30', null, $this->client->id)['message']
        );
    }

    // ---------------------------------------------------------------- lịch streamer

    public function test_streamer_cannot_add_event_over_pending_booking(): void
    {
        $this->book($this->client, $this->talent, '14:00', '16:00');

        $this->actingAs($this->talent->user)
            ->post(route('streamer.schedule.store'), [
                'event_type' => 'personal',
                'start_time' => $this->at('15:00'),
                'end_time' => $this->at('17:00'),
                'title' => 'Đi khám',
            ])
            ->assertSessionHasErrors('start_time');

        $this->actingAs($this->talent->user)
            ->post(route('streamer.schedule.store'), [
                'event_type' => 'personal',
                'start_time' => $this->at('16:00'),
                'end_time' => $this->at('17:00'),
                'title' => 'Đi khám',
            ])
            ->assertSessionHas('success');

        $this->assertSame('2026-11-05 16:00:00', Schedule::latest('id')->first()->getRawOriginal('start_time'));
    }
}

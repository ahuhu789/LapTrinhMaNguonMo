<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\StreamerProfile;
use App\Services\BookingCollisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Thay thế tests/collision_algorithm_standalone_test.php:
 * kiểm thử trực tiếp BookingCollisionService::checkCollision() trên CSDL thật (SQLite in-memory).
 * Lịch đã có: 2026-10-10 14:00 -> 17:00.
 */
class BookingCollisionServiceTest extends TestCase
{
    use RefreshDatabase;

    public static function intervalProvider(): array
    {
        return [
            'Trùng hoàn toàn (exact match)' => ['2026-10-10 14:00', '2026-10-10 17:00', true],
            'Lấn đầu (overlap start)' => ['2026-10-10 13:00', '2026-10-10 15:00', true],
            'Lấn cuối (overlap end)' => ['2026-10-10 16:00', '2026-10-10 19:00', true],
            'Nằm bên trong (sub-interval)' => ['2026-10-10 15:00', '2026-10-10 16:00', true],
            'Bao trùm (super-interval)' => ['2026-10-10 12:00', '2026-10-10 18:00', true],
            'Tách biệt trước (no overlap before)' => ['2026-10-10 10:00', '2026-10-10 13:00', false],
            'Tách biệt sau (no overlap after)' => ['2026-10-10 18:00', '2026-10-10 20:00', false],
            'Tiếp giáp cuối (adjacent end)' => ['2026-10-10 17:00', '2026-10-10 19:00', false],
            'Tiếp giáp đầu (adjacent start)' => ['2026-10-10 12:00', '2026-10-10 14:00', false],
        ];
    }

    private function streamerWithBusySlot(): StreamerProfile
    {
        $streamer = StreamerProfile::factory()->create();

        Schedule::create([
            'streamer_id' => $streamer->id,
            'event_type' => 'stream',
            'start_time' => '2026-10-10 14:00',
            'end_time' => '2026-10-10 17:00',
            'title' => 'Lịch stream cố định',
        ]);

        return $streamer;
    }

    #[DataProvider('intervalProvider')]
    public function test_check_collision_interval_cases(string $start, string $end, bool $expected): void
    {
        $streamer = $this->streamerWithBusySlot();

        $result = app(BookingCollisionService::class)->checkCollision($streamer->id, $start, $end);

        $this->assertSame($expected, $result['has_conflict']);
    }

    public function test_other_streamers_schedule_does_not_conflict(): void
    {
        $this->streamerWithBusySlot();
        $other = StreamerProfile::factory()->create();

        $result = app(BookingCollisionService::class)
            ->checkCollision($other->id, '2026-10-10 14:00', '2026-10-10 17:00');

        $this->assertFalse($result['has_conflict']);
    }

    public function test_end_before_start_is_reported_as_conflict(): void
    {
        $streamer = StreamerProfile::factory()->create();

        $result = app(BookingCollisionService::class)
            ->checkCollision($streamer->id, '2026-10-10 17:00', '2026-10-10 14:00');

        $this->assertTrue($result['has_conflict']);
    }

    public function test_booking_own_schedule_is_ignored_when_rechecking(): void
    {
        $booking = Booking::factory()->between('2026-10-10 14:00', '2026-10-10 17:00')->create();
        Schedule::create([
            'streamer_id' => $booking->streamer_id,
            'event_type' => 'booking',
            'reference_id' => $booking->id,
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
            'title' => 'Booking',
        ]);

        $service = app(BookingCollisionService::class);

        $this->assertTrue($service->checkCollision($booking->streamer_id, '2026-10-10 15:00', '2026-10-10 16:00')['has_conflict']);
        $this->assertFalse($service->checkCollision($booking->streamer_id, '2026-10-10 15:00', '2026-10-10 16:00', $booking->id)['has_conflict']);
    }
}

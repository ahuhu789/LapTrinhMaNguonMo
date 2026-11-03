<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\StreamerProfile;
use App\Models\User;
use App\Services\BookingPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingBudgetTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private StreamerProfile $talent;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-03 09:00:00'));
        $this->client = User::factory()->client()->create();
        $this->talent = StreamerProfile::factory()->create(['rate_per_hour' => 600000]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function store(string $from, string $to, $budget)
    {
        return $this->actingAs($this->client)->post(route('bookings.store'), [
            'streamer_id' => $this->talent->id,
            'start_time' => "2026-11-05 $from",
            'end_time' => "2026-11-05 $to",
            'job_description' => 'Livestream giới thiệu sản phẩm mới',
            'budget' => $budget,
        ]);
    }

    public static function quotes(): array
    {
        return [
            '2h' => ['14:00', '16:00', 1_200_000, '2'],
            '2h30' => ['14:00', '16:30', 1_500_000, '2,5'],
            '1h20 (round up to 1 đồng)' => ['14:00', '15:20', 800_000, '1,33'],
            '7 min' => ['14:00', '14:07', 70_000, '0,12'],
        ];
    }

    #[DataProvider('quotes')]
    public function test_quote_is_per_minute_and_rounded_up(string $from, string $to, int $min, string $hours): void
    {
        $q = app(BookingPricing::class)->quote($this->talent, Carbon::parse("2026-11-05 $from"), Carbon::parse("2026-11-05 $to"));

        $this->assertSame($min, $q['min_budget']);
        $this->assertSame("Tối thiểu " . BookingPricing::money($min) . " ($hours giờ × 600.000đ/giờ)", $q['label']);
    }

    public function test_rounding_up_with_odd_rate(): void
    {
        // 1 phút × 100.001đ/giờ = 1.666,68đ -> 1.667đ
        $q = app(BookingPricing::class)->quote(100001, Carbon::parse('2026-11-05 14:00'), Carbon::parse('2026-11-05 14:01'));
        $this->assertSame(1667, $q['min_budget']);
    }

    public function test_budget_below_minimum_is_rejected_with_vietnamese_message(): void
    {
        $this->store('14:00', '16:30', 1_499_999)
            ->assertSessionHasErrors(['budget' => 'Tối thiểu 1.500.000đ (2,5 giờ × 600.000đ/giờ)']);
        $this->assertSame(0, Booking::count());
    }

    public function test_exact_minimum_and_non_round_amounts_are_accepted(): void
    {
        $this->store('14:00', '16:30', 1_500_000)->assertSessionHasNoErrors();
        $this->store('17:00', '18:00', 612_345)->assertSessionHasNoErrors(); // không cần bội số 50.000
        $this->assertSame(2, Booking::where('status', 'pending')->count());
    }

    public function test_small_booking_below_old_100k_floor_is_allowed(): void
    {
        $this->store('14:00', '14:07', 70_000)->assertSessionHasNoErrors();
        $this->assertSame(1, Booking::count());
    }

    public function test_budget_must_be_integer(): void
    {
        $this->store('14:00', '16:00', '1500000.5')
            ->assertSessionHasErrors(['budget' => 'Ngân sách phải là số nguyên (đơn vị đồng).']);
        $this->store('14:00', '16:00', 'abc')->assertSessionHasErrors('budget');
    }

    public function test_check_collision_returns_quote(): void
    {
        $this->actingAs($this->client)->postJson(route('bookings.check-collision'), [
            'streamer_id' => $this->talent->id,
            'start_time' => '2026-11-05T14:00',
            'end_time' => '2026-11-05T16:30',
        ])->assertOk()->assertExactJson([
            'has_conflict' => false,
            'conflict_message' => null,
            'pending_count' => 0,
            'warnings' => [],
            'min_budget' => 1_500_000,
            'min_budget_label' => 'Tối thiểu 1.500.000đ (2,5 giờ × 600.000đ/giờ)',
            'hours' => 2.5,
            'duration_minutes' => 150,
            'rate_per_hour' => 600000,
        ]);
    }
}

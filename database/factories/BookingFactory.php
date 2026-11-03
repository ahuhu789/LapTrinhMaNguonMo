<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(2, 30))->setTime(fake()->numberBetween(8, 18), 0);

        return [
            'client_id' => User::factory()->client(),
            'streamer_id' => StreamerProfile::factory(),
            // Mặc định manager của booking = manager phụ trách streamer (giống BookingController@store)
            'manager_id' => fn (array $attrs) => StreamerProfile::find($attrs['streamer_id'])?->manager_id,
            'start_time' => $start,
            'end_time' => (clone $start)->addHours(2),
            'job_description' => fake()->sentence(10),
            'budget' => fake()->numberBetween(10, 50) * 100000,
            'commission_rate' => config('mcn.commission_rate'),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    public function between(string $start, string $end): static
    {
        return $this->state(['start_time' => $start, 'end_time' => $end]);
    }
}

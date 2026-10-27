<?php

namespace Database\Factories;

use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StreamerProfile>
 */
class StreamerProfileFactory extends Factory
{
    protected $model = StreamerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->streamer(),
            'manager_id' => null,
            'stage_name' => fake()->unique()->userName(),
            'category' => fake()->randomElement(config('mcn.categories')),
            'bio' => fake()->sentence(12),
            'rate_per_hour' => fake()->numberBetween(5, 30) * 100000,
            'status' => 'active',
        ];
    }

    public function managedBy(User $manager): static
    {
        return $this->state(['manager_id' => $manager->id]);
    }
}

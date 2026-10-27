<?php

namespace Tests;

use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private static int $seq = 0;

    protected function makeUser(string $role = 'client', array $attrs = []): User
    {
        $n = ++self::$seq;

        return User::create(array_merge([
            'name' => ucfirst($role) . " $n",
            'email' => "{$role}{$n}@test.local",
            'password' => 'password123',
            'role' => $role,
        ], $attrs));
    }

    protected function makeStreamer(?User $manager = null, array $attrs = []): StreamerProfile
    {
        $user = $this->makeUser('streamer');

        return StreamerProfile::create(array_merge([
            'user_id' => $user->id,
            'manager_id' => $manager?->id,
            'stage_name' => $user->name,
            'category' => 'Gaming',
            'rate_per_hour' => 500000,
            'status' => 'active',
        ], $attrs));
    }
}

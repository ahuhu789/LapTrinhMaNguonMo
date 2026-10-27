<?php

namespace Tests;

use App\Models\StreamerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function makeUser(string $role = 'client', array $attrs = []): User
    {
        return User::factory()->create(array_merge(['role' => $role], $attrs));
    }

    protected function makeStreamer(?User $manager = null, array $attrs = []): StreamerProfile
    {
        return StreamerProfile::factory()->create(array_merge([
            'manager_id' => $manager?->id,
        ], $attrs));
    }
}

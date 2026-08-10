<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_starts_empty_for_each_test(): void
    {
        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_factory_creates_an_isolated_database_record(): void
    {
        $user = User::factory()->create([
            'name' => 'Test Driver',
            'email' => 'driver@example.test',
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Test Driver',
            'email' => 'driver@example.test',
        ]);
        $this->assertArrayNotHasKey('password', $user->toArray());
    }
}

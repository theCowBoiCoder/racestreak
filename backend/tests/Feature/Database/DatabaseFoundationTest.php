<?php

namespace Tests\Feature\Database;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    public function test_database_connectivity_command_succeeds(): void
    {
        $this->artisan('db:check')
            ->expectsOutputToContain('Database connection is healthy (sqlite).')
            ->assertSuccessful();
    }

    public function test_migrations_can_be_applied_and_rolled_back(): void
    {
        $this->artisan('migrate')->assertSuccessful();

        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('jobs'));

        $this->artisan('migrate:rollback')->assertSuccessful();

        $this->assertFalse(Schema::hasTable('users'));
        $this->assertFalse(Schema::hasTable('jobs'));
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DriverRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_can_register_with_valid_details(): void
    {
        $response = $this->postJson('/api/v1/driver-accounts', [
            'display_name' => '  Apex Driver  ',
            'email' => 'Driver@Example.Test ',
            'password' => 'Correct-Horse-7!',
            'password_confirmation' => 'Correct-Horse-7!',
        ]);

        $response
            ->assertCreated()
            ->assertHeader('Location')
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.display_name', 'Apex Driver')
            ->assertJsonPath('data.email', 'driver@example.test')
            ->assertJsonPath('data.email_verified', false)
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.public_id')
            ->assertJsonMissingPath('data.id_internal');

        $publicId = $response->json('data.id');

        $this->assertIsString($publicId);
        $this->assertTrue(Str::isUuid($publicId, version: 7));
        $response->assertHeader('Location', "/api/v1/driver-accounts/{$publicId}");

        $driver = User::query()->sole();

        $this->assertSame($publicId, $driver->public_id);
        $this->assertSame('Apex Driver', $driver->name);
        $this->assertSame('driver@example.test', $driver->email);
        $this->assertTrue(Hash::check('Correct-Horse-7!', $driver->password));
        $this->assertNotSame('Correct-Horse-7!', $driver->password);
    }

    public function test_invalid_registration_returns_field_errors_without_creating_account(): void
    {
        User::factory()->create(['email' => 'driver@example.test']);

        $this->postJson('/api/v1/driver-accounts', [
            'display_name' => ' ',
            'email' => 'DRIVER@example.test',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonValidationErrors(
                ['display_name', 'email', 'password'],
                'error.details.fields',
            );

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_rate_limit_uses_standard_error_and_headers(): void
    {
        $payload = [
            'display_name' => 'Apex Driver',
            'email' => 'rate-limited@example.test',
            'password' => 'Correct-Horse-7!',
            'password_confirmation' => 'Correct-Horse-7!',
        ];

        $this->postJson('/api/v1/driver-accounts', $payload)->assertCreated();

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->postJson('/api/v1/driver-accounts', $payload)->assertUnprocessable();
        }

        $this->postJson('/api/v1/driver-accounts', $payload)
            ->assertTooManyRequests()
            ->assertHeader('RateLimit-Limit', '5')
            ->assertHeader('RateLimit-Remaining', '0')
            ->assertHeader('RateLimit-Reset')
            ->assertHeader('Retry-After')
            ->assertExactJson([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'The request rate limit has been exceeded.',
                ],
            ]);
    }
}

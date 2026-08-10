<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DriverAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_handshake_starts_a_session_without_caching_the_token(): void
    {
        $response = $this->getJson('/api/v1/authentication/csrf-token');

        $response
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertCookie(config('session.cookie'))
            ->assertJsonPath('success', true);

        $this->assertIsString($response->json('data.token'));
        $this->assertNotSame('', $response->json('data.token'));
    }

    public function test_driver_can_sign_in_and_receive_only_safe_account_fields(): void
    {
        Log::spy();

        $driver = User::factory()->create([
            'email' => 'driver@example.test',
            'password' => 'Correct-Horse-7!',
        ]);

        $response = $this->postJson('/api/v1/authentication/session', [
            'email' => ' Driver@Example.Test ',
            'password' => 'Correct-Horse-7!',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $driver->public_id)
            ->assertJsonPath('data.email', 'driver@example.test')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.public_id')
            ->assertJsonMissingPath('data.remember_token');

        $this->assertAuthenticatedAs($driver, 'web');

        Log::shouldHaveReceived('info')->withArgs(
            fn (string $message, array $context): bool => $message === 'auth.login_succeeded'
                && $context === ['driver_account_id' => $driver->public_id],
        )->once();
    }

    public function test_invalid_credentials_use_the_same_safe_response_for_known_and_unknown_emails(): void
    {
        User::factory()->create([
            'email' => 'known@example.test',
            'password' => 'Correct-Horse-7!',
        ]);

        $known = $this->postJson('/api/v1/authentication/session', [
            'email' => 'known@example.test',
            'password' => 'Wrong-Password-7!',
        ]);
        $unknown = $this->postJson('/api/v1/authentication/session', [
            'email' => 'unknown@example.test',
            'password' => 'Wrong-Password-7!',
        ]);

        $expected = [
            'success' => false,
            'error' => [
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'The provided credentials are invalid.',
            ],
        ];

        $known->assertUnauthorized()->assertExactJson($expected);
        $unknown->assertUnauthorized()->assertExactJson($expected);
        $this->assertGuest('web');
    }

    public function test_failed_login_attempts_are_rate_limited_with_standard_headers(): void
    {
        $payload = [
            'email' => 'rate-limited@example.test',
            'password' => 'Wrong-Password-7!',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/authentication/session', $payload)->assertUnauthorized();
        }

        $this->postJson('/api/v1/authentication/session', $payload)
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

    public function test_current_account_requires_authentication_and_returns_safe_fields(): void
    {
        $this->getJson('/api/v1/authentication/session')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');

        $driver = User::factory()->create();

        $this->actingAs($driver, 'web')
            ->getJson('/api/v1/authentication/session')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.id', $driver->public_id)
            ->assertJsonMissingPath('data.password');
    }

    public function test_driver_can_sign_out_and_invalidate_the_active_session(): void
    {
        Log::spy();

        $driver = User::factory()->create();

        $this->actingAs($driver, 'web')
            ->deleteJson('/api/v1/authentication/session')
            ->assertOk()
            ->assertJsonPath('data.message', 'Signed out.');

        $this->assertGuest('web');
        $this->getJson('/api/v1/authentication/session')->assertUnauthorized();

        Log::shouldHaveReceived('info')->withArgs(
            fn (string $message, array $context): bool => $message === 'auth.logout_succeeded'
                && $context === ['driver_account_id' => $driver->public_id],
        )->once();
    }
}

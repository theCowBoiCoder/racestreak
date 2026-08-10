<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class RequestContextTest extends TestCase
{
    public function test_valid_client_request_id_is_returned_for_correlation(): void
    {
        $this->withHeader('X-Request-ID', 'client-request_123')
            ->getJson('/api/v1/health')
            ->assertOk()
            ->assertHeader('X-Request-ID', 'client-request_123');
    }

    public function test_invalid_client_request_id_is_replaced(): void
    {
        $response = $this->withHeader('X-Request-ID', 'invalid request id with spaces')
            ->getJson('/api/v1/health')
            ->assertOk();

        $requestId = $response->headers->get('X-Request-ID');

        $this->assertIsString($requestId);
        $this->assertMatchesRegularExpression(
            '/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/',
            $requestId,
        );
    }

    public function test_failed_requests_log_safe_route_context(): void
    {
        Log::spy();

        Route::get('/api/v1/test-observability-error/{driver}', static function (): never {
            throw new RuntimeException('Sensitive exception detail');
        });

        $this->getJson('/api/v1/test-observability-error/private-driver?token=private-token')
            ->assertInternalServerError()
            ->assertHeader('X-Request-ID')
            ->assertJsonMissing(['Sensitive exception detail', 'private-driver', 'private-token']);

        Log::shouldHaveReceived('log')->withArgs(
            static function (string $level, string $message, array $context): bool {
                return $level === 'error'
                    && $message === 'http.request_failed'
                    && $context['http_method'] === 'GET'
                    && $context['route'] === 'api/v1/test-observability-error/{driver}'
                    && $context['status_code'] === 500
                    && ($context['exception_class'] ?? null) === RuntimeException::class
                    && ! str_contains(json_encode($context, JSON_THROW_ON_ERROR), 'private');
            },
        )->once();

        Log::shouldNotHaveReceived('error');
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Support\DependencyHealthChecker;
use Mockery\MockInterface;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_reports_the_application_as_healthy(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('X-Request-ID')
            ->assertExactJson([
                'success' => true,
                'data' => [
                    'status' => 'healthy',
                    'application' => 'RaceStreak',
                    'version' => '0.1.0',
                    'checks' => [
                        'database' => [
                            'status' => 'healthy',
                        ],
                    ],
                ],
            ]);
    }

    public function test_health_endpoint_reports_dependency_failures_safely(): void
    {
        $this->mock(DependencyHealthChecker::class, function (MockInterface $mock): void {
            $mock->shouldReceive('check')->once()->andReturn([
                'healthy' => false,
                'checks' => [
                    'database' => [
                        'status' => 'unhealthy',
                    ],
                ],
            ]);
        });

        $this->getJson('/api/v1/health?password=do-not-log-or-return')
            ->assertServiceUnavailable()
            ->assertHeader('X-Request-ID')
            ->assertJsonMissing(['do-not-log-or-return'])
            ->assertExactJson([
                'success' => false,
                'error' => [
                    'code' => 'SERVICE_UNAVAILABLE',
                    'message' => 'The service is temporarily unavailable.',
                    'details' => [
                        'status' => 'unhealthy',
                        'checks' => [
                            'database' => [
                                'status' => 'unhealthy',
                            ],
                        ],
                    ],
                ],
            ]);
    }
}

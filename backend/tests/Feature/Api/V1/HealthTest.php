<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_reports_the_application_as_healthy(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson([
                'success' => true,
                'data' => [
                    'status' => 'healthy',
                    'application' => 'RaceStreak',
                    'version' => '0.1.0',
                ],
            ]);
    }
}

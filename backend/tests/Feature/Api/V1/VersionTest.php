<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class VersionTest extends TestCase
{
    public function test_version_endpoint_returns_the_current_application_version(): void
    {
        $this->getJson('/api/v1/version')
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => [
                    'version' => '0.1.0',
                ],
            ]);
    }
}

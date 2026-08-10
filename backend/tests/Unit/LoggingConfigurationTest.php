<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Log;
use Monolog\Formatter\JsonFormatter;
use Tests\TestCase;

class LoggingConfigurationTest extends TestCase
{
    public function test_stderr_uses_json_with_environment_aware_context(): void
    {
        $this->assertSame(JsonFormatter::class, config('logging.channels.stderr.formatter'));
        $this->assertSame('debug', config('logging.channels.stderr.level'));
        $this->assertSame([
            'application' => 'RaceStreak',
            'application_version' => '0.1.0',
            'environment' => 'testing',
        ], array_intersect_key(Log::sharedContext(), array_flip([
            'application',
            'application_version',
            'environment',
        ])));
    }
}

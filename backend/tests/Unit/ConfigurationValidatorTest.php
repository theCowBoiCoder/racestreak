<?php

namespace Tests\Unit;

use App\Support\ConfigurationValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConfigurationValidatorTest extends TestCase
{
    #[Test]
    public function it_accepts_complete_application_configuration(): void
    {
        (new ConfigurationValidator)->validate([
            'APP_NAME' => 'RaceStreak',
            'APP_VERSION' => '0.1.0',
            'APP_TIMEZONE' => 'UTC',
            'APP_URL' => 'http://localhost:8000',
        ]);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_reports_missing_configuration_names_without_values(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing required application configuration: APP_VERSION.');

        (new ConfigurationValidator)->validate([
            'APP_NAME' => 'RaceStreak',
            'APP_VERSION' => '',
            'APP_TIMEZONE' => 'UTC',
            'APP_URL' => 'http://localhost:8000',
        ]);
    }
}

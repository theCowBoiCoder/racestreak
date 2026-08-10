<?php

namespace Tests\Unit;

use App\Support\DependencyHealthChecker;
use Illuminate\Database\DatabaseManager;
use Mockery;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tests\TestCase;

class DependencyHealthCheckerTest extends TestCase
{
    public function test_database_failure_is_reported_without_exception_details(): void
    {
        config(['monitoring.health.dependencies' => ['database']]);

        $database = Mockery::mock(DatabaseManager::class);
        $database->shouldReceive('connection')
            ->once()
            ->andThrow(new RuntimeException('postgres://username:password@database/racestreak'));

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('warning')
            ->once()
            ->with('health.dependency_failed', [
                'dependency' => 'database',
                'exception_class' => RuntimeException::class,
            ]);

        $result = (new DependencyHealthChecker($database, $logger))->check();

        $this->assertSame([
            'healthy' => false,
            'checks' => [
                'database' => [
                    'status' => 'unhealthy',
                ],
            ],
        ], $result);
    }
}

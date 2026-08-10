<?php

namespace App\Support;

use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Throwable;

class DependencyHealthChecker
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return array{healthy: bool, checks: array<string, array{status: 'healthy'|'unhealthy'}>}
     */
    public function check(): array
    {
        $checks = [];

        foreach (config('monitoring.health.dependencies', []) as $dependency) {
            $checks[$dependency] = match ($dependency) {
                'database' => $this->checkDatabase(),
                default => $this->unsupportedDependency($dependency),
            };
        }

        return [
            'healthy' => array_all(
                $checks,
                static fn (array $check): bool => $check['status'] === 'healthy',
            ),
            'checks' => $checks,
        ];
    }

    /**
     * @return array{status: 'healthy'|'unhealthy'}
     */
    private function checkDatabase(): array
    {
        try {
            $this->database->connection()->getPdo();
            $this->database->select('select 1');
        } catch (Throwable $exception) {
            $this->logger->warning('health.dependency_failed', [
                'dependency' => 'database',
                'exception_class' => $exception::class,
            ]);

            return ['status' => 'unhealthy'];
        }

        return ['status' => 'healthy'];
    }

    /**
     * @return array{status: 'unhealthy'}
     */
    private function unsupportedDependency(string $dependency): array
    {
        $this->logger->error('health.dependency_not_supported', [
            'dependency' => $dependency,
        ]);

        return ['status' => 'unhealthy'];
    }
}

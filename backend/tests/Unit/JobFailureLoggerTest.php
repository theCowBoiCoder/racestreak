<?php

namespace Tests\Unit;

use App\Support\JobFailureLogger;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Mockery;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tests\TestCase;

class JobFailureLoggerTest extends TestCase
{
    public function test_failed_job_log_contains_diagnostics_without_payload_or_exception_message(): void
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('getQueue')->once()->andReturn('default');
        $job->shouldReceive('resolveName')->once()->andReturn('App\\Jobs\\ImportRaceResult');
        $job->shouldReceive('uuid')->once()->andReturn('job-123');
        $job->shouldReceive('attempts')->once()->andReturn(3);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
            ->with('queue.job_failed', [
                'connection' => 'database',
                'queue' => 'default',
                'job_name' => 'App\\Jobs\\ImportRaceResult',
                'job_id' => 'job-123',
                'attempts' => 3,
                'exception_class' => RuntimeException::class,
            ]);

        $event = new JobFailed(
            'database',
            $job,
            new RuntimeException('Sensitive job payload detail'),
        );

        (new JobFailureLogger($logger))($event);
    }
}

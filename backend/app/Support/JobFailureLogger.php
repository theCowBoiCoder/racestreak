<?php

namespace App\Support;

use Illuminate\Queue\Events\JobFailed;
use Psr\Log\LoggerInterface;

final class JobFailureLogger
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(JobFailed $event): void
    {
        $this->logger->error('queue.job_failed', [
            'connection' => $event->connectionName,
            'queue' => $event->job->getQueue(),
            'job_name' => $event->job->resolveName(),
            'job_id' => $event->job->uuid() ?? $event->job->getJobId(),
            'attempts' => $event->job->attempts(),
            'exception_class' => $event->exception::class,
        ]);
    }
}

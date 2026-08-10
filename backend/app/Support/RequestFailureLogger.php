<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RequestFailureLogger
{
    public const LOGGED_ATTRIBUTE = 'request_failure_logged';

    public function log(
        Request $request,
        int $statusCode,
        ?Throwable $exception = null,
        ?float $durationMilliseconds = null,
    ): void {
        $context = [
            'http_method' => $request->method(),
            'route' => $request->route()?->uri() ?? 'unmatched',
            'status_code' => $statusCode,
        ];

        if ($durationMilliseconds !== null) {
            $context['duration_ms'] = round($durationMilliseconds, 2);
        }

        if ($exception !== null) {
            $context['exception_class'] = $exception::class;
        }

        Log::log(
            $statusCode >= 500 ? 'error' : 'warning',
            'http.request_failed',
            $context,
        );

        $request->attributes->set(self::LOGGED_ATTRIBUTE, true);
    }
}

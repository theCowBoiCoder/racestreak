<?php

namespace App\Http\Middleware;

use App\Support\RequestFailureLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class RequestContext
{
    public const ATTRIBUTE = 'request_id';

    public const HEADER = 'X-Request-ID';

    public function __construct(
        private readonly RequestFailureLogger $failureLogger,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->requestId($request);
        $request->attributes->set(self::ATTRIBUTE, $requestId);

        Log::shareContext(['request_id' => $requestId]);

        $startedAt = hrtime(true);
        $response = $next($request);

        $response->headers->set(self::HEADER, $requestId);

        if ($response->getStatusCode() >= 400
            && ! $request->attributes->get(RequestFailureLogger::LOGGED_ATTRIBUTE, false)) {
            $this->failureLogger->log(
                $request,
                $response->getStatusCode(),
                durationMilliseconds: (hrtime(true) - $startedAt) / 1_000_000,
            );
        }

        return $response;
    }

    private function requestId(Request $request): string
    {
        $candidate = $request->header(self::HEADER);

        if (is_string($candidate) && preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,127}\z/D', $candidate) === 1) {
            return $candidate;
        }

        return Str::uuid()->toString();
    }
}

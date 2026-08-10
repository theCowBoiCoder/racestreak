<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

final class DriverLoginRateLimiter
{
    public const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function key(Request $request, string $email): string
    {
        return 'driver-login:'.hash('sha256', $email.'|'.$request->ip());
    }

    public function tooManyAttempts(string $key): bool
    {
        return RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS);
    }

    public function hit(string $key): void
    {
        RateLimiter::hit($key, self::DECAY_SECONDS);
    }

    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }

    public function response(string $key): JsonResponse
    {
        $retryAfter = max(1, RateLimiter::availableIn($key));

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'message' => 'The request rate limit has been exceeded.',
            ],
        ], 429, [
            'Retry-After' => (string) $retryAfter,
            'RateLimit-Limit' => (string) self::MAX_ATTEMPTS,
            'RateLimit-Remaining' => '0',
            'RateLimit-Reset' => (string) $retryAfter,
        ]);
    }
}

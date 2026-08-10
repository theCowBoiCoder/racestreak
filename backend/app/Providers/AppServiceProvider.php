<?php

namespace App\Providers;

use App\Support\ConfigurationValidator;
use App\Support\JobFailureLogger;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app(ConfigurationValidator::class)->validate(
            config('app.required_environment'),
        );

        Log::shareContext([
            'application' => config('app.name'),
            'application_version' => config('app.version'),
            'environment' => app()->environment(),
        ]);

        RateLimiter::for('driver-registration', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));
            $key = hash('sha256', $email.'|'.$request->ip());

            return Limit::perMinute(5)
                ->by($key)
                ->response(function (Request $request, array $headers): JsonResponse {
                    $retryAfter = (string) ($headers['Retry-After'] ?? 60);

                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'RATE_LIMIT_EXCEEDED',
                            'message' => 'The request rate limit has been exceeded.',
                        ],
                    ], 429, [
                        ...$headers,
                        'RateLimit-Limit' => '5',
                        'RateLimit-Remaining' => '0',
                        'RateLimit-Reset' => $retryAfter,
                    ]);
                });
        });

        Queue::failing(app(JobFailureLogger::class));
    }
}

<?php

namespace App\Providers;

use App\Support\ConfigurationValidator;
use App\Support\JobFailureLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

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

        Queue::failing(app(JobFailureLogger::class));
    }
}

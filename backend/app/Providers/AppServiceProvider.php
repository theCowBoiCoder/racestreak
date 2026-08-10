<?php

namespace App\Providers;

use App\Support\ConfigurationValidator;
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
    }
}

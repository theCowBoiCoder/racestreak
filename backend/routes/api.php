<?php

use App\Http\Controllers\Api\V1\AuthenticationSessionController;
use App\Http\Controllers\Api\V1\DriverAccountController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\VersionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/driver-accounts', [DriverAccountController::class, 'store'])
        ->middleware('throttle:driver-registration')
        ->name('api.v1.driver-accounts.store');
    Route::get('/health', HealthController::class)->name('api.v1.health');
    Route::get('/version', VersionController::class)->name('api.v1.version');

    Route::middleware('web')->prefix('authentication')->group(function (): void {
        Route::get('/csrf-token', [AuthenticationSessionController::class, 'csrfToken'])
            ->name('api.v1.authentication.csrf-token');
        Route::post('/session', [AuthenticationSessionController::class, 'store'])
            ->name('api.v1.authentication.session.store');

        Route::middleware('auth:web')->group(function (): void {
            Route::get('/session', [AuthenticationSessionController::class, 'show'])
                ->name('api.v1.authentication.session.show');
            Route::delete('/session', [AuthenticationSessionController::class, 'destroy'])
                ->name('api.v1.authentication.session.destroy');
        });
    });
});

<?php

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
});

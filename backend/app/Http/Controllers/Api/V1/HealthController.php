<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\DependencyHealthChecker;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(DependencyHealthChecker $healthChecker): JsonResponse
    {
        $health = $healthChecker->check();

        if (! $health['healthy']) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SERVICE_UNAVAILABLE',
                    'message' => 'The service is temporarily unavailable.',
                    'details' => [
                        'status' => 'unhealthy',
                        'checks' => $health['checks'],
                    ],
                ],
            ], 503);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => 'healthy',
                'application' => config('app.name'),
                'version' => config('app.version'),
                'checks' => $health['checks'],
            ],
        ]);
    }
}

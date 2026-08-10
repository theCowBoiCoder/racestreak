<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAuthenticationSessionRequest;
use App\Http\Resources\Api\V1\DriverAccountResource;
use App\Models\User;
use App\Support\DriverLoginRateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthenticationSessionController extends Controller
{
    public function csrfToken(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'token' => $request->session()->token(),
            ],
        ])->header('Cache-Control', 'no-store');
    }

    public function store(
        StoreAuthenticationSessionRequest $request,
        DriverLoginRateLimiter $rateLimiter,
    ): JsonResponse {
        $credentials = $request->safe()->only(['email', 'password']);
        $email = $credentials['email'];
        $rateLimitKey = $rateLimiter->key($request, $email);

        if ($rateLimiter->tooManyAttempts($rateLimitKey)) {
            Log::warning('auth.login_rate_limited', [
                'email_fingerprint' => hash('sha256', $email),
            ]);

            return $rateLimiter->response($rateLimitKey);
        }

        if (! Auth::guard('web')->attempt($credentials)) {
            $rateLimiter->hit($rateLimitKey);

            Log::warning('auth.login_failed', [
                'email_fingerprint' => hash('sha256', $email),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'The provided credentials are invalid.',
                ],
            ], 401);
        }

        $rateLimiter->clear($rateLimitKey);
        $request->session()->regenerate();

        /** @var User $driver */
        $driver = Auth::guard('web')->user();

        Log::info('auth.login_succeeded', [
            'driver_account_id' => $driver->public_id,
        ]);

        return response()->json([
            'success' => true,
            'data' => DriverAccountResource::make($driver)->resolve($request),
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        /** @var User $driver */
        $driver = $request->user('web');

        return response()->json([
            'success' => true,
            'data' => DriverAccountResource::make($driver)->resolve($request),
        ])->header('Cache-Control', 'no-store');
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var User $driver */
        $driver = $request->user('web');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('auth.logout_succeeded', [
            'driver_account_id' => $driver->public_id,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Signed out.',
            ],
        ])->header('Cache-Control', 'no-store');
    }
}

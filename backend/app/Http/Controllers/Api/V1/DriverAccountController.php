<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDriverAccountRequest;
use App\Http\Resources\Api\V1\DriverAccountResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;

class DriverAccountController extends Controller
{
    public function store(StoreDriverAccountRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $driver = User::query()->create([
            'name' => $validated['display_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        event(new Registered($driver));

        $location = sprintf('/api/v1/driver-accounts/%s', $driver->public_id);

        return response()->json([
            'success' => true,
            'data' => DriverAccountResource::make($driver)->resolve($request),
        ], 201, ['Location' => $location]);
    }
}

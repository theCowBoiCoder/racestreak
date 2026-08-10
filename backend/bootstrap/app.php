<?php

use Illuminate\Foundation\Application;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $exception instanceof AuthenticationException => 401,
                $exception instanceof AuthorizationException => 403,
                $exception instanceof ValidationException => 422,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };

            $error = [
                'code' => match ($status) {
                    400 => 'BAD_REQUEST',
                    401 => 'UNAUTHENTICATED',
                    403 => 'FORBIDDEN',
                    404 => 'NOT_FOUND',
                    405 => 'METHOD_NOT_ALLOWED',
                    409 => 'CONFLICT',
                    422 => 'VALIDATION_ERROR',
                    429 => 'RATE_LIMIT_EXCEEDED',
                    503 => 'SERVICE_UNAVAILABLE',
                    default => 'INTERNAL_ERROR',
                },
                'message' => match ($status) {
                    400 => 'The request is invalid.',
                    401 => 'Authentication is required.',
                    403 => 'You are not allowed to perform this action.',
                    404 => 'The requested resource was not found.',
                    405 => 'The requested method is not allowed.',
                    409 => 'The request conflicts with the current resource state.',
                    422 => 'The request could not be validated.',
                    429 => 'The request rate limit has been exceeded.',
                    503 => 'The service is temporarily unavailable.',
                    default => 'An unexpected error occurred.',
                },
            ];

            if ($exception instanceof ValidationException) {
                $error['details'] = [
                    'fields' => $exception->errors(),
                ];
            }

            return response()->json([
                'success' => false,
                'error' => $error,
            ], $status);
        });
    })->create();

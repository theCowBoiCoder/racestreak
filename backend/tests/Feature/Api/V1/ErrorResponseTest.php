<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorResponseTest extends TestCase
{
    public function test_unknown_api_routes_return_the_standard_error_format(): void
    {
        $this->getJson('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertExactJson([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'The requested resource was not found.',
                ],
            ]);
    }

    public function test_unhandled_api_errors_do_not_expose_internal_details(): void
    {
        Route::get('/api/v1/test-error', static function (): never {
            throw new RuntimeException('Sensitive internal detail');
        });

        $this->getJson('/api/v1/test-error')
            ->assertInternalServerError()
            ->assertJsonMissing(['Sensitive internal detail'])
            ->assertExactJson([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'An unexpected error occurred.',
                ],
            ]);
    }

    public function test_unsupported_methods_return_the_standard_error_format(): void
    {
        $this->postJson('/api/v1/health')
            ->assertMethodNotAllowed()
            ->assertExactJson([
                'success' => false,
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'The requested method is not allowed.',
                ],
            ]);
    }

    public function test_validation_errors_return_field_details_in_the_standard_format(): void
    {
        Route::post('/api/v1/test-validation', static function (): array {
            request()->validate([
                'driver_name' => ['required', 'string'],
            ]);

            return [];
        });

        $this->postJson('/api/v1/test-validation')
            ->assertUnprocessable()
            ->assertExactJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The request could not be validated.',
                    'details' => [
                        'fields' => [
                            'driver_name' => [
                                'The driver name field is required.',
                            ],
                        ],
                    ],
                ],
            ]);
    }
}

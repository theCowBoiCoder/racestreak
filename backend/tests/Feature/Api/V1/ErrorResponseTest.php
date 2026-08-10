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
}

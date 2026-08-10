<?php

namespace App\Support;

use RuntimeException;

final class ConfigurationValidator
{
    /**
     * @param  array<string, mixed>  $configuration
     */
    public function validate(array $configuration): void
    {
        $missing = array_keys(array_filter(
            $configuration,
            static fn (mixed $value): bool => $value === null || $value === '',
        ));

        if ($missing === []) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Missing required application configuration: %s.',
            implode(', ', $missing),
        ));
    }
}

# Database conventions

PostgreSQL is the primary RaceStreak database. SQLite in memory is reserved for isolated automated tests.

## Migrations

- Every schema change must use a new migration; do not edit a migration that has been shared.
- Migrations must provide a safe `down` operation.
- Avoid product data changes inside schema migrations.
- Validate both migration and rollback behavior before publishing a change.

## Seeders

- Register seeders from `DatabaseSeeder`.
- Seeders must be deterministic and safe to run more than once.
- Never include production credentials or personal data.
- Use model factories for test-only records.

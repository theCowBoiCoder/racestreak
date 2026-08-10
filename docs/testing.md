# Automated testing

RaceStreak tests must be repeatable, isolated from developer data and safe to run in CI. The standard suite runs entirely in Docker and requires no host PHP, Composer, Node.js, npm or PostgreSQL installation.

## Standard commands

Run the complete backend and frontend suite from the repository root:

```powershell
.\scripts\dev.ps1 test
```

```sh
./scripts/dev.sh test
```

Both helpers rebuild the dedicated test images before running them. This guarantees that newly added or changed files are included. A failed build or test returns a non-zero exit code and stops the command.

Run a suite directly when diagnosing a failure:

```sh
docker compose --profile tools build backend-test frontend-test
docker compose --profile tools run --rm backend-test
docker compose --profile tools run --rm frontend-test
```

Inside an installed backend environment, `composer test`, `composer test:unit` and `composer test:feature` run all, unit-only and feature-only tests. Inside an installed frontend environment, use `npm test`, `npm run test:unit` or `npm run test:watch`.

## Test types and locations

### Backend

- `backend/tests/Unit`: pure PHP behavior with no Laravel application, network or database dependency.
- `backend/tests/Feature`: Laravel application, API and integration behavior.
- `backend/tests/Feature/Api/V1`: HTTP contract tests for `/api/v1` endpoints.
- `backend/tests/Feature/Database`: migrations, factories and database integration.

Use PHPUnit names that describe observable behavior. New API endpoints require tests for their successful response, validation failures, authentication and authorisation where applicable, and missing resources.

### Frontend

- `frontend/test/unit`: Vitest component and composable tests.
- `frontend/test/fixtures`: typed, reusable test data with no hidden state.

Test the behavior a user can observe. Mock the backend boundary and assert loading, success, empty and error states where the feature can produce them.

## Isolation and repeatability

- Backend tests force the `testing` environment, UTC timezone, English locale, array cache/session drivers, synchronous queues and an in-memory SQLite database through `phpunit.xml`.
- Database tests use Laravel's `RefreshDatabase` trait. Never connect automated tests to the local PostgreSQL service or a shared database.
- Create records with model factories. Override attributes that matter to the assertion instead of depending on random Faker output.
- Frontend tests run in `happy-dom`; Vitest resets and restores mocks between tests. Treat fixtures as read-only inputs and never depend on execution order.
- Do not call real external APIs. Fake HTTP clients, queues, mail, notifications, storage and time at the boundary relevant to the test.
- Freeze the clock when behavior depends on the current time. Use explicit UTC timestamps in assertions.
- A test must pass alone and as part of the complete suite. Do not rely on another test creating data or changing global state.

## Representative coverage

The baseline suite demonstrates:

- pure backend unit testing through the configuration validator;
- versioned API success, error, method and validation contracts;
- migration and rollback behavior;
- isolated factory-backed database records;
- frontend component loading, success, empty, error and retry behavior.

Coverage percentage thresholds are intentionally deferred until product modules exist. Every change still needs focused tests for its important success and failure paths.

## CI expectations

The test services are non-interactive, contain all development dependencies and use only disposable in-container state, making the same commands suitable for CI. CI must treat any non-zero build or test result as a failed check and should not inject production credentials.

When a test fails, reproduce it with the relevant direct command, fix the cause and then rerun the complete helper command before publishing the change.

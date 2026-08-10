# RaceStreak API

The RaceStreak API is a Laravel application. It provides the versioned API, safe JSON responses, configuration validation, health monitoring and driver-account capabilities.

## Required configuration

Copy `.env.example` to `.env` when running Laravel outside Docker. These values are required at startup:

- `APP_NAME`
- `APP_VERSION`
- `APP_TIMEZONE`
- `APP_URL`

Do not commit `.env` or real application keys.

## API endpoints

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `POST` | `/api/v1/driver-accounts` | Register a driver account |
| `GET` | `/api/v1/health` | Application health, name and version |
| `GET` | `/api/v1/version` | Current API/application version |

Successful responses use `{ "success": true, "data": { ... } }`. API failures use `{ "success": false, "error": { "code": "...", "message": "..." } }`.

Registration accepts `display_name`, `email`, `password` and `password_confirmation`. The password policy, rate limit and full response contract are documented in the [API v1 standards](../docs/api/v1/standards.md).

## Run tests

From the repository root:

```sh
docker compose run --rm -e APP_ENV=testing -e LOG_CHANNEL=null -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: backend php artisan test
```

The test environment uses SQLite and does not require an external database service.

## Database commands

The Docker stack uses PostgreSQL. From the repository root:

```sh
docker compose exec backend php artisan db:check
docker compose exec backend php artisan migrate:status
docker compose exec backend php artisan migrate
docker compose exec backend php artisan migrate:rollback
docker compose exec backend php artisan db:seed
```

Automated tests always use an isolated in-memory SQLite database. Migration and seeding conventions are documented in `database/README.md`.

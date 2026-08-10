# RaceStreak API

The RaceStreak API is a Laravel application. PF-001 establishes the versioned API, health and version endpoints, safe JSON error responses, required configuration validation, and automated endpoint tests.

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
| `GET` | `/api/v1/health` | Application health, name and version |
| `GET` | `/api/v1/version` | Current API/application version |

Successful responses use `{ "success": true, "data": { ... } }`. API failures use `{ "success": false, "error": { "code": "...", "message": "..." } }`.

## Run tests

From the repository root:

```sh
docker compose run --rm -e APP_ENV=testing -e LOG_CHANNEL=null backend php artisan test
```

The test environment uses SQLite and does not require an external database service.

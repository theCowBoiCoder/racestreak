# RaceStreak

RaceStreak is a rewards, progression and engagement platform for community sim racing. Every race counts.

This repository currently contains the PF-001 platform foundation:

- Laravel API in `backend/`
- Nuxt web application in `frontend/`
- PostgreSQL database
- Docker Compose stack for consistent local execution

## Prerequisites

- Docker Desktop with Docker Compose

No host installation of PHP, Composer, Node.js or npm is required to run the containerized stack.

## Start RaceStreak

```sh
docker compose up --build
```

Once both services are healthy:

- Nuxt frontend: <http://localhost:3000>
- Laravel API health: <http://localhost:8000/api/v1/health>
- Laravel API version: <http://localhost:8000/api/v1/version>

Stop the stack with `docker compose down`.

PostgreSQL data is retained in the `postgres-data` Docker volume. Use `docker compose down -v` only when you intentionally want to delete the local database and start again from empty migrations.

## Database commands

```sh
docker compose exec backend php artisan db:check
docker compose exec backend php artisan migrate:status
docker compose exec backend php artisan migrate
docker compose exec backend php artisan migrate:rollback
docker compose exec backend php artisan db:seed
```

The checked-in database password is a local-only Docker default. Set `DB_PASSWORD` before starting Compose to override it; never reuse the local value in a deployed environment.

## Run the backend tests

```sh
docker compose run --rm -e APP_ENV=testing -e LOG_CHANNEL=null -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: backend php artisan test
```

## Run the frontend tests

```sh
docker run --rm -v "${PWD}/frontend:/app" -w /app node:22-alpine sh -lc "npm install --global npm@11 && npm ci && npm test"
```

## Configuration

Docker Compose supplies the required development values. When running either application outside Docker, copy the relevant `.env.example` file and adjust it locally.

Laravel validates `APP_NAME`, `APP_VERSION`, `APP_TIMEZONE` and `APP_URL` during startup. Missing values produce a configuration error naming the missing variable without displaying secret values.

## Initial API format

Successful responses:

```json
{
  "success": true,
  "data": {}
}
```

Failed API responses:

```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "An unexpected error occurred."
  }
}
```

PF-001 deliberately excludes authentication, product database entities, integrations, XP, challenges, rewards and deployment automation.

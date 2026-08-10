# RaceStreak

RaceStreak is a rewards, progression and engagement platform for community sim racing. Every race counts.

This repository contains the RaceStreak platform foundation:

- Laravel API in `backend/`
- Nuxt web application in `frontend/`
- PostgreSQL database
- Docker Compose stack for consistent local execution

## Prerequisites

- Docker Desktop with Docker Compose

No host installation of PHP, Composer, Node.js or npm is required to run the containerized stack.

## Clean-clone setup

Clone the repository and enter it:

```sh
git clone https://github.com/theCowBoiCoder/racestreak.git
cd racestreak
```

Start Docker Desktop, then use the helper for your operating system:

```powershell
.\scripts\dev.ps1 start
```

```sh
./scripts/dev.sh start
```

The first start builds every image, creates PostgreSQL, runs pending migrations and starts the Laravel and Nuxt applications. No host installation of PHP, Composer, Node.js, npm or PostgreSQL is required.

## Development commands

| Action | PowerShell | macOS/Linux |
| --- | --- | --- |
| Start or rebuild | `.\scripts\dev.ps1 start` | `./scripts/dev.sh start` |
| Show service status | `.\scripts\dev.ps1 status` | `./scripts/dev.sh status` |
| Follow service logs | `.\scripts\dev.ps1 logs` | `./scripts/dev.sh logs` |
| Run all tests | `.\scripts\dev.ps1 test` | `./scripts/dev.sh test` |
| Stop services | `.\scripts\dev.ps1 stop` | `./scripts/dev.sh stop` |
| Delete local data and start fresh | `.\scripts\dev.ps1 reset -Force` | `./scripts/dev.sh reset --yes` |

The reset command permanently deletes the local PostgreSQL Docker volume. Normal stop/start commands preserve it.

## Application URLs

```sh
docker compose up --build
```

Once both services are healthy:

- Nuxt frontend: <http://localhost:3000>
- Laravel API health: <http://localhost:8000/api/v1/health>
- Laravel API version: <http://localhost:8000/api/v1/version>

PostgreSQL is available to local database clients at `localhost:5432` by default.

## Database commands

```sh
docker compose exec backend php artisan db:check
docker compose exec backend php artisan migrate:status
docker compose exec backend php artisan migrate
docker compose exec backend php artisan migrate:rollback
docker compose exec backend php artisan db:seed
```

The checked-in database password is a local-only Docker default. Set `DB_PASSWORD` before starting Compose to override it; never reuse the local value in a deployed environment.

## Run tests directly

```sh
docker compose --profile tools run --rm backend-test
docker compose --profile tools run --rm frontend-test
```

## Configuration

Docker Compose supplies safe local defaults. Copy the root `.env.example` to `.env` only when you need to override the database password or forwarded port. When running either application outside Docker, copy its own `.env.example` and adjust it locally.

Laravel validates `APP_NAME`, `APP_VERSION`, `APP_TIMEZONE` and `APP_URL` during startup. Missing values produce a configuration error naming the missing variable without displaying secret values.

## API standards

All `/api/v1` endpoints follow the versioned [RaceStreak API v1 standards](docs/api/v1/standards.md). The contract covers naming, HTTP semantics, response and validation formats, pagination, timestamps, identifiers, authentication, request IDs and rate limiting. It also includes a checklist for every new endpoint.

### Response summary

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

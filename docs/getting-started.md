# Getting started

This guide takes a new developer or coding agent from a clean clone to a working RaceStreak development environment. Docker is the supported runtime, so no host installation of PHP, Composer, Node.js, npm or PostgreSQL is needed.

## Prerequisites

- Git
- Docker Desktop, or Docker Engine with the Compose plugin
- Windows PowerShell, or a POSIX-compatible shell on macOS/Linux
- access to clone `theCowBoiCoder/racestreak`

Allow Docker enough memory to build the Laravel and Nuxt images together. Start Docker before running any helper command.

## Clone and start

```sh
git clone https://github.com/theCowBoiCoder/racestreak.git
cd racestreak
```

On Windows PowerShell:

```powershell
.\scripts\dev.ps1 start
```

On macOS or Linux:

```sh
./scripts/dev.sh start
```

The first start builds the images, creates a local PostgreSQL volume, applies pending Laravel migrations and starts all three services. Later starts preserve the database volume.

## Verify the stack

Check that the containers are running and healthy:

```powershell
.\scripts\dev.ps1 status
```

```sh
./scripts/dev.sh status
```

Then open:

- Nuxt frontend: <http://localhost:3000>
- Laravel readiness: <http://localhost:8000/api/v1/health>
- Laravel version: <http://localhost:8000/api/v1/version>

The frontend should report a healthy backend. The readiness response should report both the application and database as healthy.

## Configuration

Docker Compose provides safe development defaults, so no environment file is required for a normal first start.

The root `.env.example` contains optional Compose overrides:

| Variable | Purpose | Default |
| --- | --- | --- |
| `DB_PASSWORD` | Password shared by the local backend and PostgreSQL containers | local development value |
| `DB_PORT_FORWARD` | PostgreSQL port exposed on the host | `5432` |

Copy it only when an override is needed:

```powershell
Copy-Item .env.example .env
```

```sh
cp .env.example .env
```

The application-specific examples, `backend/.env.example` and `frontend/.env.example`, are for running an application outside Compose or understanding its supported variables. Never commit `.env` files, application keys, access tokens or production credentials.

Laravel requires `APP_NAME`, `APP_VERSION`, `APP_TIMEZONE` and `APP_URL`. The frontend uses `NUXT_BACKEND_BASE` on the server to reach the versioned Laravel API.

## Repository structure

| Path | Responsibility |
| --- | --- |
| `backend/` | Laravel API, application services, migrations and PHPUnit tests |
| `frontend/` | Nuxt web application, server proxy and Vitest tests |
| `docs/` | API, testing, quality, operations and architecture guidance |
| `scripts/` | Cross-platform helpers for the Docker development workflow |
| `.github/workflows/` | GitHub Actions continuous integration |
| `docker-compose.yml` | Local application, database and disposable tool services |

HTTP API routes belong under `backend/routes/api.php` and use the `/api/v1` prefix. Frontend pages, components and composables live under `frontend/app`.

## Normal development loop

1. Start from an up-to-date `main` branch and create a focused branch for one ticket.
2. Make the smallest change that satisfies the ticket and update affected documentation.
3. Add or update automated tests for important success and failure behavior.
4. Run the complete quality and test helpers.
5. Review the diff for secrets, generated files and unrelated changes.
6. Open a pull request linked to the GitHub issue and wait for both CI jobs to pass.

See [CONTRIBUTING.md](../CONTRIBUTING.md) for the full conventions and [commands.md](commands.md) for exact commands.

## Stop or reset

Stop the containers while preserving local database data:

```powershell
.\scripts\dev.ps1 stop
```

```sh
./scripts/dev.sh stop
```

Only use a reset when local data can be discarded. It permanently deletes the RaceStreak PostgreSQL Docker volume and starts a fresh stack:

```powershell
.\scripts\dev.ps1 reset -Force
```

```sh
./scripts/dev.sh reset --yes
```

If setup does not behave as described, use the [troubleshooting guide](troubleshooting.md) before changing project configuration.

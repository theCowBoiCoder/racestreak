# Troubleshooting

Start with the first error shown rather than changing several settings at once. Run commands from the repository root unless noted otherwise.

## Docker is unavailable

**Symptoms:** the helper reports that Docker is not available, cannot connect to the Docker daemon, or cannot find `docker compose`.

1. Start Docker Desktop or the Docker service.
2. Wait until the engine reports that it is running.
3. Confirm `docker info` and `docker compose version` succeed.
4. Rerun the helper command.

## A host port is already in use

**Symptoms:** startup fails while binding port `3000`, `8000` or `5432`.

- Stop the process or container already using the conflicting port.
- PostgreSQL alone can use another host port by copying `.env.example` to `.env` and changing `DB_PORT_FORWARD`.
- The containers communicate using their internal ports, so changing `DB_PORT_FORWARD` does not require a backend configuration change.
- Frontend and backend host ports are currently fixed in `docker-compose.yml`; changing them is a project configuration change and should be documented.

## A container is unhealthy or keeps restarting

Check status and then inspect logs:

```powershell
.\scripts\dev.ps1 status
.\scripts\dev.ps1 logs
```

```sh
./scripts/dev.sh status
./scripts/dev.sh logs
```

Common causes are a database that has not become healthy, invalid Laravel configuration, or another process occupying a port. The backend waits for PostgreSQL before running migrations and starting the server.

## The frontend cannot reach the backend

1. Open <http://localhost:8000/api/v1/health> directly.
2. Confirm the backend container is healthy.
3. Inside Compose, keep `NUXT_BACKEND_BASE=http://backend:8000/api/v1`; `localhost` inside the frontend container refers to that container, not Laravel.
4. If running Nuxt on the host, use `http://localhost:8000/api/v1` as shown in `frontend/.env.example`.
5. Rebuild the frontend after changing environment configuration.

## The database connection or readiness check fails

Run:

```sh
docker compose exec backend php artisan db:check
docker compose logs database backend
```

Confirm the backend and database use the same `DB_PASSWORD`. If disposable local data can be deleted, use the documented reset command to recreate the volume. Do not reset when the data must be preserved.

If the database port is only conflicting on the host, change `DB_PORT_FORWARD`; container-to-container connections still use `database:5432`.

## Migrations fail after switching branches

First inspect migration state:

```sh
docker compose exec backend php artisan migrate:status
```

Apply pending migrations with `php artisan migrate`. If a development branch left incompatible disposable data, reset the local stack. Never use `migrate:fresh` or the reset helper against a database containing data that must be retained.

## Tests or quality checks use stale files

Use the project helpers instead of reusing an old tool container. They rebuild `backend-test` and `frontend-test` before executing checks:

```powershell
.\scripts\dev.ps1 quality
.\scripts\dev.ps1 test
```

```sh
./scripts/dev.sh quality
./scripts/dev.sh test
```

If Docker build caching is suspected, rebuild the affected image with `docker compose build --no-cache backend-test` or `frontend-test`, then rerun the helper.

## Line-ending or formatting failures on Windows

Git uses the root `.gitattributes` to store text files with LF endings. Do not bypass it or rewrite unrelated files. Run the full quality helper and allow the configured formatter to update only the files in scope.

## GitHub Actions fails but local checks pass

Open the failed workflow, select the red `Backend` or `Frontend` job and expand the first failed step. CI also validates PostgreSQL migrations and the Nuxt production build, so reproduce that exact command from the [CI guide](ci.md). Confirm lock files are committed and no untracked local file is masking a missing dependency or configuration value.

## Get more detail

- Commands: [commands.md](commands.md)
- Tests: [testing.md](testing.md)
- Code quality: [code-quality.md](code-quality.md)
- CI: [ci.md](ci.md)
- Logging and health checks: [logging-and-monitoring.md](logging-and-monitoring.md)

When documenting a newly discovered setup failure, record the observable symptom, safe diagnostic steps, the cause and the verified recovery. Never paste credentials or connection strings into an issue or log excerpt.

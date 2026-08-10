# Command reference

Run these commands from the repository root unless a section says otherwise. The helper scripts are the supported interface because they keep Windows, macOS, Linux and CI behavior aligned.

## Local stack

| Action | Windows PowerShell | macOS/Linux |
| --- | --- | --- |
| Start or rebuild | `.\scripts\dev.ps1 start` | `./scripts/dev.sh start` |
| Show container status | `.\scripts\dev.ps1 status` | `./scripts/dev.sh status` |
| Follow application logs | `.\scripts\dev.ps1 logs` | `./scripts/dev.sh logs` |
| Stop and preserve data | `.\scripts\dev.ps1 stop` | `./scripts/dev.sh stop` |
| Delete local data and restart | `.\scripts\dev.ps1 reset -Force` | `./scripts/dev.sh reset --yes` |

`logs` follows the backend, frontend and database streams until interrupted. The reset command permanently deletes the local PostgreSQL Docker volume.

## Required checks

| Check | Windows PowerShell | macOS/Linux |
| --- | --- | --- |
| All backend and frontend tests | `.\scripts\dev.ps1 test` | `./scripts/dev.sh test` |
| Formatting, linting, static analysis and type checks | `.\scripts\dev.ps1 quality` | `./scripts/dev.sh quality` |

Run both commands before opening or updating a pull request. They rebuild disposable tool images so the results always use the current checkout.

## Database

The running backend container has the configured PostgreSQL connection:

```sh
docker compose exec backend php artisan db:check
docker compose exec backend php artisan migrate:status
docker compose exec backend php artisan migrate
docker compose exec backend php artisan migrate:rollback
docker compose exec backend php artisan db:seed
```

Use `migrate:fresh --force` only on a disposable local database because it drops all tables:

```sh
docker compose exec backend php artisan migrate:fresh --force
```

## Focused backend commands

Use the disposable backend test service when diagnosing a specific check:

```sh
docker compose --profile tools build backend-test
docker compose --profile tools run --rm backend-test php artisan test --testsuite=Unit
docker compose --profile tools run --rm backend-test php artisan test --testsuite=Feature
docker compose --profile tools run --rm backend-test ./vendor/bin/pint --test
docker compose --profile tools run --rm backend-test ./vendor/bin/phpstan analyse --memory-limit=1G
```

To apply backend formatting, run:

```sh
docker compose --profile tools run --rm backend-test ./vendor/bin/pint
```

## Focused frontend commands

Use the disposable frontend test service when diagnosing a specific check:

```sh
docker compose --profile tools build frontend-test
docker compose --profile tools run --rm frontend-test npm test
docker compose --profile tools run --rm frontend-test npm run format:check
docker compose --profile tools run --rm frontend-test npm run lint
docker compose --profile tools run --rm frontend-test npm run typecheck
docker compose --profile tools run --rm frontend-test npm run build
```

To apply automatic formatting and lint fixes, run:

```sh
docker compose --profile tools run --rm frontend-test npm run format
docker compose --profile tools run --rm frontend-test npm run lint:fix
```

Package script names are defined in `backend/composer.json` and `frontend/package.json`. Prefer the complete helper commands for the final verification even after a focused command passes.

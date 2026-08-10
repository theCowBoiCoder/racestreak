# Continuous integration

RaceStreak uses GitHub Actions to validate every pull request and every push to `main`. The workflow is defined in `.github/workflows/ci.yml` and runs from a clean Ubuntu environment without production credentials.

The root `.gitattributes` keeps text files on LF line endings so the same formatting checks pass on Windows, macOS and Linux.

## Required checks

The workflow runs two independent jobs so a failure identifies the affected application quickly.

### Backend

The `Backend` job:

1. starts a PostgreSQL 17 service;
2. installs PHP 8.4 and Composer dependencies;
3. checks Laravel formatting with Pint;
4. runs Larastan static analysis;
5. runs the backend automated tests; and
6. applies, rolls back and reapplies all migrations against PostgreSQL.

Composer's download cache is reused between runs. The installed `vendor` directory is deliberately recreated from `composer.lock` on every run.

### Frontend

The `Frontend` job:

1. installs Node.js 22 and dependencies with `npm ci`;
2. checks Prettier formatting, ESLint rules and TypeScript types;
3. runs the frontend automated tests; and
4. creates a Nuxt production build.

The npm download cache is reused between runs. The installed `node_modules` directory is deliberately recreated from `package-lock.json` on every run.

Any failed command fails its job and prevents the CI workflow from passing. Configure branch protection for `main` to require the `Backend` and `Frontend` checks before merging.

## Run the checks locally

Docker is the supported local environment. Before opening or updating a pull request, run:

```powershell
.\scripts\dev.ps1 quality
.\scripts\dev.ps1 test
```

On macOS or Linux, run:

```sh
./scripts/dev.sh quality
./scripts/dev.sh test
```

To exercise migrations against the local PostgreSQL container:

```sh
docker compose exec backend php artisan migrate:fresh --force
docker compose exec backend php artisan migrate:rollback --force
docker compose exec backend php artisan migrate --force
```

`migrate:fresh` deletes all tables in the selected database. Use it only with a disposable local or test database.

## Troubleshooting

Open the failed workflow run in GitHub, select the red job and expand the first failed step. The step names mirror the local checks:

- `Install backend dependencies` or `Install frontend dependencies`: confirm the relevant lock file is committed and matches its manifest.
- `Check backend formatting and static analysis`: run the local quality helper, apply Pint fixes where needed, then resolve any Larastan errors.
- `Check frontend formatting, linting and types`: run the local quality helper, or use `npm run format` and `npm run lint:fix` in the frontend container before resolving remaining type errors.
- `Run backend tests` or `Run frontend tests`: reproduce with the local test helper and inspect the failing test output.
- `Validate PostgreSQL migrations`: check that every migration can run both `up` and `down` on PostgreSQL and does not rely on existing local data.
- `Build production frontend`: run `npm run build` in the frontend container and resolve the first Nuxt build error.

Rerun a job after fixing transient infrastructure failures. Code, test, migration and lock-file failures must be fixed in a new commit rather than bypassed.

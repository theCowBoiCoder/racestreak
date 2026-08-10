# Architecture

RaceStreak is a Docker-first modular monorepo. The current foundation separates the Laravel API, Nuxt web application and PostgreSQL database while keeping their local workflow and CI together.

## System boundaries

| Component | Responsibility | Boundary |
| --- | --- | --- |
| Nuxt frontend | Browser experience and server-side calls to Laravel | Consumes the versioned API; does not access PostgreSQL directly |
| Laravel backend | API contracts, application behavior, validation and persistence | Owns database access and external integration boundaries |
| PostgreSQL | Durable relational application data | Accessed through Laravel migrations and configured connections |
| GitHub Actions | Independent backend and frontend validation | Recreates dependencies and test state from committed files |

The browser calls Nuxt. Nuxt server routes can proxy to Laravel using the private Compose service address. Laravel exposes versioned endpoints under `/api/v1` and is the only application service that connects to PostgreSQL.

## Repository boundaries

- `backend/app` contains Laravel application behavior; controllers should remain thin.
- `backend/routes/api.php` defines the versioned HTTP surface.
- `backend/database` owns migrations, factories and seeders.
- `frontend/app` contains pages, components and composables.
- `frontend/server` contains server-only Nuxt endpoints and backend communication.
- shared operational behavior belongs at the repository root in Compose, scripts, CI and documentation.

New product areas should be introduced as focused modules within these boundaries. A separate deployable service requires an explicit architectural decision rather than being added incidentally.

## Architectural principles

- Prefer explicit versioned contracts between frontend and backend.
- Keep secrets and internal service addresses on the server side.
- Use environment configuration for values that differ between environments.
- Make local workflows reproducible through containers and committed lock files.
- Keep tests isolated from shared services and production credentials.
- Add observability at system boundaries without logging sensitive values.
- Record significant choices and their trade-offs in an ADR.

## Decision records

Architecture decision records live in [`docs/architecture/decisions`](decisions/README.md). They preserve why a significant choice was made, not just the final implementation.

The first accepted record, [ADR-0001](decisions/0001-docker-first-modular-monorepo.md), documents the Docker-first modular monorepo foundation.

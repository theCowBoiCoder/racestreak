# ADR-0001: Use a Docker-first modular monorepo

- Status: Accepted
- Date: 2026-08-10
- Deciders: RaceStreak maintainers
- Supersedes: None
- Superseded by: None

## Context

RaceStreak begins with a Laravel API, a Nuxt web application and PostgreSQL. Contributors need a repeatable setup on Windows, macOS and Linux without separately managing compatible PHP, Composer, Node.js, npm and database installations.

The platform is early in development. Backend and frontend changes often share an API contract and need to be validated together, while the code should retain clear boundaries that allow future deployment decisions.

## Options considered

### Separate repositories and host-managed runtimes

This gives each application an independent history and release process, but duplicates project configuration, makes coordinated contract changes harder and requires every contributor to install matching runtimes.

### Monorepo with host-managed runtimes

This keeps coordinated changes together but still leaves setup dependent on host versions and platform-specific instructions.

### Docker-first modular monorepo

This keeps backend, frontend, local infrastructure, tests and documentation in one repository while preserving application directories and API boundaries. Containers define runtime versions and Docker Compose coordinates local services.

## Decision

Use a Docker-first modular monorepo. Keep Laravel in `backend/`, Nuxt in `frontend/`, shared developer tooling at the repository root and PostgreSQL as a Compose-managed dependency.

Treat the Laravel API as the frontend's application boundary and keep browser-facing code from accessing PostgreSQL directly. Maintain independent backend and frontend CI jobs so failures and future deployment boundaries remain visible.

## Consequences

### Positive

- A clean clone needs only Git and Docker for the supported workflow.
- API, frontend and infrastructure changes can be reviewed atomically.
- Runtime and dependency versions are captured in Dockerfiles and lock files.
- Shared scripts and CI reduce differences between contributor environments.
- Clear application directories preserve the option to deploy components independently later.

### Negative

- Docker image builds consume more time and disk space than some host-native workflows.
- Repository CI runs both application boundaries for coordinated validation.
- Contributors must understand which concerns belong in the backend, frontend or shared root.
- Splitting a component into another repository later would require an explicit migration.

## Validation

- The documented clean-clone workflow starts PostgreSQL, Laravel and Nuxt through Docker Compose.
- Cross-platform helpers run the complete test and quality suites.
- GitHub Actions validates backend and frontend independently from a clean checkout.
- The frontend communicates with Laravel through the versioned API and does not connect to PostgreSQL.

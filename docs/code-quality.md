# Code quality

RaceStreak uses automated formatting, linting and static analysis so code reviews can focus on behavior and architecture. The standard checks run in Docker and require no host PHP, Composer, Node.js or npm installation.

## Standard command

Run every backend and frontend quality check from the repository root:

```powershell
.\scripts\dev.ps1 quality
```

```sh
./scripts/dev.sh quality
```

The helpers rebuild the tool images first, then run Laravel Pint in check mode, Larastan/PHPStan, Prettier in check mode, ESLint and Nuxt TypeScript checking. Any violation returns a non-zero exit code and stops the command.

## Fixing violations

Backend formatting can be fixed inside an installed backend environment with:

```sh
composer format
```

Frontend formatting and automatically fixable lint rules can be fixed inside an installed frontend environment with:

```sh
npm run format
npm run lint:fix
```

Rerun the complete standard quality command after applying fixes. Static-analysis and type errors normally require a code change rather than an automatic rewrite.

## Backend standards

- Laravel Pint's `laravel` preset is authoritative for PHP formatting and import order.
- Larastan analyses application code at PHPStan level 6. New code must not add ignored errors or a baseline to bypass a finding.
- Classes, enums and traits use `PascalCase`; methods, properties and local variables use `camelCase`; constants use `UPPER_SNAKE_CASE`.
- Database columns, JSON fields and API query parameters use `snake_case`, matching the API v1 contract.
- Controllers remain thin. Put reusable business behavior in clearly named application or domain classes as those modules are introduced.
- Use declared parameter and return types. Use PHPDoc for useful generic or shaped types, not to repeat native types.

## Frontend standards

- Prettier is authoritative for Vue and TypeScript formatting.
- ESLint applies the Nuxt rules, rejects warnings, enforces type-only imports and sorts imports and exports deterministically.
- Vue component files and component names use `PascalCase`. Composables use a `useName` function and matching `useName.ts` filename.
- TypeScript types and interfaces use `PascalCase`; functions and variables use `camelCase`; true constants use `UPPER_SNAKE_CASE`.
- Unit test files use the `.spec.ts` suffix and describe user-observable behavior.
- Avoid `any`. Prefer precise types at API, component-prop and composable boundaries.

## Exclusions and generated files

Dependency folders, build output, caches and generated Nuxt files are excluded. Do not manually edit or format `vendor`, `node_modules`, `.nuxt`, `.output`, `dist`, coverage output or Composer/npm lock files. Dependency tools own lock-file formatting.

## Suppressions

Fix the underlying issue whenever practical. A lint or static-analysis suppression must be scoped to the smallest possible line, include a short reason, and be used only when the tool cannot understand valid framework behavior. Project-wide rule disabling requires an explicit architectural decision.

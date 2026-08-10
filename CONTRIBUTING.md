# Contributing to RaceStreak

RaceStreak changes are delivered as focused GitHub issues and pull requests. These conventions apply to human contributors and coding agents.

## Before making a change

1. Read the issue, its acceptance criteria and linked Epic.
2. Confirm the issue is not already covered by an open pull request.
3. Start from the latest `main` branch.
4. Create a short branch name that includes the ticket, such as `feature/pf-010-developer-documentation`.
5. Read the relevant guides in `docs/` before changing an established contract.

Keep work inside the issue scope. Preserve unrelated work in the checkout and do not combine opportunistic refactors with a feature or fix.

## Implementation conventions

- Follow the [API v1 standards](docs/api/v1/standards.md) for every endpoint.
- Follow the [testing](docs/testing.md) and [code-quality](docs/code-quality.md) conventions.
- Add a migration for every database schema change; never edit a migration that has already been shared.
- Keep secrets and personal data out of source, fixtures, logs, screenshots and pull-request text.
- Do not manually edit generated folders or dependency lock files unless the dependency change requires it.
- Update documentation in the same pull request when behavior, configuration, commands or architecture changes.
- Record a significant or difficult-to-reverse technical choice as an [architecture decision record](docs/architecture/decisions/README.md).

## Tests and verification

Add focused tests for important success and failure paths. From the repository root, run:

```powershell
.\scripts\dev.ps1 quality
.\scripts\dev.ps1 test
```

```sh
./scripts/dev.sh quality
./scripts/dev.sh test
```

For database changes, also verify that migrations apply, roll back and reapply against local PostgreSQL. For user-visible or API changes, smoke-test the relevant running application path.

## Commits and pull requests

- Write a concise, imperative commit subject that describes the outcome.
- Open one pull request per issue and link it with `Closes #<issue-number>`.
- Explain the behavior changed, the checks run and any deliberate limitations.
- Open unfinished work as a draft. Mark it ready only after local verification is complete.
- Do not merge while the `Backend` or `Frontend` GitHub Actions check is failing.
- Address review feedback with new commits; do not hide a functional change in an unrelated rewrite.

## Completion checklist

- [ ] The issue acceptance criteria are satisfied.
- [ ] Automated tests cover the change.
- [ ] Quality and test helpers pass.
- [ ] Relevant documentation is current and its links work.
- [ ] No secrets, environment files or generated output are included.
- [ ] The pull request is linked to the issue and CI passes.

See the [getting started guide](docs/getting-started.md), [command reference](docs/commands.md) and [troubleshooting guide](docs/troubleshooting.md) for practical help.

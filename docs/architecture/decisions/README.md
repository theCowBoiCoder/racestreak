# Architecture decision records

An architecture decision record (ADR) captures a significant technical choice, its context and its consequences. Use one when a decision changes a system boundary, public contract, data ownership, deployment model, security model, primary dependency or team-wide development convention.

Do not create an ADR for a routine implementation detail that is easy to reverse and already covered by project conventions.

## Workflow

1. Copy `template.md` to the next four-digit number and a short kebab-case title, for example `0002-authentication-boundary.md`.
2. Set the status to `Proposed` and describe the decision drivers and realistic alternatives.
3. Discuss the ADR in the pull request that needs the decision.
4. Change the status to `Accepted` when the pull request is approved.
5. Add it to the index below and link it from affected technical documentation.

Accepted ADRs are immutable historical records. If a choice changes, add a new ADR with status `Accepted`, change the old record to `Superseded`, and link the two records. Use `Deprecated` when a decision no longer applies and has no direct replacement.

## Status values

- `Proposed` — under discussion and not yet authoritative.
- `Accepted` — current project direction.
- `Deprecated` — no longer applicable.
- `Superseded` — replaced by a newer ADR.
- `Rejected` — considered but not adopted.

## Index

| ADR | Status | Decision |
| --- | --- | --- |
| [0001](0001-docker-first-modular-monorepo.md) | Accepted | Use a Docker-first modular monorepo |

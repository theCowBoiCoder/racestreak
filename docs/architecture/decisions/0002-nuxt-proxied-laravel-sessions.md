# ADR-0002: Use Nuxt-proxied Laravel sessions for browser authentication

- Status: Accepted
- Date: 2026-08-10
- Deciders: RaceStreak maintainers
- Supersedes: None
- Superseded by: None

## Context

RaceStreak has a Nuxt web application and a Laravel API. Browser requests already pass through Nuxt server routes so internal service addresses remain private. Driver authentication needs to support server-side rendering, CSRF protection, credential revocation and future account-security controls without exposing reusable credentials to browser JavaScript.

## Options considered

### Browser-held bearer tokens

Laravel could issue a bearer token for storage in browser state. This is simple for stateless API calls, but a token available to JavaScript has a larger impact if cross-site scripting occurs. Secure storage, expiration, refresh and revocation behavior would also need a new public contract.

### Direct browser-to-Laravel cookie sessions

The browser could call Laravel directly and use its normal session cookies. This follows Laravel's first-party application model, but adds a second browser-visible origin and requires coordinated cross-origin cookie, CORS and deployment configuration.

### Laravel sessions through the Nuxt server boundary

Nuxt can proxy the authentication endpoints, forward Laravel's opaque HTTP-only session cookie and relay safe response fields. Laravel remains the authentication authority while the browser communicates with one origin.

## Decision

Use Laravel's built-in database-backed session authentication through the Nuxt server proxy.

Nuxt retrieves a Laravel CSRF token before each login or logout and forwards it with the browser's opaque session cookie. Laravel validates credentials, regenerates the session identifier after login, protects authenticated routes with the `web` guard, invalidates the session during logout and returns only the allowlisted driver-account resource.

The browser never receives a bearer token or session payload. The session cookie is HTTP-only and SameSite, and production must enable its Secure flag under HTTPS. Authentication requests and responses remain under the versioned API contract.

## Consequences

### Positive

- Reusable authentication material is not available to browser JavaScript.
- Laravel owns credential validation, session rotation, expiry and invalidation.
- CSRF protection applies to login and logout.
- Nuxt server rendering and route middleware can identify the current driver through the same-origin proxy.
- Database-backed sessions support multiple Laravel containers and later session-revocation controls.

### Negative

- Nuxt must correctly forward request cookies and every `Set-Cookie` response.
- Stateful requests require a CSRF handshake before mutation.
- The browser UI depends on the Nuxt server and cannot call the Laravel authentication routes directly without implementing the same cookie and CSRF flow.
- Deployed environments must coordinate the session cookie, HTTPS and proxy configuration.

## Validation

- Feature tests cover login, safe current-account reads, logout, unauthenticated access and throttling.
- Frontend tests cover successful, invalid, validation and pending sign-in states.
- A Docker end-to-end test signs in through Nuxt, reads the protected account and confirms logout invalidation against PostgreSQL.

# Logging and monitoring

RaceStreak emits newline-delimited JSON logs to standard error in Docker. This keeps application logs compatible with container platforms and future log aggregation without coupling the foundation to a monitoring vendor.

## Log context

Every application log includes:

- `application`: the configured application name;
- `application_version`: the deployed RaceStreak version; and
- `environment`: the Laravel environment.

API requests also include `request_id`. A client may send `X-Request-ID` using 1–128 letters, digits, dots, underscores or hyphens. Invalid or missing values are replaced with a UUID, and the authoritative value is returned in the response header.

Use the response request ID to correlate a user-visible failure with backend logs. Request IDs are diagnostic values only and must not contain personal, authentication or business data.

## Events and levels

| Event | Level | Safe context |
| --- | --- | --- |
| `http.request_failed` | `warning` for 4xx, `error` for 5xx | method, route template, status, duration where available, exception class where applicable |
| `queue.job_failed` | `error` | connection, queue, job name, job ID, attempts, exception class |
| `health.dependency_failed` | `warning` | dependency name and exception class |
| `health.dependency_not_supported` | `error` | configured dependency name |
| `application.exception` | `error` | exception class for failures outside API requests |
| `auth.login_succeeded` | `info` | driver account public ID |
| `auth.login_failed` | `warning` | one-way email fingerprint |
| `auth.login_rate_limited` | `warning` | one-way email fingerprint |
| `auth.logout_succeeded` | `info` | driver account public ID |

Local and test environments default to the `debug` threshold. Production defaults to `info`. Set `LOG_LEVEL` to override the threshold and `LOG_CHANNEL` to select another Laravel channel. Docker uses the structured `stderr` channel; automated tests use the `null` channel unless a test is explicitly inspecting logs.

## Sensitive-data rules

Never add these values to log messages or context:

- passwords, API keys, access tokens, cookies or authorization headers;
- full request or queue payloads;
- raw query strings;
- database connection URLs or credentials;
- personal data that is not explicitly approved for operational logging; or
- exception messages that may contain any of the above.

Log stable identifiers, route templates, status codes and exception class names instead. RaceStreak's application logger deliberately omits exception messages and stack traces. Future error-reporting tools must apply equivalent redaction before they are enabled.

## Health endpoints

- `GET /up` is Laravel's process liveness check. It confirms the application can answer a request without checking external dependencies.
- `GET /api/v1/health` is the public readiness check. It currently verifies the configured database and returns HTTP 503 with safe dependency statuses if the database is unavailable.

Healthy readiness response:

```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "application": "RaceStreak",
    "version": "0.1.0",
    "checks": {
      "database": { "status": "healthy" }
    }
  }
}
```

An unhealthy response identifies the failed dependency only. It never returns exception messages, hostnames, credentials or connection strings.

Container orchestration should use `/up` for liveness and `/api/v1/health` for readiness. Alert when readiness returns 503 repeatedly, when `http.request_failed` errors increase, or when any `queue.job_failed` event occurs.

## Local inspection

Start the stack and follow backend output:

```sh
docker compose up --build -d
docker compose logs --follow backend
```

Each line from the application is a JSON object. To test correlation, supply a safe request ID:

```sh
curl -H "X-Request-ID: local-check-123" http://localhost:8000/api/v1/health
```

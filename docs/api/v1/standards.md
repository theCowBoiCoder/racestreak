# RaceStreak API v1 standards

Status: Active

Contract version: 1.0

Base path: `/api/v1`

This document is the contract for every RaceStreak API v1 endpoint. A breaking change requires a new base-path version. Additive fields, new endpoints and new optional query parameters may be introduced within v1; clients must ignore response fields they do not recognise.

## Transport and representation

- Use HTTPS outside local development.
- Send and receive UTF-8 JSON with `Content-Type: application/json`.
- Clients should send `Accept: application/json`.
- JSON property names and query parameters use `snake_case`.
- Resource paths use lowercase, plural, kebab-case nouns, for example `/driver-profiles/{driver_profile_id}`. Do not put verbs in resource paths.
- Nested resources are allowed only when the parent relationship is essential, for example `/communities/{community_id}/members`.

## HTTP methods and status codes

Use HTTP methods according to their standard semantics. `GET` reads, `POST` creates or starts an action, `PUT` fully replaces, `PATCH` partially updates and `DELETE` removes. `GET`, `PUT` and `DELETE` operations must be idempotent. Action endpoints, when unavoidable, use a final action segment such as `POST /races/{race_id}/publish`.

| Status | Use |
| --- | --- |
| `200 OK` | Successful read, update or action with a response body |
| `201 Created` | Resource created; include a `Location` header |
| `202 Accepted` | Asynchronous work accepted but not completed |
| `204 No Content` | Successful operation with no response body |
| `400 Bad Request` | Malformed JSON, query syntax or request shape |
| `401 Unauthorized` | Missing, invalid or expired credentials |
| `403 Forbidden` | Authenticated caller lacks permission |
| `404 Not Found` | Resource or route is not visible to the caller |
| `405 Method Not Allowed` | Path exists but does not support the method |
| `409 Conflict` | Request conflicts with current resource state |
| `422 Unprocessable Content` | Syntactically valid request fails field validation |
| `429 Too Many Requests` | Rate limit exceeded |
| `500 Internal Server Error` | Unexpected server failure |
| `503 Service Unavailable` | Temporary dependency or maintenance failure |

## Successful responses

Except for `204` responses, success responses always contain `success: true` and `data`. A single resource uses an object:

```json
{
  "success": true,
  "data": {
    "id": "0198f42c-5a75-7a4f-ae19-4c44225bc2c7",
    "display_name": "Hayden"
  }
}
```

A collection uses an array and includes pagination metadata when it can be paged:

```json
{
  "success": true,
  "data": [
    {
      "id": "0198f42c-5a75-7a4f-ae19-4c44225bc2c7",
      "display_name": "Hayden"
    }
  ],
  "meta": {
    "pagination": {
      "per_page": 25,
      "next_cursor": "eyJpZCI6IjAxOThmNDJjIn0",
      "previous_cursor": null,
      "has_more": true
    }
  },
  "links": {
    "next": "/api/v1/drivers?cursor=eyJpZCI6IjAxOThmNDJjIn0&per_page=25",
    "previous": null
  }
}
```

## Error responses

Errors always contain `success: false` and an `error` object. `code` is a stable, documented, uppercase `SNAKE_CASE` identifier intended for programmatic handling. `message` is safe for display but may change and must not be parsed. `details` is optional and must not expose stack traces, database details, credentials or other sensitive values.

```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "The requested resource was not found."
  }
}
```

The platform-wide codes are:

| Status | Code |
| --- | --- |
| `400` | `BAD_REQUEST` |
| `401` | `UNAUTHENTICATED` |
| `403` | `FORBIDDEN` |
| `404` | `NOT_FOUND` |
| `405` | `METHOD_NOT_ALLOWED` |
| `409` | `CONFLICT` |
| `422` | `VALIDATION_ERROR` |
| `429` | `RATE_LIMIT_EXCEEDED` |
| `500` | `INTERNAL_ERROR` |
| `503` | `SERVICE_UNAVAILABLE` |

Domain-specific errors may add documented codes while retaining the same envelope and appropriate HTTP status.

Validation errors use status `422`, code `VALIDATION_ERROR`, and a `details.fields` object. Each key is the request property's dot-notation path and each value is an array of messages. All detected field failures may be returned together.

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The request could not be validated.",
    "details": {
      "fields": {
        "driver_name": [
          "The driver name field is required."
        ],
        "profile.country_code": [
          "The selected country code is invalid."
        ]
      }
    }
  }
}
```

## Pagination and filtering

- Use cursor pagination for API collections. Accept the opaque `cursor` query parameter and return it unchanged only through generated links.
- Accept `per_page`, defaulting to `25` and capped at `100`.
- Return pagination state in `meta.pagination` and navigable relative URLs in `links` as shown above.
- Use repeated parameters for multi-value filters, for example `status=active&status=pending`.
- Use `sort` for ordering. Prefix descending fields with `-`, for example `sort=-created_at,display_name`.
- Invalid cursors, page sizes, filters or sort fields return a `422` validation error.

## Dates, times and durations

- Timestamps use RFC 3339 strings in UTC with seconds: `2026-08-10T14:30:00Z`. Include fractional seconds only when the precision is meaningful.
- Calendar dates use `YYYY-MM-DD`.
- Time-only values include an offset, for example `19:30:00+01:00`.
- Durations use whole integer seconds unless a field explicitly documents another unit. Unit-bearing names such as `duration_seconds` are required.
- Store and compare instants in UTC. A user's IANA timezone, such as `Europe/London`, is a separate preference and never changes the meaning of a timestamp.

## Identifiers

- New public resource identifiers use lowercase RFC 9562 UUID version 7 strings.
- Treat every identifier and cursor as opaque. Clients must not derive creation time, ordering or business meaning from them.
- Path parameters use descriptive names such as `{race_id}`, not `{id}`.
- External-provider identifiers are strings and use a provider-qualified field name, such as `simgrid_event_id`.

## Authentication and authorisation

- Protected endpoints accept `Authorization: Bearer <token>` over HTTPS.
- Authentication failures use `401` and include `WWW-Authenticate: Bearer` where appropriate.
- Authorisation failures use `403`. Use `404` instead when revealing that a resource exists would leak information.
- Never accept tokens in URLs or return credentials, token values or secret material in responses or logs.
- Public endpoints must be explicitly documented as public. Authentication implementation is outside PF-005.

## Request IDs

- Every API response carries an `X-Request-ID` generated by RaceStreak or accepted from a valid client value.
- A client may supply `X-Request-ID`; the server may accept a valid value or replace it, and the response header is always authoritative.
- Request IDs are opaque diagnostic values. They do not provide idempotency and must not contain user or business data.
- Include the response request ID in support reports. Header generation and safe correlation logging are implemented by PF-009.

## Rate limiting

- Rate-limited endpoints use the RFC 9331 response fields `RateLimit-Limit`, `RateLimit-Remaining` and `RateLimit-Reset`.
- A `429` response includes `Retry-After` and the normal RaceStreak error envelope with code `RATE_LIMIT_EXCEEDED`.
- Limits may differ by endpoint, caller or subscription, so clients must use response fields instead of hard-coded assumptions.

## Current endpoint examples

### Driver registration

`POST /api/v1/driver-accounts` is public and accepts:

```json
{
  "display_name": "Apex Driver",
  "email": "driver@example.test",
  "password": "a password meeting the policy",
  "password_confirmation": "the same password"
}
```

Passwords require at least 12 characters with upper and lower case, a number and a symbol. Email addresses are trimmed, normalized to lowercase and unique. Successful registration returns `201 Created`, a `Location` header and the safe driver-account resource; it never returns the password, hash or internal database ID.

Registration is limited to five attempts per minute for the submitted email and network address. A rejected attempt returns the standard `429` envelope with `Retry-After` and `RateLimit-*` fields.

```json
{
  "success": true,
  "data": {
    "id": "0198f42c-5a75-7a4f-ae19-4c44225bc2c7",
    "display_name": "Apex Driver",
    "email": "driver@example.test",
    "email_verified": false,
    "created_at": "2026-08-10T13:30:00Z"
  }
}
```

Authentication is intentionally not created by registration and is introduced by DA-002.

### Platform health

`GET /api/v1/health` is public and returns:

```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "application": "RaceStreak",
    "version": "0.1.0",
    "checks": {
      "database": {
        "status": "healthy"
      }
    }
  }
}
```

`GET /api/v1/version` is public and returns:

```json
{
  "success": true,
  "data": {
    "version": "0.1.0"
  }
}
```

## New endpoint checklist

- [ ] Route is under `/api/v1` and uses resource-oriented naming.
- [ ] Public or authenticated access and required authorisation are documented and tested.
- [ ] Request fields, query parameters, validation constraints and defaults are documented.
- [ ] Success response uses the standard envelope, status code and, for creation, `Location` header.
- [ ] Collections follow the pagination, filtering and sorting contract.
- [ ] Errors use stable codes, safe messages and the standard validation structure.
- [ ] Identifiers, dates, timestamps, timezones and durations follow this contract.
- [ ] Rate-limit policy is selected and documented where applicable.
- [ ] Feature tests cover success, validation, authentication, authorisation and missing resources as applicable.
- [ ] No response or log exposes secrets, internal exceptions or unnecessary personal data.
- [ ] Any additive contract change updates this document; breaking changes target a new API version.

## Standards references

- [HTTP Semantics (RFC 9110)](https://www.rfc-editor.org/rfc/rfc9110.html)
- [Date and Time on the Internet (RFC 3339)](https://www.rfc-editor.org/rfc/rfc3339.html)
- [RateLimit Fields for HTTP (RFC 9331)](https://www.rfc-editor.org/rfc/rfc9331.html)
- [Universally Unique IDentifiers (RFC 9562)](https://www.rfc-editor.org/rfc/rfc9562.html)

# RaceStreak

RaceStreak is a rewards, progression and engagement platform for community sim racing. Every race counts.

This repository currently contains the PF-001 platform foundation:

- Laravel API in `backend/`
- Nuxt web application in `frontend/`
- Docker Compose stack for consistent local execution

## Prerequisites

- Docker Desktop with Docker Compose

No host installation of PHP, Composer, Node.js or npm is required to run the containerized stack.

## Start RaceStreak

```sh
docker compose up --build
```

Once both services are healthy:

- Nuxt frontend: <http://localhost:3000>
- Laravel API health: <http://localhost:8000/api/v1/health>
- Laravel API version: <http://localhost:8000/api/v1/version>

Stop the stack with `docker compose down`.

## Run the backend tests

```sh
docker compose run --rm -e APP_ENV=testing -e LOG_CHANNEL=null backend php artisan test
```

## Run the frontend tests

```sh
docker run --rm -v "${PWD}/frontend:/app" -w /app node:22-alpine sh -lc "npm install --global npm@11 && npm ci && npm test"
```

## Configuration

Docker Compose supplies the required development values. When running either application outside Docker, copy the relevant `.env.example` file and adjust it locally.

Laravel validates `APP_NAME`, `APP_VERSION`, `APP_TIMEZONE` and `APP_URL` during startup. Missing values produce a configuration error naming the missing variable without displaying secret values.

## Initial API format

Successful responses:

```json
{
  "success": true,
  "data": {}
}
```

Failed API responses:

```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "An unexpected error occurred."
  }
}
```

PF-001 deliberately excludes authentication, product database entities, integrations, XP, challenges, rewards and deployment automation.

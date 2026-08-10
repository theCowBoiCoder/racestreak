# RaceStreak Web

The RaceStreak frontend is a Nuxt application. Its initial screen calls the Laravel health endpoint through a server-side Nuxt proxy, so the internal Docker service name is never exposed to the browser.

Set `NUXT_BACKEND_BASE` to the internal API base URL when running in a container, or use the default `http://localhost:8000/api/v1` during local development.

```sh
npm install
npm run dev
```

Run the frontend tests with:

```sh
npm test
```

The test command requires Node.js 22 or can be run from the repository root in a clean container:

```sh
docker run --rm -v "${PWD}/frontend:/app" -w /app node:22-alpine sh -lc "npm install --global npm@11 && npm ci && npm test"
```

The initial routed status page includes loading, healthy, empty and error states. It calls the internal Nuxt `/api/health` endpoint so backend service addresses and configuration remain server-side.

For the complete stack, use Docker Compose from the repository root.

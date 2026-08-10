# RaceStreak Web

The RaceStreak frontend is a Nuxt application. Its initial screen calls the Laravel health endpoint through a server-side Nuxt proxy, so the internal Docker service name is never exposed to the browser.

Set `NUXT_BACKEND_BASE` to the internal API base URL when running in a container, or use the default `http://localhost:8000/api/v1` during local development.

```sh
npm install
npm run dev
```

For the complete stack, use Docker Compose from the repository root.

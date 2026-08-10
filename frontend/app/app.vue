<script setup lang="ts">
type HealthResponse = {
  success: boolean
  data: {
    status: string
    application: string
    version: string
  }
}

const { data: health, error, status, refresh } = await useFetch<HealthResponse>('/api/health')
</script>

<template>
  <main>
    <NuxtRouteAnnouncer />
    <section class="card">
      <p class="eyebrow">Every race counts.</p>
      <h1>RaceStreak</h1>
      <p class="intro">The platform foundation is running.</p>

      <div class="status" :class="{ healthy: health?.success, unhealthy: error }">
        <span class="indicator" aria-hidden="true" />
        <div>
          <strong v-if="status === 'pending'">Checking the API…</strong>
          <strong v-else-if="health?.success">Backend healthy</strong>
          <strong v-else>Backend unavailable</strong>
          <p v-if="health">{{ health.data.application }} API v{{ health.data.version }}</p>
          <p v-else-if="error">Start the Laravel container and try again.</p>
        </div>
      </div>

      <button v-if="error" type="button" @click="refresh()">Try again</button>
    </section>
  </main>
</template>

<style>
:root {
  color-scheme: dark;
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  background: #090b10;
  color: #f6f7fb;
}

* { box-sizing: border-box; }
body { margin: 0; }

main {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 2rem;
  background:
    radial-gradient(circle at 20% 20%, rgba(255, 82, 46, .18), transparent 35%),
    linear-gradient(145deg, #111522, #07080c 65%);
}

.card {
  width: min(100%, 42rem);
  padding: clamp(2rem, 7vw, 4.5rem);
  border: 1px solid rgba(255,255,255,.11);
  border-radius: 1.5rem;
  background: rgba(15, 18, 27, .82);
  box-shadow: 0 2rem 6rem rgba(0,0,0,.45);
  backdrop-filter: blur(18px);
}

.eyebrow { margin: 0 0 .75rem; color: #ff6647; font-weight: 750; letter-spacing: .12em; text-transform: uppercase; }
h1 { margin: 0; font-size: clamp(3rem, 10vw, 6rem); line-height: .95; letter-spacing: -.055em; }
.intro { margin: 1.25rem 0 2.5rem; color: #adb3c1; font-size: 1.15rem; }

.status {
  display: flex;
  gap: 1rem;
  align-items: center;
  padding: 1rem 1.1rem;
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 1rem;
  background: rgba(255,255,255,.035);
}

.indicator { width: .8rem; height: .8rem; flex: 0 0 auto; border-radius: 50%; background: #f6b73c; box-shadow: 0 0 1rem currentColor; }
.healthy .indicator { color: #31d17c; background: currentColor; }
.unhealthy .indicator { color: #ff5b5b; background: currentColor; }
.status p { margin: .25rem 0 0; color: #8e96a8; }

button { margin-top: 1rem; border: 0; border-radius: .7rem; padding: .75rem 1rem; background: #ff6647; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
</style>

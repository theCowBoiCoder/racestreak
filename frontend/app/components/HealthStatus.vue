<script setup lang="ts">
import { useBackendHealth } from '~/composables/useBackendHealth'

const { data: health, error, status, refresh } = useBackendHealth()
</script>

<template>
  <div
    class="status"
    :class="{ healthy: health?.success, unhealthy: error }"
    aria-live="polite"
  >
    <span class="indicator" aria-hidden="true" />
    <div>
      <strong v-if="status === 'pending'">Checking the API…</strong>
      <strong v-else-if="health?.success">Backend healthy</strong>
      <strong v-else-if="error">Backend unavailable</strong>
      <strong v-else>No status data</strong>

      <p v-if="health">{{ health.data.application }} API v{{ health.data.version }}</p>
      <p v-else-if="error">Start the Laravel container and try again.</p>
      <p v-else-if="status !== 'pending'">No backend status has been received yet.</p>
    </div>
  </div>

  <button v-if="error" type="button" @click="refresh()">Try again</button>
</template>

<style scoped>
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

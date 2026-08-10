<script setup lang="ts">
const route = useRoute()
const authentication = useDriverAuthentication()

if (authentication.status.value === 'idle') {
  await authentication.refresh()
}

if (authentication.account.value) {
  await navigateTo('/account')
}

const sessionExpired = computed(() => route.query.expired === '1')
const sessionUnavailable = computed(() => route.query.unavailable === '1')

function safeRedirect(): string {
  const redirect = route.query.redirect

  return typeof redirect === 'string' && redirect.startsWith('/') && !redirect.startsWith('//')
    ? redirect
    : '/account'
}

async function signedIn() {
  await navigateTo(safeRedirect())
}
</script>

<template>
  <main>
    <section class="card login-card">
      <p class="eyebrow">Welcome back</p>
      <h1>Sign in</h1>
      <p class="intro">Continue building your RaceStreak.</p>

      <p v-if="sessionExpired" class="session-message" role="status">
        Your session expired. Sign in again to continue.
      </p>
      <p v-else-if="sessionUnavailable" class="session-message" role="status">
        Your session could not be checked. Please sign in again.
      </p>

      <DriverLoginForm @signed-in="signedIn" />

      <p class="account-link">
        New to RaceStreak?
        <NuxtLink to="/register">Create an account</NuxtLink>
      </p>
    </section>
  </main>
</template>

<style scoped>
h1 {
  font-size: clamp(2.4rem, 8vw, 4.75rem);
}

.login-card {
  width: min(100%, 46rem);
}

.session-message {
  margin: 0 0 1.25rem;
  border-radius: 0.75rem;
  padding: 0.85rem 1rem;
  background: rgba(255, 102, 71, 0.12);
  color: #ffd0c7;
}

.account-link {
  margin: 1.5rem 0 0;
  color: #adb3c1;
}

a {
  color: #ff8a73;
}
</style>

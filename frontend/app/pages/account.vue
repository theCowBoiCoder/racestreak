<script setup lang="ts">
definePageMeta({ middleware: 'authenticated' })

const authentication = useDriverAuthentication()
const isSigningOut = ref(false)
const signOutError = ref('')

async function signOut() {
  if (isSigningOut.value) {
    return
  }

  isSigningOut.value = true
  signOutError.value = ''

  try {
    await authentication.signOut()
    await navigateTo('/login')
  } catch {
    signOutError.value = 'Sign out could not be confirmed. Please try again.'
  } finally {
    isSigningOut.value = false
  }
}
</script>

<template>
  <main>
    <section class="card account-card">
      <p class="eyebrow">Driver account</p>

      <div v-if="authentication.status.value === 'loading'" role="status">
        Loading your account...
      </div>

      <template v-else-if="authentication.account.value">
        <h1>{{ authentication.account.value.display_name }}</h1>
        <p class="intro">Signed in as {{ authentication.account.value.email }}</p>

        <dl>
          <div>
            <dt>Account ID</dt>
            <dd>{{ authentication.account.value.id }}</dd>
          </div>
          <div>
            <dt>Email status</dt>
            <dd>
              {{ authentication.account.value.email_verified ? 'Verified' : 'Not yet verified' }}
            </dd>
          </div>
        </dl>

        <p v-if="signOutError" class="form-error" role="alert">{{ signOutError }}</p>
        <button type="button" :disabled="isSigningOut" @click="signOut">
          {{ isSigningOut ? 'Signing out...' : 'Sign out' }}
        </button>
      </template>
    </section>
  </main>
</template>

<style scoped>
h1 {
  overflow-wrap: anywhere;
  font-size: clamp(2.4rem, 8vw, 4.75rem);
}

.account-card {
  width: min(100%, 52rem);
}

dl {
  display: grid;
  gap: 0.8rem;
  margin: 0 0 2rem;
}

dl div {
  display: grid;
  gap: 0.25rem;
  border-radius: 0.75rem;
  padding: 0.9rem 1rem;
  background: rgba(255, 255, 255, 0.05);
}

dt {
  color: #adb3c1;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
}

dd {
  margin: 0;
  overflow-wrap: anywhere;
}

.form-error {
  color: #ff9292;
}

button {
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 0.75rem;
  padding: 0.85rem 1rem;
  background: transparent;
  color: inherit;
  font: inherit;
  font-weight: 750;
  cursor: pointer;
}

button:disabled {
  cursor: wait;
  opacity: 0.65;
}
</style>

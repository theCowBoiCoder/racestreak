<script setup lang="ts">
import { reactive, ref } from 'vue'

import { useDriverAuthentication } from '~/composables/useDriverAuthentication'
import type { AuthenticationErrorResponse } from '~/types/authentication'

const emit = defineEmits<{
  signedIn: []
}>()

const { signIn } = useDriverAuthentication()
const form = reactive({ email: '', password: '' })
const fieldErrors = ref<Record<string, string[]>>({})
const generalError = ref('')
const isSubmitting = ref(false)

function errorData(error: unknown): AuthenticationErrorResponse | null {
  if (typeof error !== 'object' || error === null || !('data' in error)) {
    return null
  }

  const data = error.data

  if (typeof data !== 'object' || data === null || !('success' in data) || data.success !== false) {
    return null
  }

  return data as AuthenticationErrorResponse
}

async function submit() {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  fieldErrors.value = {}
  generalError.value = ''

  try {
    await signIn({ ...form })
    form.password = ''
    emit('signedIn')
  } catch (error: unknown) {
    const response = errorData(error)
    fieldErrors.value = response?.error.details?.fields ?? {}
    generalError.value = response?.error.message ?? 'Sign in is unavailable. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <form class="login-form" novalidate @submit.prevent="submit">
    <div class="field">
      <label for="login-email">Email address</label>
      <input
        id="login-email"
        v-model="form.email"
        name="email"
        type="email"
        autocomplete="email"
        maxlength="254"
        required
        :aria-invalid="Boolean(fieldErrors.email)"
        :aria-describedby="fieldErrors.email ? 'login-email-error' : undefined"
      />
      <p v-if="fieldErrors.email" id="login-email-error" class="field-error">
        {{ fieldErrors.email[0] }}
      </p>
    </div>

    <div class="field">
      <label for="login-password">Password</label>
      <input
        id="login-password"
        v-model="form.password"
        name="password"
        type="password"
        autocomplete="current-password"
        maxlength="4096"
        required
        :aria-invalid="Boolean(fieldErrors.password)"
        :aria-describedby="fieldErrors.password ? 'login-password-error' : undefined"
      />
      <p v-if="fieldErrors.password" id="login-password-error" class="field-error">
        {{ fieldErrors.password[0] }}
      </p>
    </div>

    <p v-if="generalError" class="form-error" role="alert">{{ generalError }}</p>

    <button type="submit" :disabled="isSubmitting">
      {{ isSubmitting ? 'Signing in...' : 'Sign in' }}
    </button>
  </form>
</template>

<style scoped>
.login-form,
.field {
  display: grid;
}

.login-form {
  gap: 1.15rem;
}

.field {
  gap: 0.45rem;
}

label {
  font-weight: 700;
}

input {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.16);
  border-radius: 0.75rem;
  padding: 0.85rem 0.95rem;
  background: rgba(255, 255, 255, 0.055);
  color: inherit;
  font: inherit;
}

input:focus {
  border-color: #ff6647;
  outline: 3px solid rgba(255, 102, 71, 0.2);
}

input[aria-invalid='true'] {
  border-color: #ff7272;
}

.field-error,
.form-error {
  margin: 0;
  color: #ff9292;
  font-size: 0.9rem;
}

button {
  border: 0;
  border-radius: 0.75rem;
  padding: 0.9rem 1rem;
  background: #ff6647;
  color: #fff;
  font: inherit;
  font-weight: 800;
  cursor: pointer;
}

button:disabled {
  cursor: wait;
  opacity: 0.65;
}
</style>

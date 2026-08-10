<script setup lang="ts">
import { reactive, ref } from 'vue'

import { registerDriver } from '~/composables/useDriverRegistration'
import type { ApiValidationErrorResponse, DriverAccount } from '~/types/driverAccount'

const form = reactive({
  display_name: '',
  email: '',
  password: '',
  password_confirmation: ''
})
const fieldErrors = ref<Record<string, string[]>>({})
const generalError = ref('')
const createdAccount = ref<DriverAccount | null>(null)
const isSubmitting = ref(false)

function errorData(error: unknown): ApiValidationErrorResponse | null {
  if (typeof error !== 'object' || error === null || !('data' in error)) {
    return null
  }

  const data = error.data

  if (typeof data !== 'object' || data === null || !('success' in data) || data.success !== false) {
    return null
  }

  return data as ApiValidationErrorResponse
}

async function submit() {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  fieldErrors.value = {}
  generalError.value = ''
  createdAccount.value = null

  try {
    const response = await registerDriver({ ...form })
    createdAccount.value = response.data
    form.password = ''
    form.password_confirmation = ''
  } catch (error: unknown) {
    const response = errorData(error)
    fieldErrors.value = response?.error.details?.fields ?? {}
    generalError.value = response?.error.message ?? 'Registration is unavailable. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <form v-if="!createdAccount" class="registration-form" novalidate @submit.prevent="submit">
    <div class="field">
      <label for="display-name">Display name</label>
      <input
        id="display-name"
        v-model="form.display_name"
        name="display_name"
        type="text"
        autocomplete="nickname"
        minlength="2"
        maxlength="50"
        required
        :aria-invalid="Boolean(fieldErrors.display_name)"
        :aria-describedby="fieldErrors.display_name ? 'display-name-error' : undefined"
      />
      <p v-if="fieldErrors.display_name" id="display-name-error" class="field-error">
        {{ fieldErrors.display_name[0] }}
      </p>
    </div>

    <div class="field">
      <label for="email">Email address</label>
      <input
        id="email"
        v-model="form.email"
        name="email"
        type="email"
        autocomplete="email"
        maxlength="254"
        required
        :aria-invalid="Boolean(fieldErrors.email)"
        :aria-describedby="fieldErrors.email ? 'email-error' : undefined"
      />
      <p v-if="fieldErrors.email" id="email-error" class="field-error">
        {{ fieldErrors.email[0] }}
      </p>
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input
        id="password"
        v-model="form.password"
        name="password"
        type="password"
        autocomplete="new-password"
        minlength="12"
        required
        :aria-invalid="Boolean(fieldErrors.password)"
        :aria-describedby="fieldErrors.password ? 'password-error password-help' : 'password-help'"
      />
      <p id="password-help" class="field-help">
        Use 12 or more characters with upper and lower case, a number and a symbol.
      </p>
      <p v-if="fieldErrors.password" id="password-error" class="field-error">
        {{ fieldErrors.password[0] }}
      </p>
    </div>

    <div class="field">
      <label for="password-confirmation">Confirm password</label>
      <input
        id="password-confirmation"
        v-model="form.password_confirmation"
        name="password_confirmation"
        type="password"
        autocomplete="new-password"
        minlength="12"
        required
      />
    </div>

    <p v-if="generalError" class="form-error" role="alert">{{ generalError }}</p>

    <button type="submit" :disabled="isSubmitting">
      {{ isSubmitting ? 'Creating account...' : 'Create account' }}
    </button>
  </form>

  <section v-else class="success" aria-live="polite">
    <p class="eyebrow">Account created</p>
    <h2>Welcome, {{ createdAccount.display_name }}.</h2>
    <p>Your RaceStreak account is ready.</p>
  </section>
</template>

<style scoped>
.registration-form {
  display: grid;
  gap: 1.15rem;
}

.field {
  display: grid;
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

.field-help,
.field-error,
.form-error,
.success p {
  margin: 0;
  color: #adb3c1;
  font-size: 0.9rem;
}

.field-error,
.form-error {
  color: #ff9292;
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

.success h2 {
  margin: 0 0 0.75rem;
  font-size: clamp(1.8rem, 5vw, 2.6rem);
}
</style>

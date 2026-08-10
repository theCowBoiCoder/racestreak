// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  runtimeConfig: {
    backendBase: process.env.NUXT_BACKEND_BASE || 'http://localhost:8000/api/v1'
  }
})

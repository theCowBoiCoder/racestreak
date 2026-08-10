export default defineEventHandler(async () => {
  const config = useRuntimeConfig()

  return await $fetch(`${config.backendBase}/health`)
})

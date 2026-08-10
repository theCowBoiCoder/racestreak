import { useDriverAuthentication } from '~/composables/useDriverAuthentication'

export default defineNuxtRouteMiddleware(async (to) => {
  const authentication = useDriverAuthentication()

  try {
    await authentication.refresh()
  } catch {
    return navigateTo({ path: '/login', query: { unavailable: '1', redirect: to.fullPath } })
  }

  if (!authentication.account.value) {
    return navigateTo({
      path: '/login',
      query: {
        ...(authentication.status.value === 'expired' ? { expired: '1' } : {}),
        redirect: to.fullPath
      }
    })
  }
})

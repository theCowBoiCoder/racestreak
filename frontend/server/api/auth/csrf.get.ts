import type { CsrfTokenResponse } from '../../../app/types/authentication'
import { backendRequestHeaders, forwardBackendResponse } from '../../utils/backendProxy'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const response = await $fetch.raw<CsrfTokenResponse>(
    `${config.backendBase}/authentication/csrf-token`,
    {
      headers: backendRequestHeaders(event),
      ignoreResponseError: true
    }
  )

  forwardBackendResponse(event, response)

  return response._data
})

import type {
  AuthenticationErrorResponse,
  AuthenticationSessionResponse,
  DriverLoginPayload
} from '../../../app/types/authentication'
import { backendRequestHeaders, forwardBackendResponse } from '../../utils/backendProxy'

type LoginResponse = AuthenticationSessionResponse | AuthenticationErrorResponse

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const body = await readBody<DriverLoginPayload>(event)
  const csrfToken = getHeader(event, 'x-csrf-token')
  const response = await $fetch.raw<LoginResponse>(
    `${config.backendBase}/authentication/session`,
    {
      method: 'POST',
      body,
      headers: backendRequestHeaders(event, csrfToken),
      ignoreResponseError: true
    }
  )

  forwardBackendResponse(event, response)

  return response._data
})

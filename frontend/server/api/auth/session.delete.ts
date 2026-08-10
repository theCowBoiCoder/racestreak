import type {
  AuthenticationErrorResponse,
  SignOutResponse
} from '../../../app/types/authentication'
import { backendRequestHeaders, forwardBackendResponse } from '../../utils/backendProxy'

type DeleteSessionResponse = SignOutResponse | AuthenticationErrorResponse

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const csrfToken = getHeader(event, 'x-csrf-token')
  const response = await $fetch.raw<DeleteSessionResponse>(
    `${config.backendBase}/authentication/session`,
    {
      method: 'DELETE',
      headers: backendRequestHeaders(event, csrfToken),
      ignoreResponseError: true
    }
  )

  forwardBackendResponse(event, response)

  return response._data
})

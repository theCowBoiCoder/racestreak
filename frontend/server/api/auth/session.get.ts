import type {
  AuthenticationErrorResponse,
  AuthenticationSessionResponse
} from '../../../app/types/authentication'
import { backendRequestHeaders, forwardBackendResponse } from '../../utils/backendProxy'

type CurrentSessionResponse = AuthenticationSessionResponse | AuthenticationErrorResponse

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const response = await $fetch.raw<CurrentSessionResponse>(
    `${config.backendBase}/authentication/session`,
    {
      headers: backendRequestHeaders(event),
      ignoreResponseError: true
    }
  )

  forwardBackendResponse(event, response)

  return response._data
})

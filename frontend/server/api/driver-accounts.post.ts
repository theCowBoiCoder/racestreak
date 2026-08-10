import type {
  ApiValidationErrorResponse,
  DriverRegistrationPayload,
  DriverRegistrationResponse
} from '../../app/types/driverAccount'

type RegistrationApiResponse = DriverRegistrationResponse | ApiValidationErrorResponse

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const body = await readBody<DriverRegistrationPayload>(event)
  const requestId = getHeader(event, 'x-request-id')

  const response = await $fetch.raw<RegistrationApiResponse>(
    `${config.backendBase}/driver-accounts`,
    {
      method: 'POST',
      body,
      headers: {
        accept: 'application/json',
        ...(requestId ? { 'x-request-id': requestId } : {})
      },
      ignoreResponseError: true
    }
  )

  setResponseStatus(event, response.status)

  for (const header of [
    'location',
    'x-request-id',
    'ratelimit-limit',
    'ratelimit-remaining',
    'ratelimit-reset',
    'retry-after'
  ]) {
    const value = response.headers.get(header)

    if (value) {
      setHeader(event, header, value)
    }
  }

  return response._data
})

import type { H3Event } from 'h3'

const forwardedResponseHeaders = [
  'cache-control',
  'location',
  'x-request-id',
  'ratelimit-limit',
  'ratelimit-remaining',
  'ratelimit-reset',
  'retry-after'
]

export function backendRequestHeaders(event: H3Event, csrfToken?: string): Record<string, string> {
  const headers: Record<string, string> = { accept: 'application/json' }
  const cookie = getHeader(event, 'cookie')
  const requestId = getHeader(event, 'x-request-id')

  if (cookie) {
    headers.cookie = cookie
  }

  if (requestId) {
    headers['x-request-id'] = requestId
  }

  if (csrfToken) {
    headers['x-csrf-token'] = csrfToken
  }

  return headers
}

export function forwardBackendResponse(
  event: H3Event,
  response: { status: number; headers: Headers }
): void {
  setResponseStatus(event, response.status)

  for (const header of forwardedResponseHeaders) {
    const value = response.headers.get(header)

    if (value) {
      setHeader(event, header, value)
    }
  }

  for (const cookie of response.headers.getSetCookie()) {
    appendResponseHeader(event, 'set-cookie', cookie)
  }
}

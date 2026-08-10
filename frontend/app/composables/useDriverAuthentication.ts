import type {
  AuthenticationSessionResponse,
  AuthenticationStatus,
  CsrfTokenResponse,
  DriverLoginPayload,
  SignOutResponse
} from '~/types/authentication'
import type { DriverAccount } from '~/types/driverAccount'

function responseStatus(error: unknown): number | undefined {
  if (typeof error !== 'object' || error === null || !('statusCode' in error)) {
    return undefined
  }

  return typeof error.statusCode === 'number' ? error.statusCode : undefined
}

export function useDriverAuthentication() {
  const account = useState<DriverAccount | null>('driver-account', () => null)
  const status = useState<AuthenticationStatus>('driver-authentication-status', () => 'idle')
  const requestFetch = useRequestFetch()

  async function csrfToken(): Promise<string> {
    const response = await requestFetch<CsrfTokenResponse>('/api/auth/csrf')

    return response.data.token
  }

  async function signIn(payload: DriverLoginPayload): Promise<DriverAccount> {
    status.value = 'loading'

    try {
      const token = await csrfToken()
      const response = await requestFetch<AuthenticationSessionResponse>('/api/auth/session', {
        method: 'POST',
        headers: { 'x-csrf-token': token },
        body: payload
      })

      account.value = response.data
      status.value = 'authenticated'

      return response.data
    } catch (error: unknown) {
      account.value = null
      status.value = 'signed-out'
      throw error
    }
  }

  async function refresh(): Promise<DriverAccount | null> {
    const previouslyAuthenticated = account.value !== null || status.value === 'authenticated'
    status.value = 'loading'

    try {
      const response = await requestFetch<AuthenticationSessionResponse>('/api/auth/session')
      account.value = response.data
      status.value = 'authenticated'

      return response.data
    } catch (error: unknown) {
      account.value = null

      if (responseStatus(error) === 401) {
        status.value = previouslyAuthenticated ? 'expired' : 'signed-out'

        return null
      }

      status.value = 'error'
      throw error
    }
  }

  async function signOut(): Promise<void> {
    status.value = 'loading'

    try {
      const token = await csrfToken()
      await requestFetch<SignOutResponse>('/api/auth/session', {
        method: 'DELETE',
        headers: { 'x-csrf-token': token }
      })
      account.value = null
      status.value = 'signed-out'
    } catch (error: unknown) {
      status.value = account.value ? 'authenticated' : 'error'
      throw error
    }
  }

  return {
    account: readonly(account),
    status: readonly(status),
    signIn,
    refresh,
    signOut
  }
}

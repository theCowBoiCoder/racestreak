import type { ApiValidationErrorResponse, DriverAccount } from './driverAccount'

export interface DriverLoginPayload {
  email: string
  password: string
}

export interface CsrfTokenResponse {
  success: true
  data: {
    token: string
  }
}

export interface AuthenticationSessionResponse {
  success: true
  data: DriverAccount
}

export interface SignOutResponse {
  success: true
  data: {
    message: string
  }
}

export type AuthenticationErrorResponse = ApiValidationErrorResponse

export type AuthenticationStatus =
  'idle' | 'loading' | 'authenticated' | 'signed-out' | 'expired' | 'error'

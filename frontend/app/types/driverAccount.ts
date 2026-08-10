export interface DriverRegistrationPayload {
  display_name: string
  email: string
  password: string
  password_confirmation: string
}

export interface DriverAccount {
  id: string
  display_name: string
  email: string
  email_verified: boolean
  created_at: string
}

export interface DriverRegistrationResponse {
  success: true
  data: DriverAccount
}

export interface ApiValidationErrorResponse {
  success: false
  error: {
    code: string
    message: string
    details?: {
      fields?: Record<string, string[]>
    }
  }
}

import type { DriverRegistrationPayload, DriverRegistrationResponse } from '~/types/driverAccount'

export function registerDriver(payload: DriverRegistrationPayload) {
  return $fetch<DriverRegistrationResponse>('/api/driver-accounts', {
    method: 'POST',
    body: payload
  })
}

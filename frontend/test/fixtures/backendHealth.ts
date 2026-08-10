import type { HealthResponse } from '../../app/composables/useBackendHealth'

export const healthyBackendResponse: HealthResponse = {
  success: true,
  data: {
    status: 'healthy',
    application: 'RaceStreak',
    version: '0.1.0'
  }
}

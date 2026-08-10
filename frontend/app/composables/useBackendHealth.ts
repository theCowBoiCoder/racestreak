export type HealthResponse = {
  success: boolean
  data: {
    status: string
    application: string
    version: string
  }
}

export function useBackendHealth() {
  return useFetch<HealthResponse>('/api/health')
}

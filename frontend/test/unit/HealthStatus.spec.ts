import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'

import HealthStatus from '../../app/components/HealthStatus.vue'
import { healthyBackendResponse } from '../fixtures/backendHealth'

const useBackendHealth = vi.hoisted(() => vi.fn())

vi.mock('~/composables/useBackendHealth', () => ({ useBackendHealth }))

describe('HealthStatus', () => {
  it('shows a loading state while the health request is pending', () => {
    useBackendHealth.mockReturnValue({
      data: ref(null),
      error: ref(null),
      status: ref('pending'),
      refresh: vi.fn()
    })

    const component = mount(HealthStatus)

    expect(component.text()).toContain('Checking the API…')
  })

  it('shows the healthy backend and its version', () => {
    useBackendHealth.mockReturnValue({
      data: ref(healthyBackendResponse),
      error: ref(null),
      status: ref('success'),
      refresh: vi.fn()
    })

    const component = mount(HealthStatus)

    expect(component.text()).toContain('Backend healthy')
    expect(component.text()).toContain('RaceStreak API v0.1.0')
  })

  it('shows an empty state when no health data is returned', () => {
    useBackendHealth.mockReturnValue({
      data: ref(null),
      error: ref(null),
      status: ref('success'),
      refresh: vi.fn()
    })

    const component = mount(HealthStatus)

    expect(component.text()).toContain('No status data')
    expect(component.text()).toContain('No backend status has been received yet.')
  })

  it('shows an error state and retries the health request', async () => {
    const refresh = vi.fn()

    useBackendHealth.mockReturnValue({
      data: ref(null),
      error: ref(new Error('Backend unavailable')),
      status: ref('error'),
      refresh
    })

    const component = mount(HealthStatus)

    expect(component.text()).toContain('Backend unavailable')
    await component.get('button').trigger('click')
    expect(refresh).toHaveBeenCalledOnce()
  })
})

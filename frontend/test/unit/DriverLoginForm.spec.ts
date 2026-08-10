import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import DriverLoginForm from '../../app/components/DriverLoginForm.vue'

const signIn = vi.hoisted(() => vi.fn())

vi.mock('~/composables/useDriverAuthentication', () => ({
  useDriverAuthentication: () => ({ signIn })
}))

async function completeForm(component: ReturnType<typeof mount>) {
  await component.get('[name="email"]').setValue('driver@example.test')
  await component.get('[name="password"]').setValue('Correct-Horse-7!')
}

describe('DriverLoginForm', () => {
  beforeEach(() => {
    signIn.mockReset()
  })

  it('signs in and emits completion without retaining the password', async () => {
    signIn.mockResolvedValue({ id: 'driver-id' })
    const component = mount(DriverLoginForm)

    await completeForm(component)
    await component.get('form').trigger('submit')
    await flushPromises()

    expect(signIn).toHaveBeenCalledWith({
      email: 'driver@example.test',
      password: 'Correct-Horse-7!'
    })
    expect(component.emitted('signedIn')).toHaveLength(1)
    expect((component.get('[name="password"]').element as HTMLInputElement).value).toBe('')
  })

  it('shows the safe invalid-credentials message', async () => {
    signIn.mockRejectedValue({
      data: {
        success: false,
        error: {
          code: 'INVALID_CREDENTIALS',
          message: 'The provided credentials are invalid.'
        }
      }
    })
    const component = mount(DriverLoginForm)

    await completeForm(component)
    await component.get('form').trigger('submit')
    await flushPromises()

    expect(component.text()).toContain('The provided credentials are invalid.')
  })

  it('shows validation errors and marks affected fields', async () => {
    signIn.mockRejectedValue({
      data: {
        success: false,
        error: {
          code: 'VALIDATION_ERROR',
          message: 'The request could not be validated.',
          details: {
            fields: { email: ['The email field must be a valid email address.'] }
          }
        }
      }
    })
    const component = mount(DriverLoginForm)

    await completeForm(component)
    await component.get('form').trigger('submit')
    await flushPromises()

    expect(component.text()).toContain('The email field must be a valid email address.')
    expect(component.get('[name="email"]').attributes('aria-invalid')).toBe('true')
  })

  it('prevents duplicate submissions while sign in is pending', async () => {
    let resolveSignIn: ((value: unknown) => void) | undefined
    signIn.mockReturnValue(
      new Promise((resolve) => {
        resolveSignIn = resolve
      })
    )
    const component = mount(DriverLoginForm)

    await completeForm(component)
    await component.get('form').trigger('submit')
    await component.get('form').trigger('submit')

    expect(signIn).toHaveBeenCalledOnce()
    expect(component.get('button').attributes('disabled')).toBeDefined()
    expect(component.get('button').text()).toContain('Signing in...')

    resolveSignIn?.({ id: 'driver-id' })
    await flushPromises()
  })
})

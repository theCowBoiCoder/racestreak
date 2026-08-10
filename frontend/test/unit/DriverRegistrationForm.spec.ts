import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'

import DriverRegistrationForm from '../../app/components/DriverRegistrationForm.vue'

const registerDriver = vi.hoisted(() => vi.fn())

vi.mock('~/composables/useDriverRegistration', () => ({ registerDriver }))

async function completeForm(component: ReturnType<typeof mount>) {
  await component.get('[name="display_name"]').setValue('Apex Driver')
  await component.get('[name="email"]').setValue('driver@example.test')
  await component.get('[name="password"]').setValue('Correct-Horse-7!')
  await component.get('[name="password_confirmation"]').setValue('Correct-Horse-7!')
}

describe('DriverRegistrationForm', () => {
  it('submits the registration fields and shows the created account', async () => {
    registerDriver.mockResolvedValue({
      success: true,
      data: {
        id: '0198f42c-5a75-7a4f-ae19-4c44225bc2c7',
        display_name: 'Apex Driver',
        email: 'driver@example.test',
        email_verified: false,
        created_at: '2026-08-10T13:30:00Z'
      }
    })
    const component = mount(DriverRegistrationForm)

    await completeForm(component)
    await component.get('form').trigger('submit')
    await flushPromises()

    expect(registerDriver).toHaveBeenCalledWith({
      display_name: 'Apex Driver',
      email: 'driver@example.test',
      password: 'Correct-Horse-7!',
      password_confirmation: 'Correct-Horse-7!'
    })
    expect(component.text()).toContain('Account created')
    expect(component.text()).toContain('Welcome, Apex Driver.')
  })

  it('shows backend field errors safely', async () => {
    registerDriver.mockRejectedValue({
      data: {
        success: false,
        error: {
          code: 'VALIDATION_ERROR',
          message: 'The request could not be validated.',
          details: {
            fields: {
              email: ['The email has already been taken.'],
              password: ['The password must be at least 12 characters.']
            }
          }
        }
      }
    })
    const component = mount(DriverRegistrationForm)

    await completeForm(component)
    await component.get('form').trigger('submit')
    await flushPromises()

    expect(component.text()).toContain('The email has already been taken.')
    expect(component.text()).toContain('The password must be at least 12 characters.')
    expect(component.text()).toContain('The request could not be validated.')
    expect(component.get('[name="email"]').attributes('aria-invalid')).toBe('true')
  })

  it('prevents a duplicate submission while registration is pending', async () => {
    let resolveRegistration: ((value: unknown) => void) | undefined
    registerDriver.mockReturnValue(
      new Promise((resolve) => {
        resolveRegistration = resolve
      })
    )
    const component = mount(DriverRegistrationForm)

    await completeForm(component)
    await component.get('form').trigger('submit')
    await component.get('form').trigger('submit')

    expect(registerDriver).toHaveBeenCalledOnce()
    expect(component.get('button').attributes('disabled')).toBeDefined()
    expect(component.get('button').text()).toContain('Creating account')

    resolveRegistration?.({
      success: true,
      data: {
        id: '0198f42c-5a75-7a4f-ae19-4c44225bc2c7',
        display_name: 'Apex Driver',
        email: 'driver@example.test',
        email_verified: false,
        created_at: '2026-08-10T13:30:00Z'
      }
    })
    await flushPromises()
  })

  it('shows a generic error when registration is unavailable', async () => {
    registerDriver.mockRejectedValue(new Error('Network failure'))
    const component = mount(DriverRegistrationForm)

    await completeForm(component)
    await component.get('form').trigger('submit')
    await flushPromises()

    expect(component.text()).toContain('Registration is unavailable. Please try again.')
  })
})

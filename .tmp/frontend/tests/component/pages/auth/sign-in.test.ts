import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import SignIn from '@/pages/auth/sign-in.vue'

// Mock child components
vi.mock('@/pages/auth/components/auth-title.vue', () => ({
  default: {
    name: 'AuthTitle',
    template: '<div data-testid="auth-title">Auth Title</div>',
  },
}))

vi.mock('@/pages/auth/components/login-form.vue', () => ({
  default: {
    name: 'LoginForm',
    template: '<div data-testid="login-form">Login Form</div>',
  },
}))

describe('signIn Page', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('should render the sign-in page', () => {
    const wrapper = mount(SignIn)

    expect(wrapper.exists()).toBe(true)
  })

  it('should render AuthTitle component', () => {
    const wrapper = mount(SignIn)

    const authTitle = wrapper.find('[data-testid="auth-title"]')
    expect(authTitle.exists()).toBe(true)
  })

  it('should render LoginForm component', () => {
    const wrapper = mount(SignIn)

    const loginForm = wrapper.find('[data-testid="login-form"]')
    expect(loginForm.exists()).toBe(true)
  })

  it('should have correct layout classes', () => {
    const wrapper = mount(SignIn)

    const main = wrapper.find('main')
    expect(main.exists()).toBe(true)
    expect(main.classes()).toContain('flex')
    expect(main.classes()).toContain('flex-col')
    expect(main.classes()).toContain('gap-4')
  })

  it('should have correct container classes', () => {
    const wrapper = mount(SignIn)

    const container = wrapper.find('div')
    expect(container.exists()).toBe(true)
    expect(container.classes()).toContain('flex')
    expect(container.classes()).toContain('items-center')
    expect(container.classes()).toContain('justify-center')
    expect(container.classes()).toContain('min-h-screen')
  })
})

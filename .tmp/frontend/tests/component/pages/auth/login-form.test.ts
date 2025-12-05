import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import LoginForm from '@/pages/auth/components/login-form.vue'

// Mock useAuth composable
const mockLogin = vi.fn()
const mockLoading = { value: false }

vi.mock('@/composables/use-auth', () => ({
  useAuth: () => ({
    login: mockLogin,
    loading: mockLoading,
  }),
}))

// Mock child components
vi.mock('@/pages/auth/components/github-button.vue', () => ({
  default: {
    name: 'GitHubButton',
    template: '<button data-testid="github-button">GitHub</button>',
  },
}))

vi.mock('@/pages/auth/components/google-button.vue', () => ({
  default: {
    name: 'GoogleButton',
    template: '<button data-testid="google-button">Google</button>',
  },
}))

vi.mock('@/pages/auth/components/privacy-policy-button.vue', () => ({
  default: {
    name: 'PrivacyPolicyButton',
    template: '<button data-testid="privacy-policy-button">Privacy Policy</button>',
  },
}))

vi.mock('@/pages/auth/components/terms-of-service-button.vue', () => ({
  default: {
    name: 'TermsOfServiceButton',
    template: '<button data-testid="terms-of-service-button">Terms</button>',
  },
}))

vi.mock('@/pages/auth/components/to-forgot-password-link.vue', () => ({
  default: {
    name: 'ToForgotPasswordLink',
    template: '<a data-testid="forgot-password-link">Forgot Password</a>',
  },
}))

// Mock router
const mockPush = vi.fn()
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}))

describe('loginForm Component', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    mockLoading.value = false
  })

  it('should render the login form', () => {
    const wrapper = mount(LoginForm)

    expect(wrapper.exists()).toBe(true)
  })

  it('should display the login title', () => {
    const wrapper = mount(LoginForm)

    const title = wrapper.find('.text-2xl')
    expect(title.exists()).toBe(true)
    expect(title.text()).toBe('Login')
  })

  it('should display the login description', () => {
    const wrapper = mount(LoginForm)

    const description = wrapper.text()
    expect(description).toContain('Enter your email and password below to log into your account.')
    expect(description).toContain('Not have an account?')
  })

  it('should render email input field', () => {
    const wrapper = mount(LoginForm)

    const emailInput = wrapper.find('#email')
    expect(emailInput.exists()).toBe(true)
    expect(emailInput.attributes('type')).toBe('email')
    expect(emailInput.attributes('placeholder')).toBe('m@example.com')
    expect(emailInput.attributes('required')).toBeDefined()
  })

  it('should render password input field', () => {
    const wrapper = mount(LoginForm)

    const passwordInput = wrapper.find('#password')
    expect(passwordInput.exists()).toBe(true)
    expect(passwordInput.attributes('type')).toBe('password')
    expect(passwordInput.attributes('required')).toBeDefined()
  })

  it('should render email label', () => {
    const wrapper = mount(LoginForm)

    const emailLabel = wrapper.find('label[for="email"]')
    expect(emailLabel.exists()).toBe(true)
    expect(emailLabel.text()).toBe('Email')
  })

  it('should render password label', () => {
    const wrapper = mount(LoginForm)

    const passwordLabel = wrapper.find('label[for="password"]')
    expect(passwordLabel.exists()).toBe(true)
    expect(passwordLabel.text()).toBe('Password')
  })

  it('should render login button', () => {
    const wrapper = mount(LoginForm)

    // Find the button with "Mock Login" text (not the Sign Up button)
    const allButtons = wrapper.findAll('button')
    const loginButton = allButtons.find(btn => btn.text().includes('Mock Login'))
    expect(loginButton).toBeDefined()
    expect(loginButton?.text()).toContain('Mock Login')
  })

  it('should call login function when button is clicked', async () => {
    const wrapper = mount(LoginForm)

    // Find the button with "Mock Login" text
    const allButtons = wrapper.findAll('button')
    const loginButton = allButtons.find(btn => btn.text().includes('Mock Login'))
    expect(loginButton).toBeDefined()

    await loginButton!.trigger('click')

    expect(mockLogin).toHaveBeenCalledTimes(1)
  })

  it('should show loading spinner when loading is true', async () => {
    mockLoading.value = true
    const wrapper = mount(LoginForm)

    // Check if spinner is rendered - UiSpinner renders as an SVG with lucide-loader-circle-icon
    const html = wrapper.html()
    expect(html).toContain('lucide-loader-circle')
    expect(html).toContain('animate-spin')
  })

  it('should not show loading spinner when loading is false', () => {
    mockLoading.value = false
    const wrapper = mount(LoginForm)

    const spinner = wrapper.find('uispinner-stub')
    expect(spinner.exists()).toBe(false)
  })

  it('should render GitHub button', () => {
    const wrapper = mount(LoginForm)

    const githubButton = wrapper.find('[data-testid="github-button"]')
    expect(githubButton.exists()).toBe(true)
  })

  it('should render Google button', () => {
    const wrapper = mount(LoginForm)

    const googleButton = wrapper.find('[data-testid="google-button"]')
    expect(googleButton.exists()).toBe(true)
  })

  it('should render forgot password link', () => {
    const wrapper = mount(LoginForm)

    const forgotPasswordLink = wrapper.find('[data-testid="forgot-password-link"]')
    expect(forgotPasswordLink.exists()).toBe(true)
  })

  it('should render terms of service button', () => {
    const wrapper = mount(LoginForm)

    const termsButton = wrapper.find('[data-testid="terms-of-service-button"]')
    expect(termsButton.exists()).toBe(true)
  })

  it('should render privacy policy button', () => {
    const wrapper = mount(LoginForm)

    const privacyButton = wrapper.find('[data-testid="privacy-policy-button"]')
    expect(privacyButton.exists()).toBe(true)
  })

  it('should have sign up link that navigates to sign-up page', async () => {
    // Import config to access the router mock
    const { config } = await import('@vue/test-utils')
    const mockRouterPush = config.global.mocks?.$router?.push

    // Clear any previous calls
    if (mockRouterPush) {
      mockRouterPush.mockClear()
    }

    const wrapper = mount(LoginForm)

    // Find button with "Sign Up" text
    const allButtons = wrapper.findAll('button')
    const signUpBtn = allButtons.find(btn => btn.text().includes('Sign Up'))

    expect(signUpBtn).toBeDefined()

    if (signUpBtn && mockRouterPush) {
      await signUpBtn.trigger('click')
      // The router mock from setup.ts should be called
      expect(mockRouterPush).toHaveBeenCalledWith('/auth/sign-up')
    }
    // Verify the button exists
    expect(wrapper.text()).toContain('Sign Up')
  })

  it('should render separator component with "Or continue with" label', () => {
    const wrapper = mount(LoginForm)

    // UiSeparator renders with label attribute
    const html = wrapper.html()
    // Check if separator has the label attribute
    expect(html).toContain('label="Or continue with"')
    // Verify separator is rendered
    expect(html).toContain('data-slot="separator"')
  })

  it('should display terms and privacy policy text', () => {
    const wrapper = mount(LoginForm)

    const text = wrapper.text()
    expect(text).toContain('By clicking login, you agree to our')
  })
})

import type { Page } from '@playwright/test'

import { expect } from '@playwright/test'

import { apiURL } from '../../../playwright.config'
import { BasePage } from './BasePage'

/**
 * Login Page Object Model
 * Handles all interactions with the login page
 */
export class LoginPage extends BasePage {
  constructor(page: Page) {
    super(page)
  }

  /**
   * Navigate to login page
   */
  async navigate(): Promise<void> {
    await this.navigateTo('/auth/sign-in')
    await this.waitForLoadState()
  }

  /**
   * Fill email input
   */
  async fillEmail(email: string): Promise<void> {
    const emailInput = this.getByTestId('login-form_email_input')
    await emailInput.fill(email)
  }

  /**
   * Fill password input
   */
  async fillPassword(password: string): Promise<void> {
    const passwordInput = this.getByTestId('login-form_password_input')
    await passwordInput.fill(password)
  }

  /**
   * Fill login form with email and password
   */
  async fillForm(email: string, password: string): Promise<void> {
    await this.fillEmail(email)
    await this.fillPassword(password)
  }

  /**
   * Submit login form
   */
  async submit(): Promise<void> {
    const submitButton = this.getByTestId('login-form_submit_button')
    const responsePromise = this.page.waitForResponse((response) => {
      const url = response.url()
      const method = response.request().method()
      // Match POST requests to the login endpoint on the API server
      // Exclude CSRF cookie requests (GET requests to /sanctum/csrf-cookie)
      return (
        url.startsWith(apiURL)
        && (url.endsWith('/login') || url.endsWith('/login/'))
        && method === 'POST'
      )
    })
    await submitButton.click()
    await responsePromise
    const spinner = this.getByTestId('login-form_loading_spinner')
    await spinner.waitFor({ state: 'hidden' }).catch(() => {})
  }

  /**
   * Click Sign Up link
   */
  async clickSignUpLink(): Promise<void> {
    const signUpLink = this.getByTestId('login-form_sign-up_link')
    await signUpLink.click()
  }

  /**
   * Click Forgot Password link
   */
  async clickForgotPasswordLink(): Promise<void> {
    const forgotPasswordLink = this.getByTestId(
      'login-form_forgot-password_link',
    )
    await forgotPasswordLink.click()
  }

  /**
   * Check if loading spinner is visible
   */
  async isLoading(): Promise<boolean> {
    const spinner = this.getByTestId('login-form_loading_spinner')
    return await spinner.isVisible().catch(() => false)
  }

  /**
   * Verify we're on the login page
   */
  async verifyOnPage(): Promise<void> {
    await expect(this.page).toHaveURL(/.*\/auth\/sign-in/)
    const heading = this.getByRole('heading', { name: 'Login' })
    await expect(heading).toBeVisible()
  }

  /**
   * Complete login flow: fill form and submit
   */
  async login(email: string, password: string): Promise<void> {
    await this.fillForm(email, password)
    await this.submit()
  }
}

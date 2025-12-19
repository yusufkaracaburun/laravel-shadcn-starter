import type { Page } from '@playwright/test'

import { expect } from '@playwright/test'

import { apiURL } from '../../../playwright.config'
import { BasePage } from './BasePage'

/**
 * Register Page Object Model
 * Handles all interactions with the registration page
 */
export class RegisterPage extends BasePage {
  constructor(page: Page) {
    super(page)
  }

  /**
   * Navigate to register page
   */
  async navigate(): Promise<void> {
    await this.navigateTo('/auth/sign-up')
    await this.waitForLoadState()
  }

  /**
   * Fill name input
   */
  async fillName(name: string): Promise<void> {
    const nameInput = this.getByTestId('register-form_name_input')
    await nameInput.fill(name)
  }

  /**
   * Fill email input
   */
  async fillEmail(email: string): Promise<void> {
    const emailInput = this.getByTestId('register-form_email_input')
    await emailInput.fill(email)
  }

  /**
   * Fill password input
   */
  async fillPassword(password: string): Promise<void> {
    const passwordInput = this.getByTestId('register-form_password_input')
    await passwordInput.fill(password)
  }

  /**
   * Fill password confirmation input
   */
  async fillPasswordConfirmation(password: string): Promise<void> {
    const passwordConfirmationInput = this.getByTestId('register-form_password-confirmation_input')
    await passwordConfirmationInput.fill(password)
  }

  /**
   * Fill registration form with all fields
   */
  async fillForm(
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string,
  ): Promise<void> {
    await this.fillName(name)
    await this.fillEmail(email)
    await this.fillPassword(password)
    await this.fillPasswordConfirmation(passwordConfirmation)
  }

  /**
   * Submit registration form
   */
  async submit(): Promise<void> {
    const submitButton = this.getByTestId('register-form_submit_button')
    const responsePromise = this.page.waitForResponse((response) => {
      const url = response.url()
      const method = response.request().method()
      // Match POST requests to the register endpoint on the API server
      // Exclude CSRF cookie requests (GET requests to /sanctum/csrf-cookie)
      return (
        url.startsWith(apiURL) &&
        (url.endsWith('/register') || url.endsWith('/register/')) &&
        method === 'POST'
      )
    })
    await submitButton.click()
    await responsePromise
    const spinner = this.getByTestId('register-form_loading_spinner')
    await spinner.waitFor({ state: 'hidden' }).catch(() => {})
  }

  /**
   * Click Sign In link
   */
  async clickSignInLink(): Promise<void> {
    const signInLink = this.getByTestId('register-form_sign-in_link')
    await signInLink.click()
  }

  /**
   * Check if loading spinner is visible
   */
  async isLoading(): Promise<boolean> {
    const spinner = this.getByTestId('register-form_loading_spinner')
    return await spinner.isVisible().catch(() => false)
  }

  /**
   * Verify we're on the register page
   */
  async verifyOnPage(): Promise<void> {
    await expect(this.page).toHaveURL(/.*\/auth\/sign-up/)
    const heading = this.getByRole('heading', { name: 'Sign Up' })
    await expect(heading).toBeVisible()
  }

  /**
   * Complete registration flow: fill form and submit
   */
  async register(
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string,
  ): Promise<void> {
    await this.fillForm(name, email, password, passwordConfirmation)
    await this.submit()
  }
}

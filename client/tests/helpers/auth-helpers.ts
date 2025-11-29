import type { Page } from '@playwright/test'

export async function fillLoginForm(page: Page, email: string, password: string) {
  await page.getByLabel('Email').fill(email)
  await page.getByLabel('Password').fill(password)
}

export async function submitLoginForm(page: Page) {
  await page.getByRole('button', { name: 'Mock Login' }).click()
}

export async function fillRegisterForm(
  page: Page,
  firstName: string,
  lastName: string,
  email: string,
  password: string,
  passwordConfirmation: string,
) {
  // Use getByPlaceholder for text inputs (user-visible, follows Playwright best practices)
  await page.getByPlaceholder('Max').fill(firstName)
  await page.getByPlaceholder('Robinson').fill(lastName)
  await page.getByPlaceholder('m@example.com').fill(email)

  // For password fields: FormLabel components may not have proper label association
  // Using ID as fallback when getByLabel doesn't work (still better than CSS selectors)
  // Note: This is acceptable when label association isn't properly implemented in the component
  await page.locator('input[id="password"]').fill(password)
  await page.locator('input[id="password-confirmation"]').fill(passwordConfirmation)
}

export async function submitRegisterForm(page: Page) {
  await page.getByRole('button', { name: 'Create Account' }).click()
}

export async function waitForNavigationToDashboard(page: Page) {
  await page.waitForURL('**/dashboard', { timeout: 10000 })
}

export async function waitForNavigationToSignIn(page: Page) {
  await page.waitForURL('**/auth/sign-in', { timeout: 15000, waitUntil: 'networkidle' })
}

export async function navigateToLogin(page: Page) {
  await page.goto('/auth/sign-in')
  await page.waitForLoadState('networkidle')
}

export async function navigateToRegister(page: Page) {
  await page.goto('/auth/sign-up')
  await page.waitForLoadState('networkidle')
}

// Note: mockSuccessfulRegistration has been removed
// E2E tests should use the real API instead of mocks

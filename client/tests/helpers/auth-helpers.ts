import { Page } from '@playwright/test'

export async function fillLoginForm(page: Page, email: string, password: string) {
  await page.fill('input[id="email"]', email)
  await page.fill('input[id="password"]', password)
}

export async function submitLoginForm(page: Page) {
  await page.click('button[type="submit"]:has-text("Login")')
}

export async function fillRegisterForm(
  page: Page,
  firstName: string,
  lastName: string,
  email: string,
  password: string,
  passwordConfirmation: string,
) {
  await page.fill('input[id="first-name"]', firstName)
  await page.fill('input[id="last-name"]', lastName)
  await page.fill('input[id="email"]', email)
  await page.fill('input[id="password"]', password)
  await page.fill('input[id="password-confirmation"]', passwordConfirmation)
}

export async function submitRegisterForm(page: Page) {
  await page.click('button[type="submit"]:has-text("Create Account")')
}

export async function waitForNavigationToDashboard(page: Page) {
  await page.waitForURL('**/dashboard', { timeout: 10000 })
}

export async function waitForNavigationToSignIn(page: Page) {
  await page.waitForURL('**/auth/sign-in', { timeout: 10000 })
}

export async function navigateToLogin(page: Page) {
  await page.goto('/auth/sign-in')
  await page.waitForLoadState('networkidle')
}

export async function navigateToRegister(page: Page) {
  await page.goto('/auth/sign-up')
  await page.waitForLoadState('networkidle')
}


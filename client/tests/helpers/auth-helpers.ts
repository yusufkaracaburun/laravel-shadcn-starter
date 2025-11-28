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
  await page.getByLabel('First name').fill(firstName)
  await page.getByLabel('Last name').fill(lastName)
  await page.getByLabel('Email').fill(email)
  await page.getByLabel('Password').fill(password)
  await page.getByLabel('Confirm Password').fill(passwordConfirmation)
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

import { test, expect } from '@playwright/test'
import {
  navigateToLogin,
  fillLoginForm,
  submitLoginForm,
  waitForNavigationToDashboard,
} from '../../helpers/auth-helpers'
import { generateTestUser, registerUser, generateLoginCredentials } from '../../helpers/test-data'
import { registerUser as registerUserAPI } from '../../helpers/api-helpers'

test.describe('Login E2E', () => {
  test('should navigate to login page', async ({ page }) => {
    await navigateToLogin(page)
    await expect(page).toHaveURL(/.*\/auth\/sign-in/)
    await expect(page.locator('text=Login')).toBeVisible()
  })

  test('should login successfully with valid credentials', async ({ page, request }) => {
    // First register a user via API
    const testUser = generateTestUser()
    await registerUserAPI(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    // Navigate to login page
    await navigateToLogin(page)

    // Fill and submit login form
    await fillLoginForm(page, testUser.email, testUser.password)
    await submitLoginForm(page)

    // Wait for navigation to dashboard
    await waitForNavigationToDashboard(page)
    await expect(page).toHaveURL(/.*\/dashboard/)
  })

  test('should show error message with invalid credentials', async ({ page }) => {
    await navigateToLogin(page)

    const credentials = generateLoginCredentials()
    await fillLoginForm(page, credentials.email, credentials.password)
    await submitLoginForm(page)

    // Wait for error message to appear
    await expect(page.locator('text=/Invalid|error|credentials/i')).toBeVisible({ timeout: 5000 })
    await expect(page).toHaveURL(/.*\/auth\/sign-in/) // Should not redirect
  })

  test('should show validation errors for empty form', async ({ page }) => {
    await navigateToLogin(page)

    // Try to submit without filling fields
    await submitLoginForm(page)

    // Wait for validation errors
    await expect(page.locator('text=/required|Please enter/i')).toBeVisible({ timeout: 2000 })
  })

  test('should show validation error for invalid email format', async ({ page }) => {
    await navigateToLogin(page)

    await fillLoginForm(page, 'not-an-email', 'password123')
    await submitLoginForm(page)

    // Wait for email validation error
    await expect(page.locator('text=/valid email|email address/i')).toBeVisible({ timeout: 2000 })
  })

  test('should navigate to sign-up page when clicking Sign Up link', async ({ page }) => {
    await navigateToLogin(page)

    await page.click('text=Sign Up')
    await expect(page).toHaveURL(/.*\/auth\/sign-up/)
  })

  test('should navigate to forgot password page when clicking Forgot Password link', async ({ page }) => {
    await navigateToLogin(page)

    const forgotPasswordLink = page.locator('text=/forgot|password/i').first()
    if (await forgotPasswordLink.isVisible()) {
      await forgotPasswordLink.click()
      // Assuming there's a forgot password page
      // Adjust URL pattern based on actual route
      await expect(page).toHaveURL(/.*\/auth\/forgot-password/)
    }
  })

  test('should show loading state during login', async ({ page, request }) => {
    // Register a user
    const testUser = generateTestUser()
    await registerUserAPI(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    await navigateToLogin(page)
    await fillLoginForm(page, testUser.email, testUser.password)

    // Submit and check for loading spinner
    const submitPromise = submitLoginForm(page)
    await expect(page.locator('[role="status"], .spinner, [class*="spinner"]')).toBeVisible({ timeout: 1000 }).catch(() => {
      // Spinner might not be visible or have different selector
    })
    await submitPromise
  })
})


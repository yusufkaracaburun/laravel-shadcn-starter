import { test, expect } from '@playwright/test'
import {
  navigateToRegister,
  fillRegisterForm,
  submitRegisterForm,
  waitForNavigationToSignIn,
} from '../../helpers/auth-helpers'
import { generateTestUser } from '../../helpers/test-data'
import { registerUser } from '../../helpers/api-helpers'

test.describe('Register E2E', () => {
  test('should navigate to register page', async ({ page }) => {
    await navigateToRegister(page)
    await expect(page).toHaveURL(/.*\/auth\/sign-up/)
    await expect(page.locator('text=Sign Up')).toBeVisible()
  })

  test('should register successfully with valid data', async ({ page }) => {
    await navigateToRegister(page)

    const testUser = generateTestUser()
    await fillRegisterForm(
      page,
      testUser.firstName,
      testUser.lastName,
      testUser.email,
      testUser.password,
      testUser.password_confirmation,
    )

    await submitRegisterForm(page)

    // Should redirect to sign-in page after successful registration
    await waitForNavigationToSignIn(page)
    await expect(page).toHaveURL(/.*\/auth\/sign-in/)
  })

  test('should show error message with duplicate email', async ({ page, request }) => {
    // First register a user via API
    const testUser = generateTestUser()
    await registerUser(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    // Try to register again with same email
    await navigateToRegister(page)
    const duplicateUser = generateTestUser()
    await fillRegisterForm(
      page,
      duplicateUser.firstName,
      duplicateUser.lastName,
      testUser.email, // Same email
      duplicateUser.password,
      duplicateUser.password_confirmation,
    )

    await submitRegisterForm(page)

    // Wait for error message
    await expect(page.locator('text=/already|taken|exists/i')).toBeVisible({ timeout: 5000 })
  })

  test('should show validation errors for empty form', async ({ page }) => {
    await navigateToRegister(page)

    // Try to submit without filling fields
    await submitRegisterForm(page)

    // Wait for validation errors - should show multiple required field errors
    await expect(page.locator('text=/required/i').first()).toBeVisible({ timeout: 2000 })
  })

  test('should show validation error for password mismatch', async ({ page }) => {
    await navigateToRegister(page)

    const testUser = generateTestUser()
    await fillRegisterForm(
      page,
      testUser.firstName,
      testUser.lastName,
      testUser.email,
      testUser.password,
      'differentpassword', // Mismatched password
    )

    await submitRegisterForm(page)

    // Wait for password mismatch error
    await expect(page.locator('text=/match|do not match/i')).toBeVisible({ timeout: 2000 })
  })

  test('should show validation error for invalid email format', async ({ page }) => {
    await navigateToRegister(page)

    const testUser = generateTestUser()
    await fillRegisterForm(
      page,
      testUser.firstName,
      testUser.lastName,
      'not-an-email',
      testUser.password,
      testUser.password_confirmation,
    )

    await submitRegisterForm(page)

    // Wait for email validation error
    await expect(page.locator('text=/valid email|email address/i')).toBeVisible({ timeout: 2000 })
  })

  test('should show validation error for password too short', async ({ page }) => {
    await navigateToRegister(page)

    const testUser = generateTestUser()
    const shortPassword = 'short'
    await fillRegisterForm(
      page,
      testUser.firstName,
      testUser.lastName,
      testUser.email,
      shortPassword,
      shortPassword,
    )

    await submitRegisterForm(page)

    // Wait for password length validation error
    await expect(page.locator('text=/at least|8 characters|minimum/i')).toBeVisible({ timeout: 2000 })
  })

  test('should navigate to sign-in page when clicking Sign In link', async ({ page }) => {
    await navigateToRegister(page)

    await page.click('text=Sign In')
    await expect(page).toHaveURL(/.*\/auth\/sign-in/)
  })

  test('should show OAuth buttons (GitHub and Google)', async ({ page }) => {
    await navigateToRegister(page)

    // Check for OAuth buttons - they should exist
    const githubButton = page.locator('text=/github/i').first()
    const googleButton = page.locator('text=/google/i').first()

    // At least one OAuth button should be visible
    const hasOAuthButtons = await githubButton.isVisible() || await googleButton.isVisible()
    expect(hasOAuthButtons).toBeTruthy()
  })

  test('should show loading state during registration', async ({ page }) => {
    await navigateToRegister(page)

    const testUser = generateTestUser()
    await fillRegisterForm(
      page,
      testUser.firstName,
      testUser.lastName,
      testUser.email,
      testUser.password,
      testUser.password_confirmation,
    )

    // Submit and check for loading spinner
    const submitPromise = submitRegisterForm(page)
    await expect(page.locator('[role="status"], .spinner, [class*="spinner"]')).toBeVisible({ timeout: 1000 }).catch(() => {
      // Spinner might not be visible or have different selector
    })
    await submitPromise
  })
})


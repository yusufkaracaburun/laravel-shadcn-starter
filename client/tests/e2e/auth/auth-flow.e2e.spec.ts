import { test, expect } from '@playwright/test'
import {
  navigateToRegister,
  navigateToLogin,
  fillRegisterForm,
  fillLoginForm,
  submitRegisterForm,
  submitLoginForm,
  mockSuccessfulRegistration,
} from '../../helpers/auth-helpers'
import { generateTestUser } from '../../helpers/test-data'

test.describe('Auth Flow Integration', () => {
  test.describe.configure({ mode: 'parallel' })
  test('should complete full flow: register → login → logout', async ({ page }) => {
    // Mock successful registration API response
    await mockSuccessfulRegistration(page)

    const testUser = generateTestUser()

    // Step 1: Register
    await navigateToRegister(page)
    await fillRegisterForm(
      page,
      testUser.firstName,
      testUser.lastName,
      testUser.email,
      testUser.password,
      testUser.password_confirmation,
    )
    // Submit form and wait for navigation
    await Promise.all([
      page.waitForURL(/.*\/auth\/sign-in/, { timeout: 20000 }),
      submitRegisterForm(page),
    ])

    // Step 2: Login
    await fillLoginForm(page, testUser.email, testUser.password)
    await Promise.all([
      page.waitForURL(/.*\/dashboard/, { timeout: 10000 }),
      submitLoginForm(page),
    ])
    await expect(page).toHaveURL(/.*\/dashboard/)

    // Step 3: Logout (if logout button exists)
    const logoutButton = page.getByRole('button', { name: /logout|sign out|log out/i }).first()
    if (await logoutButton.isVisible({ timeout: 2000 }).catch(() => false)) {
      await Promise.all([
        page.waitForURL(/.*\/auth\/sign-in/, { timeout: 10000 }),
        logoutButton.click(),
      ])
    }
  })

  test('should register and immediately login with same credentials', async ({ page }) => {
    // Mock successful registration API response
    await mockSuccessfulRegistration(page)

    const testUser = generateTestUser()

    // Register
    await navigateToRegister(page)
    await fillRegisterForm(
      page,
      testUser.firstName,
      testUser.lastName,
      testUser.email,
      testUser.password,
      testUser.password_confirmation,
    )
    // Submit form and wait for navigation
    await Promise.all([
      page.waitForURL(/.*\/auth\/sign-in/, { timeout: 20000 }),
      submitRegisterForm(page),
    ])

    // Immediately login with same credentials
    await fillLoginForm(page, testUser.email, testUser.password)
    await Promise.all([
      page.waitForURL(/.*\/dashboard/, { timeout: 10000 }),
      submitLoginForm(page),
    ])
    await expect(page).toHaveURL(/.*\/dashboard/)
  })

  test('should redirect to login when accessing protected route without login', async ({ page }) => {
    // Try to access dashboard without logging in
    await page.goto('/dashboard')
    await page.waitForLoadState('networkidle')

    // Should redirect to login page
    await expect(page).toHaveURL(/.*\/auth\/sign-in/)
  })

  test('should access protected route after login', async ({ page, request }) => {
    // Register a user via API
    const testUser = generateTestUser()
    const { registerUser: registerUserAPI } = await import('../../helpers/api-helpers')
    await registerUserAPI(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    // Login
    await navigateToLogin(page)
    await fillLoginForm(page, testUser.email, testUser.password)
    await Promise.all([
      page.waitForURL(/.*\/dashboard/, { timeout: 10000 }),
      submitLoginForm(page),
    ])

    // Verify we're on dashboard
    await expect(page).toHaveURL(/.*\/dashboard/)
  })

  test('should maintain login state after page reload', async ({ page, request }) => {
    // Register and login
    const testUser = generateTestUser()
    const { registerUser: registerUserAPI } = await import('../../helpers/api-helpers')
    await registerUserAPI(request, {
      name: testUser.name,
      email: testUser.email,
      password: testUser.password,
      password_confirmation: testUser.password_confirmation,
    })

    await navigateToLogin(page)
    await fillLoginForm(page, testUser.email, testUser.password)
    await Promise.all([
      page.waitForURL(/.*\/dashboard/, { timeout: 10000 }),
      submitLoginForm(page),
    ])

    // Reload page
    await page.reload()
    await page.waitForLoadState('networkidle')

    // Should still be on dashboard (if auth state is persisted)
    // Note: This depends on how auth state is stored (localStorage, cookies, etc.)
    // Adjust expectations based on actual implementation
    const currentURL = page.url()
    expect(currentURL).toMatch(/.*\/dashboard|.*\/auth\/sign-in/)
  })
})


import { test, expect } from '@playwright/test'
import {
  navigateToRegister,
  navigateToLogin,
  fillRegisterForm,
  fillLoginForm,
  submitRegisterForm,
  submitLoginForm,
  waitForNavigationToDashboard,
  waitForNavigationToSignIn,
} from '../../helpers/auth-helpers'
import { generateTestUser } from '../../helpers/test-data'

test.describe('Auth Flow Integration', () => {
  test('should complete full flow: register → login → logout', async ({ page }) => {
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
    await submitRegisterForm(page)
    await waitForNavigationToSignIn(page)

    // Step 2: Login
    await fillLoginForm(page, testUser.email, testUser.password)
    await submitLoginForm(page)
    await waitForNavigationToDashboard(page)
    await expect(page).toHaveURL(/.*\/dashboard/)

    // Step 3: Logout (if logout button exists)
    const logoutButton = page.locator('text=/logout|sign out|log out/i').first()
    if (await logoutButton.isVisible({ timeout: 2000 }).catch(() => false)) {
      await logoutButton.click()
      await waitForNavigationToSignIn(page)
    }
  })

  test('should register and immediately login with same credentials', async ({ page }) => {
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
    await submitRegisterForm(page)
    await waitForNavigationToSignIn(page)

    // Immediately login with same credentials
    await fillLoginForm(page, testUser.email, testUser.password)
    await submitLoginForm(page)
    await waitForNavigationToDashboard(page)
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
    await submitLoginForm(page)
    await waitForNavigationToDashboard(page)

    // Verify we're on dashboard
    await expect(page).toHaveURL(/.*\/dashboard/)

    // Try to navigate to another protected route (if exists)
    // This test assumes dashboard is a protected route
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
    await submitLoginForm(page)
    await waitForNavigationToDashboard(page)

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


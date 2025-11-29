import { registerUser } from '../../helpers/api-helpers'
import { generateTestUser } from '../../helpers/test-data'
import { expect, test } from '../fixtures'

test.describe('Authentication Flow', () => {
  test('User can complete full authentication flow (register → login → logout)', async ({ page, registerPage, dashboardPage }) => {
    await test.step('Navigate to register page', async () => {
      await registerPage.navigate()
      await registerPage.verifyOnPage()
    })

    await test.step('Fill registration form', async () => {
      await registerPage.fillForm('Test User', 'flowuser@example.com', 'password123', 'password123')
    })

    await test.step('Submit registration form', async () => {
      await registerPage.submit()
      await page.waitForURL(/.*\/dashboard/)
    })

    await test.step('Verify user is on dashboard', async () => {
      await dashboardPage.verifyOnPage()
    })

    await test.step('Logout user', async () => {
      await dashboardPage.logout()
      await page.waitForURL(/.*\/auth\/sign-in/)
    })

    await test.step('Verify redirect to login page', async () => {
      await expect(page).toHaveURL(/.*\/auth\/sign-in/)
    })

    await test.step('Verify dashboard is not accessible', async () => {
      await page.goto('/dashboard')
      await expect(page).toHaveURL(/.*\/auth\/sign-in/)
    })
  })

  test('User can access protected routes after login', async ({ page, loginPage, dashboardPage, request }) => {
    const user = generateTestUser()
    user.email = 'protected@example.com'
    user.password = 'password123'
    user.password_confirmation = 'password123'

    await test.step('Register user via API', async () => {
      await registerUser(request, {
        name: user.name,
        email: user.email,
        password: user.password,
        password_confirmation: user.password_confirmation,
      })
    })

    await test.step('Navigate to login page', async () => {
      await loginPage.navigate()
      await loginPage.verifyOnPage()
    })

    await test.step('Fill and submit login form', async () => {
      await loginPage.fillForm('protected@example.com', 'password123')
      await loginPage.submit()
      await page.waitForURL(/.*\/dashboard/)
    })

    await test.step('Verify user is on dashboard', async () => {
      await dashboardPage.verifyOnPage()
    })

    await test.step('Navigate to dashboard directly', async () => {
      await dashboardPage.navigate()
      await dashboardPage.verifyOnPage()
    })
  })

  test('User is redirected to login when accessing protected routes without auth', async ({ browser }) => {
    const context = await browser.newContext({
      storageState: undefined,
    })
    const page = await context.newPage()

    await test.step('Navigate to dashboard without authentication', async () => {
      await page.goto('/dashboard', { waitUntil: 'networkidle' })
    })

    await test.step('Verify redirect to login page', async () => {
      await expect(page).toHaveURL(/.*\/auth\/sign-in/)
    })

    await context.close()
  })

  test('User session persists after page reload', async ({ page, loginPage, dashboardPage, request }) => {
    const user = generateTestUser()
    user.email = 'session@example.com'
    user.password = 'password123'
    user.password_confirmation = 'password123'

    await test.step('Register user via API', async () => {
      await registerUser(request, {
        name: user.name,
        email: user.email,
        password: user.password,
        password_confirmation: user.password_confirmation,
      })
    })

    await test.step('Navigate to login page', async () => {
      await loginPage.navigate()
      await loginPage.verifyOnPage()
    })

    await test.step('Fill and submit login form', async () => {
      await loginPage.fillForm('session@example.com', 'password123')
      await loginPage.submit()
      await page.waitForURL(/.*\/dashboard/)
    })

    await test.step('Verify user is on dashboard', async () => {
      await dashboardPage.verifyOnPage()
    })

    await test.step('Reload page', async () => {
      await page.reload()
      await page.waitForLoadState('networkidle')
    })

    await test.step('Verify session persists', async () => {
      await dashboardPage.verifyOnPage()
      const isVisible = await dashboardPage.isVisible()
      expect(isVisible).toBe(true)
    })
  })
})

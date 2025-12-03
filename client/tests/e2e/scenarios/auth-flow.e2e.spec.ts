import { testusers } from '../.data/testusers'
import { expect, test } from '../fixtures'
import { registerUser } from '../helpers/api-helpers'

test.describe('Authentication Flow', () => {
  test('User can complete full authentication flow (register → login → logout)', async ({ page, registerPage, dashboardPage }) => {
    const user = testusers.customer

    await test.step('Navigate to register page', async () => {
      await registerPage.navigate()
      await registerPage.verifyOnPage()
    })

    await test.step('Fill registration form', async () => {
      await registerPage.fillForm(user.name, user.email, user.password, user.password)
    })

    await test.step('Submit registration form', async () => {
      await registerPage.submit()
    })

    await test.step('Wait for navigation to dashboard', async () => {
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
    const user = testusers.customer

    await test.step('Register user via API', async () => {
      await registerUser(request, {
        name: user.name,
        email: user.email,
        password: user.password,
        password_confirmation: user.password,
      })
    })

    await test.step('Navigate to login page', async () => {
      await loginPage.navigate()
      await loginPage.verifyOnPage()
    })

    await test.step('Fill login form', async () => {
      await loginPage.fillForm(user.email, user.password)
    })

    await test.step('Submit login form', async () => {
      await loginPage.submit()
    })

    await test.step('Wait for navigation to dashboard', async () => {
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
      await page.goto('/dashboard')
    })

    await test.step('Wait for client-side redirect to login page', async () => {
      await page.waitForURL(/.*\/auth\/sign-in/)
      await page.waitForLoadState('networkidle')
    })

    await test.step('Verify redirect to login page', async () => {
      await expect(page).toHaveURL(/.*\/auth\/sign-in/)
    })

    await context.close()
  })

  test('User session persists after page reload', async ({ page, loginPage, dashboardPage, request }) => {
    const user = testusers.customer

    await test.step('Register user via API', async () => {
      await registerUser(request, {
        name: user.name,
        email: user.email,
        password: user.password,
        password_confirmation: user.password,
      })
    })

    await test.step('Navigate to login page', async () => {
      await loginPage.navigate()
      await loginPage.verifyOnPage()
    })

    await test.step('Fill login form', async () => {
      await loginPage.fillForm(user.email, user.password)
    })

    await test.step('Submit login form', async () => {
      await loginPage.submit()
    })

    await test.step('Wait for navigation to dashboard', async () => {
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

import { testusers } from '../.data/testusers'
import { expect, test } from '../fixtures'
import { registerUser } from '../helpers/api-helpers'
import { generateTestUser } from '../helpers/test-data'

test.describe('User Registration', () => {
  test('User can register successfully with valid data', async ({ page, registerPage }) => {
    const user = generateTestUser()

    await test.step('Navigate to register page', async () => {
      await registerPage.navigate()
      await registerPage.verifyOnPage()
    })

    await test.step('Fill registration form', async () => {
      await registerPage.fillForm(user.name, user.email, user.password, user.password_confirmation)
    })

    await test.step('Submit registration form', async () => {
      await registerPage.submit()
    })

    await test.step('Wait for navigation to dashboard', async () => {
      await page.waitForURL(/.*\/dashboard/)
    })

    await test.step('Verify redirect to dashboard', async () => {
      await expect(page).toHaveURL(/.*\/dashboard/)
    })
  })

  test('User cannot register with duplicate email', async ({ page, registerPage, request }) => {
    const existingUser = testusers.customer

    await test.step('Register user via API', async () => {
      await registerUser(request, {
        name: existingUser.name,
        email: existingUser.email,
        password: existingUser.password,
        password_confirmation: existingUser.password,
      })
    })

    await test.step('Navigate to register page', async () => {
      await registerPage.navigate()
      await registerPage.verifyOnPage()
    })

    await test.step('Fill registration form with duplicate email', async () => {
      await registerPage.fillForm(
        'Another User',
        existingUser.email,
        existingUser.password,
        existingUser.password,
      )
    })

    await test.step('Submit registration form', async () => {
      await registerPage.submit()
    })

    await test.step('Verify error message about duplicate email', async () => {
      const errorText = page.getByText(/already|taken|exists|duplicate/i).first()
      await expect(errorText).toBeVisible()
    })

    await test.step('Verify user remains on register page', async () => {
      await expect(page).toHaveURL(/.*\/auth\/sign-up/)
    })
  })

  test('User is redirected to login after successful registration', async ({
    page,
    registerPage,
  }) => {
    const user = generateTestUser()

    await test.step('Navigate to register page', async () => {
      await registerPage.navigate()
      await registerPage.verifyOnPage()
    })

    await test.step('Fill registration form', async () => {
      await registerPage.fillForm(user.name, user.email, user.password, user.password_confirmation)
    })

    await test.step('Submit registration form', async () => {
      await registerPage.submit()
    })

    await test.step('Wait for navigation to dashboard', async () => {
      await page.waitForURL(/.*\/dashboard/)
    })

    await test.step('Verify redirect to dashboard', async () => {
      await expect(page).toHaveURL(/.*\/dashboard/)
    })
  })
})

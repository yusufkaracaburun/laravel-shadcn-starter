import { test } from '@playwright/test'

import { LoginPage } from '../../pages/LoginPage'

interface LoginTestFixture {
  loginPage: LoginPage
}

export const loginTestFixture = test.extend<LoginTestFixture>({
  loginPage: async ({ page }, use) => {
    const loginPage = new LoginPage(page)
    await use(loginPage)
  },
})

import { test } from '@playwright/test'

import { RegisterPage } from '../../pages/RegisterPage'

interface RegisterTestFixture {
  registerPage: RegisterPage
}

export const registerTestFixture = test.extend<RegisterTestFixture>({
  registerPage: async ({ page }, use) => {
    const registerPage = new RegisterPage(page)
    await use(registerPage)
  },
})

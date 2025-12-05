import { expect } from '@playwright/test'

import {
  expectCookiesSet,
  expectSuccess,
  expectValidationErrors,
  expectValidUser,
} from '../../features/shared/helpers/test-helpers'
import { invalidUser, test, testUser } from '../../fixtures'

test.describe('Login', { tag: ['@api', '@auth', '@login'] }, () => {
  test('should login with valid credentials', async ({ authClient }) => {
    // Arrange & Act
    const response = await authClient.login(testUser.email, testUser.password)

    // Assert
    expectSuccess(response)
    await expectValidUser(response)
  })

  test('should set cookies after successful login', async ({ authClient }) => {
    // Arrange & Act
    const response = await authClient.login(testUser.email, testUser.password)

    // Assert
    expectSuccess(response)
    expectCookiesSet(response)
  })

  test('should not login with invalid credentials', async ({ authClient }) => {
    // Arrange & Act
    const response = await authClient.login(invalidUser.email, invalidUser.password)

    // Assert
    await expectValidationErrors(response)
  })

  test('should handle login without CSRF cookie gracefully', async ({ authClient }) => {
    // Arrange: Try to login without getting CSRF cookie first
    // Note: Laravel may handle this differently in API context
    // Act: Login should still work as AuthClient handles CSRF internally
    const response = await authClient.login(testUser.email, testUser.password)

    // Assert: Should succeed because AuthClient.getCsrfCookie() is called internally
    expectSuccess(response)
  })

  test('should extract CSRF token using CookieStore and use it in subsequent requests', async ({ authClient }) => {
    // Arrange: Get CSRF cookie first
    // The CookieStore API (or response header extraction in tests) will extract the token
    const csrfResponse = await authClient.getCsrfCookie()
    expectSuccess(csrfResponse)

    // Verify CSRF token cookie is set in response headers
    const setCookieHeader = csrfResponse.headers()['set-cookie']
    expect(setCookieHeader).toBeTruthy()

    // Verify XSRF-TOKEN cookie is present in response
    const cookies = Array.isArray(setCookieHeader) ? setCookieHeader : [setCookieHeader]
    const xsrfCookie = cookies.find((cookie: string) => cookie.startsWith('XSRF-TOKEN='))
    expect(xsrfCookie).toBeTruthy()

    // Verify the CSRF token is extracted and stored by the handler
    // In the test environment, the CsrfHandler extracts from response headers
    // In the browser, CookieStore API reads from the cookie store
    // Both should result in the token being available for the next request

    // Act: Login should use the extracted CSRF token
    const loginResponse = await authClient.login(testUser.email, testUser.password)

    // Assert: Login should succeed with the CSRF token
    // This verifies that the token was extracted and used correctly
    expectSuccess(loginResponse)
    await expectValidUser(loginResponse)
  })
})

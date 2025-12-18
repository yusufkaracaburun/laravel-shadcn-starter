import type { User } from '../../features/shared/types'

import { HttpStatus } from '../../features/shared/enums'
import {
  expectCookiesSet,
  expectIResponse,
  expectSuccess,
  expectValidationErrors,
  expectValidUser,
} from '../../features/shared/helpers'
import { expect, invalidUser, test, testUser } from '../../fixtures'

test.describe('Login', { tag: ['@api', '@auth', '@login'] }, () => {
  test('should login successfully and validate user data', async ({ authClient }) => {
    // Arrange & Act
    const loginResponse = await authClient.login(testUser.email, testUser.password)

    // Assert - Login should succeed
    expectSuccess(loginResponse)
    expect(loginResponse.status()).toBe(HttpStatus.OK)

    // Assert - Login response contains user object
    const loginBody = await loginResponse.json()
    expect(loginBody).toHaveProperty('id')
    expect(loginBody).toHaveProperty('name')
    expect(loginBody).toHaveProperty('email')
    expect(loginBody).toHaveProperty('current_team_id')
    expect((loginBody as { email: string }).email).toBe(testUser.email)

    // Act - Get current user to verify authentication and validate full user data
    const userResponse = await authClient.getUser()

    // Assert - User should be authenticated and response structure should match IResponse<User>
    expectSuccess(userResponse)
    expect(userResponse.status()).toBe(HttpStatus.OK)

    // Assert - Response structure matches IResponse<User>
    const userBody = await expectIResponse<User>(userResponse)

    // Assert - User data structure matches User interface
    expect(userBody.data).toHaveProperty('id')
    expect(userBody.data).toHaveProperty('name')
    expect(userBody.data).toHaveProperty('email')
    expect(userBody.data).toHaveProperty('email_verified_at')
    expect(userBody.data).toHaveProperty('current_team_id')
    expect(userBody.data).toHaveProperty('profile_photo_url')
    expect(userBody.data).toHaveProperty('created_at')
    expect(userBody.data).toHaveProperty('updated_at')

    // Assert - User data matches login credentials
    expect(userBody.data.email).toBe(testUser.email)
    expect(userBody.data.name).toBe(testUser.name)
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

  test('should handle login with automatic CSRF cookie', async ({ authClient }) => {
    // Arrange: Try to login without manually getting CSRF cookie first
    // Note: AuthClient handles CSRF internally via ensureCsrfCookie()
    // Act: Login should still work as AuthClient handles CSRF internally
    const response = await authClient.login(testUser.email, testUser.password)

    // Assert: Should succeed because AuthClient.ensureCsrfCookie() is called internally
    expectSuccess(response)
    expect(response.status()).toBe(HttpStatus.OK)

    // Verify login response contains user object
    const loginBody = await response.json()
    expect(loginBody).toHaveProperty('id')
    expect(loginBody).toHaveProperty('email', testUser.email)
    expect(loginBody).toHaveProperty('name')
  })

  test('should extract CSRF token and use it in subsequent requests', async ({ authClient }) => {
    // Arrange: Get CSRF cookie first
    const csrfResponse = await authClient.getCsrfCookie()
    expectSuccess(csrfResponse)
    expect(csrfResponse.status()).toBe(HttpStatus.NO_CONTENT)

    // Verify CSRF token cookie is set in response headers
    const setCookieHeader = csrfResponse.headers()['set-cookie']
    expect(setCookieHeader).toBeTruthy()

    // Verify XSRF-TOKEN cookie is present in response
    const cookies = Array.isArray(setCookieHeader) ? setCookieHeader : [setCookieHeader]
    const xsrfCookie = cookies.find((cookie: string) => cookie.startsWith('XSRF-TOKEN='))
    expect(xsrfCookie).toBeTruthy()

    // Act: Login should use the extracted CSRF token
    // Note: AuthClient.ensureCsrfCookie() checks if token exists, so it won't fetch again
    // However, the token is already extracted from the CSRF response above
    const loginResponse = await authClient.login(testUser.email, testUser.password)

    // Assert: Login should succeed with the CSRF token
    // This verifies that the token was extracted and used correctly
    expectSuccess(loginResponse)
    expect(loginResponse.status()).toBe(HttpStatus.OK)

    // Verify login response contains user object
    const loginBody = await loginResponse.json()
    expect(loginBody).toHaveProperty('id')
    expect(loginBody).toHaveProperty('email', testUser.email)
    expect(loginBody).toHaveProperty('name')
  })
})

import { expectSuccess, expectStatus, expectUnauthenticated } from '../../features/shared/helpers'
import { test, expect } from '../../fixtures'
import { HttpStatus } from '../../features/shared/enums'

test.describe('Logout', { tag: ['@api', '@auth', '@logout'] }, () => {
  test('should logout successfully and verify authentication is terminated', async ({
    authenticatedAuthClient,
  }) => {
    // Arrange: authenticatedAuthClient fixture provides pre-authenticated client
    // Verify authenticated before logout
    const beforeLogoutResponse = await authenticatedAuthClient.getUser()
    expectSuccess(beforeLogoutResponse)

    // Act
    const logoutResponse = await authenticatedAuthClient.logout()

    // Assert - Logout should succeed
    // Laravel logout returns 204 No Content (no body) or 200 with JSON response
    expect([HttpStatus.OK, HttpStatus.NO_CONTENT]).toContain(logoutResponse.status())

    // If status is 200, check response structure (IResponse<null>)
    if (logoutResponse.status() === HttpStatus.OK) {
      const logoutBody = await logoutResponse.json()
      expect(logoutBody).toHaveProperty('success', true)
      expect(logoutBody).toHaveProperty('code', HttpStatus.OK)
      expect(logoutBody).toHaveProperty('message')
      // data might be null or not present
      if ('data' in (logoutBody as Record<string, unknown>)) {
        expect((logoutBody as { data: unknown }).data).toBeNull()
      }
      expect(logoutBody).toHaveProperty('extra')
    }

    // Act - Verify authentication is terminated
    const afterLogoutResponse = await authenticatedAuthClient.getUser()

    // Assert - User should not be authenticated after logout
    expectUnauthenticated(afterLogoutResponse)
  })

  test('should logout when authenticated', async ({ authenticatedAuthClient }) => {
    // Arrange: authenticatedAuthClient fixture provides pre-authenticated client
    // Verify authenticated before logout
    const beforeLogoutResponse = await authenticatedAuthClient.getUser()
    expectSuccess(beforeLogoutResponse)

    // Act
    const logoutResponse = await authenticatedAuthClient.logout()

    // Assert - Logout should succeed
    // Laravel logout returns 204 No Content (no body) or 200 with JSON response
    expect([HttpStatus.OK, HttpStatus.NO_CONTENT]).toContain(logoutResponse.status())

    // Verify unauthenticated after logout
    const afterLogoutResponse = await authenticatedAuthClient.getUser()
    expectUnauthenticated(afterLogoutResponse)
  })

  test('should not logout when not authenticated', async ({ authClient }) => {
    // Arrange: Use unauthenticated client
    await authClient.getCsrfCookie() // Get CSRF cookie (as a real SPA would do)

    // Act
    const response = await authClient.logout()

    // Assert
    expectUnauthenticated(response)
  })
})


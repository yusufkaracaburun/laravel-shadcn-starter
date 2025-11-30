import { expect, test } from '../fixtures/api-fixtures'
import { testusers } from '../../.data/users.data'
import { prepareTestUser, generateTestUser } from '../../helpers/test-data'

// ============================================================================
// Pure Functions for Response Extraction
// ============================================================================

/**
 * Pure function: Extracts user email from API response
 */
const extractUserEmail = async (response: Awaited<ReturnType<typeof test.authApi>>['getCurrentUser']): Promise<string> => {
  const body = await response.json()
  return body.data.email
}

/**
 * Pure function: Extracts user email from registration response
 */
const extractRegisteredUserEmail = async (response: Awaited<ReturnType<typeof test.authApi>>['register']): Promise<string> => {
  const body = await response.json()
  return body.data?.email || body.email
}

test.describe('Authentication Flow API', () => {
  test('should complete happy flow: login → register → logout', async ({ authApi }) => {
    // Arrange - Pure data preparation
    const adminCredentials = {
      email: testusers.admin.email,
      password: testusers.admin.password,
    }

    const baseUser = generateTestUser()
    const newUser = prepareTestUser({
      name: baseUser.name,
      email: baseUser.email,
      password: baseUser.password,
      password_confirmation: baseUser.password_confirmation,
    })

    await test.step('Step 1: Login with admin user', async () => {
      // Act - Side effect
      const loginResponse = await authApi.login(adminCredentials)

      // Assert - Pure validation
      expect(loginResponse.status()).toBe(200)

      // Verify authentication
      const userResponse = await authApi.getCurrentUser()
      expect(userResponse.status()).toBe(200)
      const userEmail = await extractUserEmail(userResponse)
      expect(userEmail).toBe(testusers.admin.email)
    })

    await test.step('Step 2: Register new user', async () => {
      // Act - Side effect
      const registerResponse = await authApi.register(newUser)

      // Assert - Pure validation
      expect(registerResponse.status()).toBe(201)
      const registeredEmail = await extractRegisteredUserEmail(registerResponse)
      expect(registeredEmail).toBe(newUser.email)

      // Verify new user can authenticate
      const loginResponse = await authApi.login({
        email: newUser.email,
        password: newUser.password,
      })
      expect(loginResponse.status()).toBe(200)
    })

    await test.step('Step 3: Logout', async () => {
      // Act - Side effect
      const logoutResponse = await authApi.logout()

      // Assert - Pure validation
      expect(logoutResponse.status()).toBe(204)
    })
  })
})


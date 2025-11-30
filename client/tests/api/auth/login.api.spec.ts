import { expect, test } from '../fixtures/api-fixtures'
import { testusers } from '../../.data/users.data'

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
 * Pure function: Extracts two_factor property from login response
 */
const extractTwoFactor = async (response: Awaited<ReturnType<typeof test.authApi>>['login']): Promise<boolean> => {
  const body = await response.json()
  return body.two_factor ?? false
}

test.describe('Login API', () => {
  test('should authenticate admin user successfully', async ({ authApi }) => {
    // Arrange - Pure data preparation
    const credentials = {
      email: testusers.admin.email,
      password: testusers.admin.password,
    }

    await test.step('Login with admin credentials', async () => {
      // Act - Side effect (API call via curried function)
      const response = await authApi.login(credentials)

      // Assert - Pure validation
      expect(response.status()).toBe(200)
      const twoFactor = await extractTwoFactor(response)
      expect(typeof twoFactor).toBe('boolean')
    })

    await test.step('Verify authentication state', async () => {
      // Act - Side effect
      const userResponse = await authApi.getCurrentUser()

      // Assert - Pure validation
      expect(userResponse.status()).toBe(200)
      const userEmail = await extractUserEmail(userResponse)
      expect(userEmail).toBe(testusers.admin.email)
    })
  })

  test('should return 422 for invalid credentials', async ({ authApi }) => {
    // Arrange - Pure data preparation
    const invalidCredentials = {
      email: testusers.admin.email,
      password: 'wrong-password',
    }

    await test.step('Attempt login with invalid password', async () => {
      // Act - Side effect
      const response = await authApi.login(invalidCredentials)

      // Assert - Pure validation
      expect(response.status()).toBe(422)
      const body = await response.json()
      expect(body).toHaveProperty('errors')
      expect(body.errors).toHaveProperty('email')
    })
  })

  test('should return 422 for non-existent user', async ({ authApi }) => {
    // Arrange - Pure data preparation
    const nonExistentCredentials = {
      email: 'nonexistent@example.com',
      password: 'password',
    }

    await test.step('Attempt login with non-existent email', async () => {
      // Act - Side effect
      const response = await authApi.login(nonExistentCredentials)

      // Assert - Pure validation
      expect(response.status()).toBe(422)
      const body = await response.json()
      expect(body).toHaveProperty('errors')
    })
  })
})


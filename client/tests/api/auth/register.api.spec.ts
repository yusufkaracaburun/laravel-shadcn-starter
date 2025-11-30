import { expect, test } from '../fixtures/api-fixtures'
import { prepareTestUser } from '../../helpers/test-data'
import { generateTestUser } from '../../helpers/test-data'

// ============================================================================
// Pure Functions for Response Extraction
// ============================================================================

/**
 * Pure function: Extracts user ID from registration response
 */
const extractUserId = async (response: Awaited<ReturnType<typeof test.authApi>>['register']): Promise<number> => {
  const body = await response.json()
  return body.data?.id || body.id
}

/**
 * Pure function: Extracts user email from registration response
 */
const extractUserEmail = async (response: Awaited<ReturnType<typeof test.authApi>>['register']): Promise<string> => {
  const body = await response.json()
  return body.data?.email || body.email
}

test.describe('Register API', () => {
  test('should register new user successfully', async ({ authApi }) => {
    // Arrange - Pure data preparation using composed function
    const baseUser = generateTestUser()
    const testUser = prepareTestUser({
      name: baseUser.name,
      email: baseUser.email,
      password: baseUser.password,
      password_confirmation: baseUser.password_confirmation,
    })

    await test.step('Register new user', async () => {
      // Act - Side effect (API call via curried function)
      const response = await authApi.register(testUser)

      // Assert - Pure validation
      expect(response.status()).toBe(201)
      const userEmail = await extractUserEmail(response)
      expect(userEmail).toBe(testUser.email)
    })

    await test.step('Verify user can authenticate after registration', async () => {
      // Act - Side effect
      const loginResponse = await authApi.login({
        email: testUser.email,
        password: testUser.password,
      })

      // Assert - Pure validation
      expect(loginResponse.status()).toBe(200)
    })
  })

  test('should return 422 for duplicate email', async ({ authApi }) => {
    // Arrange - Pure data preparation
    const baseUser = generateTestUser()
    const testUser = prepareTestUser({
      name: baseUser.name,
      email: baseUser.email,
      password: baseUser.password,
      password_confirmation: baseUser.password_confirmation,
    })

    await test.step('Register user first time', async () => {
      // Act - Side effect
      const response = await authApi.register(testUser)

      // Assert - Pure validation
      expect(response.status()).toBe(201)
    })

    await test.step('Attempt to register with same email', async () => {
      // Arrange - Pure data preparation
      const duplicateUser = {
        name: 'Another User',
        email: testUser.email, // Same email
        password: 'password123',
        password_confirmation: 'password123',
      }

      // Act - Side effect
      const response = await authApi.register(duplicateUser)

      // Assert - Pure validation
      expect(response.status()).toBe(422)
      const body = await response.json()
      expect(body).toHaveProperty('errors')
      expect(body.errors).toHaveProperty('email')
    })
  })

  test('should return 422 for invalid data', async ({ authApi }) => {
    // Arrange - Pure data preparation
    const invalidData = {
      name: '',
      email: 'invalid-email',
      password: '123', // Too short
      password_confirmation: '456', // Mismatch
    }

    await test.step('Attempt registration with invalid data', async () => {
      // Act - Side effect
      const response = await authApi.register(invalidData)

      // Assert - Pure validation
      expect(response.status()).toBe(422)
      const body = await response.json()
      expect(body).toHaveProperty('errors')
      expect(body.errors).toHaveProperty('email')
      expect(body.errors).toHaveProperty('password')
    })
  })
})


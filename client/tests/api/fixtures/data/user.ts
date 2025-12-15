import { testusers } from '../../../.data/users.data'

/**
 * Test user data for API tests
 * Uses existing testusers from .data/users.data.ts
 */
export const testUser = testusers.test

export const invalidUser = {
  email: 'invalid@example.com',
  password: 'wrongpassword',
}

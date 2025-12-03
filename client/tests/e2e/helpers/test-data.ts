import { faker } from '@faker-js/faker'

export interface TestUser {
  name: string
  firstName: string
  lastName: string
  email: string
  password: string
  password_confirmation: string
}

export interface UserData {
  name: string
  email: string
  password: string
  password_confirmation?: string
}

// ============================================================================
// Pure Functions for Test Data Transformation
// ============================================================================

/**
 * Pure function: Adds timestamp to email to make it unique
 */
export function addTimestamp(email: string): string {
  const [localPart, domain] = email.split('@')
  return `${localPart}-${Date.now()}@${domain}`
}

/**
 * Pure function: Creates unique email from base email
 */
export function createUniqueEmail(base: string): string {
  return addTimestamp(base)
}

/**
 * Pure function: Creates unique user data from base user (immutable)
 */
export function createUniqueUser(user: UserData): UserData {
  return {
    ...user,
    email: createUniqueEmail(user.email),
    password_confirmation: user.password_confirmation || user.password,
  }
}

/**
 * Composed function: Prepares test user with unique email
 * This composes createUniqueUser and addTimestamp
 */
export function prepareTestUser(baseUser: UserData): UserData {
  return createUniqueUser(baseUser)
}

// ============================================================================
// Existing Function (kept for backward compatibility)
// ============================================================================

/**
 * Generates a new test user with random data
 */
export function generateTestUser(): TestUser {
  const firstName = faker.person.firstName()
  const lastName = faker.person.lastName()
  const password = faker.internet.password({ length: 12 })

  return {
    name: `${firstName} ${lastName}`,
    firstName,
    lastName,
    email: faker.internet.email(),
    password,
    password_confirmation: password,
  }
}

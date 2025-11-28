import { faker } from '@faker-js/faker'

export interface TestUser {
  name: string
  firstName: string
  lastName: string
  email: string
  password: string
  password_confirmation: string
}

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

export function generateTestUserWithEmail(email: string): TestUser {
  const firstName = faker.person.firstName()
  const lastName = faker.person.lastName()
  const password = faker.internet.password({ length: 12 })

  return {
    name: `${firstName} ${lastName}`,
    firstName,
    lastName,
    email,
    password,
    password_confirmation: password,
  }
}

export function generateLoginCredentials(email?: string, password?: string) {
  return {
    email: email || faker.internet.email(),
    password: password || faker.internet.password({ length: 12 }),
  }
}

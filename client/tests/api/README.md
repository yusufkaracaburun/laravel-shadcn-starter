# API Tests

This directory contains Playwright API tests for testing REST endpoints directly without browser automation. These tests follow functional design patterns and are part of the testing pyramid (15-20% of total tests).

## Overview

API tests validate:
- Request/response handling
- Authentication and authorization
- Error handling and status codes
- API endpoint contracts

**MUST NOT**:
- Test business logic details (use unit tests)
- Test complete user workflows (use E2E tests)
- Mock the HTTP layer (use real HTTP requests)

## Running Tests

### Run all API tests
```bash
npm run test:api
# or
npx playwright test --project=api
```

### Run specific test file
```bash
npx playwright test tests/api/auth/login.api.spec.ts
```

### Run with UI mode
```bash
npx playwright test --project=api --ui
```

### Run in debug mode
```bash
npx playwright test --project=api --debug
```

## Test Structure

### File Naming Convention
- **MANDATORY**: All API test files MUST use `*.api.spec.ts` suffix
- Location: `tests/api/**/*.api.spec.ts`

### Test Organization
```
tests/api/
  ├── auth/
  │   ├── login.api.spec.ts
  │   ├── register.api.spec.ts
  │   ├── logout.api.spec.ts
  │   └── auth-flow.api.spec.ts
  └── fixtures/
      └── api-fixtures.ts
```

## Functional Design Patterns

All API tests follow functional programming principles:

### 1. Pure Functions
Data transformations without side effects:
```typescript
// Pure function: Creates headers without side effects
const createHeaders = (customHeaders?: Record<string, string>): Record<string, string> => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  ...customHeaders,
})
```

### 2. Currying
Reusable, configurable API operations:
```typescript
// Curried function: Returns configured API functions
const api = apiRequest(request)
const response = await api.post('/endpoint', data)
```

### 3. Function Composition
Build complex operations from smaller functions:
```typescript
// Composed function: Prepares test user with unique email
const testUser = prepareTestUser(baseUser)
```

### 4. Immutability
Never mutate input parameters; always return new objects:
```typescript
// ✅ Good: Returns new object
const uniqueUser = createUniqueUser(user)

// ❌ Bad: Mutates input
user.email = addTimestamp(user.email)
```

### 5. Separation of Concerns
- **Arrange**: Pure data preparation
- **Act**: Side-effect operations (API calls)
- **Assert**: Pure validation

## Writing API Tests

### Basic Test Structure

```typescript
import { expect, test } from '../fixtures/api-fixtures'
import { testusers } from '../../.data/users.data'

// Pure function for response extraction
const extractUserEmail = async (response: APIResponse): Promise<string> => {
  const body = await response.json()
  return body.data.email
}

test.describe('Feature API', () => {
  test('should perform action', async ({ authApi }) => {
    // Arrange - Pure data preparation
    const credentials = {
      email: testusers.admin.email,
      password: testusers.admin.password,
    }

    await test.step('Perform action', async () => {
      // Act - Side effect (API call via curried function)
      const response = await authApi.login(credentials)

      // Assert - Pure validation
      expect(response.status()).toBe(200)
      const email = await extractUserEmail(response)
      expect(email).toBe(testusers.admin.email)
    })
  })
})
```

### Using Fixtures

The API fixtures provide curried functions ready to use:

```typescript
test('should use authApi fixture', async ({ authApi }) => {
  // authApi provides: login, register, logout, getCurrentUser
  const response = await authApi.login(credentials)
})

test('should use general api fixture', async ({ api }) => {
  // api provides: get, post, put, delete
  const response = await api.get('/api/endpoint')
})
```

### Test Data

Use pure functions for test data preparation:

```typescript
import { prepareTestUser, generateTestUser } from '../../helpers/test-data'

// Generate unique test user
const baseUser = generateTestUser()
const testUser = prepareTestUser({
  name: baseUser.name,
  email: baseUser.email,
  password: baseUser.password,
  password_confirmation: baseUser.password_confirmation,
})
```

### Error Testing

Always test error scenarios:

```typescript
test('should return 422 for invalid data', async ({ authApi }) => {
  // Arrange
  const invalidData = {
    email: 'invalid-email',
    password: '123',
  }

  // Act
  const response = await authApi.register(invalidData)

  // Assert
  expect(response.status()).toBe(422)
  const body = await response.json()
  expect(body).toHaveProperty('errors')
  expect(body.errors).toHaveProperty('email')
})
```

## Available Helpers

### API Helpers (`tests/helpers/api-helpers.ts`)

#### Pure Functions
- `createHeaders(customHeaders?)`: Creates headers object
- `buildUrl(endpoint)`: Builds full URL from endpoint
- `extractCookieValue(cookieHeader)`: Extracts cookie values
- `buildAuthenticatedHeaders(csrfData, customHeaders?)`: Builds authenticated headers

#### Curried Functions
- `apiRequest(request)`: Returns configured API functions (get, post, put, delete)
- `createAuthApi(request)`: Returns authentication functions (login, register, logout, getCurrentUser)

#### Side Effect Functions
- `getCsrfCookie(request)`: Gets CSRF cookie from server
- `getCsrfTokenAndCookies(request)`: Gets CSRF token and cookies

### Test Data Helpers (`tests/helpers/test-data.ts`)

#### Pure Functions
- `addTimestamp(email)`: Adds timestamp to email
- `createUniqueEmail(base)`: Creates unique email from base
- `createUniqueUser(user)`: Creates unique user data (immutable)
- `prepareTestUser(baseUser)`: Composed function for test user preparation

#### Existing Functions
- `generateTestUser()`: Generates random test user

## Test Data

### Test Users (`tests/.data/users.data.ts`)

Predefined test users available:
- `testusers.admin`: Admin user
- `testusers.customer`: Customer user
- `testusers.contractor`: Contractor user
- `testusers.super_admin`: Super admin user
- `testusers.test`: Test user

## Assertions

### Status Codes
```typescript
expect(response.status()).toBe(200)  // OK
expect(response.status()).toBe(201)  // Created
expect(response.status()).toBe(204)  // No Content
expect(response.status()).toBe(400)  // Bad Request
expect(response.status()).toBe(401)  // Unauthorized
expect(response.status()).toBe(404)  // Not Found
expect(response.status()).toBe(422)  // Unprocessable Entity
expect(response.status()).toBe(500)  // Internal Server Error
```

### Response Body
```typescript
const body = await response.json()
expect(body).toHaveProperty('id')
expect(body.email).toBe('user@example.com')
expect(body).toHaveProperty('data')
expect(body.data).toHaveProperty('email')
```

### Headers
```typescript
expect(response.headers()['content-type']).toContain('application/json')
```

## Test Independence

**MANDATORY**: All tests MUST be:
- **Independent**: No dependencies between tests
- **Runnable in any order**: Tests can run in parallel
- **Idempotent**: Can run multiple times safely
- **Self-contained**: Clean up after themselves

### Unique Test Data
Always use unique test data to avoid conflicts:
```typescript
// ✅ Good: Uses timestamp for uniqueness
const testUser = prepareTestUser(baseUser)

// ❌ Bad: May conflict with other tests
const testUser = { email: 'test@example.com', ... }
```

## Configuration

### Environment Variables
- `PLAYWRIGHT_TEST_API_URL`: API base URL (default: `http://127.0.0.1:8000`)

### Playwright Config
API tests run in the `api` project:
```typescript
{
  name: 'api',
  use: { ...devices['Desktop Chrome'] },
  testMatch: /tests\/.*\.api\.spec\.ts$/,
}
```

## Examples

### Complete Example: Login Test
```typescript
import { expect, test } from '../fixtures/api-fixtures'
import { testusers } from '../../.data/users.data'

const extractUserEmail = async (response) => {
  const body = await response.json()
  return body.data.email
}

test.describe('Login API', () => {
  test('should authenticate admin user successfully', async ({ authApi }) => {
    // Arrange - Pure data preparation
    const credentials = {
      email: testusers.admin.email,
      password: testusers.admin.password,
    }

    await test.step('Login with admin credentials', async () => {
      // Act - Side effect
      const response = await authApi.login(credentials)

      // Assert - Pure validation
      expect(response.status()).toBe(200)
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
})
```

## Best Practices

1. **Use Pure Functions**: Extract response data with pure functions
2. **Separate Concerns**: Keep pure transformations separate from side effects
3. **Use test.step()**: Organize complex tests with steps
4. **Test Error Cases**: Always test error scenarios
5. **Use Unique Data**: Avoid test data conflicts
6. **Clean Up**: Clean up test data after each test
7. **Follow AAA Pattern**: Arrange (pure), Act (side effect), Assert (pure)

## References

- [Playwright API Testing Documentation](https://playwright.dev/docs/api-testing)
- [Functional Design Patterns](https://dev.to/patferraggi/do-you-need-design-patterns-in-functional-programming-370c)
- API testing rules: `client/.cursor/rules/api-testing.mdc`
- Testing pyramid rules: `client/.cursor/rules/testing-pyramid.mdc`


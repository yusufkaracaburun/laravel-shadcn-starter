# API Test Suite

This directory contains API tests for the Laravel backend, organized with code in `features/` and tests in `scenarios/` following SOLID principles and design patterns.

## Architecture

### Feature-Based Code Organization

All code (clients, utilities, fixtures) is organized by feature in the `features/` directory:

```
tests/api/
├── features/              # All code organized by feature
│   ├── auth/             # Authentication feature code
│   │   ├── auth-client.ts
│   │   └── fixtures.ts
│   ├── health/           # Health check feature code
│   │   └── health-client.ts
│   └── shared/           # Shared utilities organized by category
│       ├── core/         # Core base classes and clients
│       │   ├── base-client.ts
│       │   └── api-client.ts
│       ├── handlers/     # Request/response handlers
│       │   ├── csrf-handler.ts
│       │   ├── error-handler.ts
│       │   └── request-builder.ts
│       ├── helpers/      # Test helper functions
│       │   └── test-helpers.ts
│       ├── types/        # Type definitions
│       │   └── types.ts
│       ├── fixtures/    # Playwright fixtures
│       │   ├── fixtures.ts
│       │   └── index.ts
│       ├── factories/   # Factory classes
│       │   └── client-factory.ts
│       └── examples/    # Example/template code
│           └── example-client.ts
├── scenarios/            # All test files organized by feature
│   ├── auth/
│   │   ├── login.spec.ts
│   │   ├── logout.spec.ts
│   │   └── user.spec.ts
│   └── health/
│       └── health.spec.ts
└── fixtures/             # Playwright fixtures and test data
    ├── api.ts           # Playwright test fixtures
    ├── index.ts         # Main fixtures entry point
    └── data/            # Test data fixtures
        ├── user.ts
        └── index.ts
```

### Design Patterns Applied

#### 1. **Template Method Pattern**
`BaseClient` provides a template for all API clients, handling common concerns (CSRF, headers) while allowing subclasses to implement specific endpoints.

```typescript
export class ProductsClient extends BaseClient {
  async getAll() {
    return this.get('/api/products'); // CSRF handled automatically
  }
}
```

#### 2. **Single Responsibility Principle (SRP)**
Each class has a single, well-defined responsibility:
- **Core**: `BaseClient` (common HTTP methods), `ApiClient` (facade)
- **Handlers**: `CsrfHandler` (CSRF token management), `RequestBuilder` (header building), `ErrorHandler` (error parsing)
- **Helpers**: Test assertion helpers
- **Types**: Type definitions and interfaces
- **Fixtures**: Playwright test fixtures
- **Factories**: `ClientFactory` (client creation)
- **Examples**: Template code for new features

#### 3. **Factory Pattern**
`ClientFactory` provides a centralized way to create API clients for non-test usage. For tests, use Playwright fixtures instead.

```typescript
// For non-test code
const auth = ClientFactory.createAuthClient(request);

// For tests (recommended)
test('my test', async ({ authClient }) => {
  // authClient is automatically provided
});
```

#### 4. **Playwright Fixtures Pattern**
Playwright fixtures provide automatic setup/teardown and composability. See [Playwright Fixtures Documentation](https://playwright.dev/docs/test-fixtures#introduction) for details.

#### 5. **Facade Pattern**
`ApiClient` acts as a facade, providing a unified interface to multiple specialized clients while maintaining backward compatibility.

#### 6. **Dependency Injection**
All clients accept `APIRequestContext` via constructor injection, making them testable and following the Dependency Inversion Principle.

#### 7. **Builder Pattern**
`RequestBuilder` constructs complex request headers step by step, making header configuration flexible and maintainable.

### Test Organization

#### Playwright Fixtures
Tests use Playwright's fixture system for setup and teardown. Following [Playwright Fixtures documentation](https://playwright.dev/docs/test-fixtures#introduction), fixtures provide:
- **Encapsulation**: Setup and teardown in the same place
- **Reusability**: Define once, use in all tests
- **On-demand**: Only setup what's needed
- **Composable**: Can depend on each other
- **Flexible**: Tests can use any combination

All fixtures are organized in `fixtures/` directory:
- `fixtures/api.ts`: Playwright test fixtures
- `fixtures/data/`: Test data (user credentials, etc.)
- `fixtures/index.ts`: Main entry point for convenient imports

Available fixtures:
- `authClient`: Unauthenticated AuthClient instance
- `healthClient`: HealthClient instance
- `apiClient`: Unified ApiClient facade
- `authenticatedAuthClient`: Pre-authenticated AuthClient (automatically logs in)

#### AAA Pattern (Arrange-Act-Assert)
All tests follow the AAA pattern for clarity:

```typescript
import { test, expect, testUser } from '../../fixtures';

test('should login with valid credentials', async ({ authClient }) => {
  // Arrange & Act
  const response = await authClient.login(testUser.email, testUser.password);

  // Assert
  expectSuccess(response);
  await expectValidUser(response);
});
```

#### Using Authenticated Fixtures
For tests requiring authentication, use the `authenticatedAuthClient` fixture:

```typescript
test('should get authenticated user', async ({ authenticatedAuthClient }) => {
  // authenticatedAuthClient is already logged in
  const userResponse = await authenticatedAuthClient.getUser();
  await expectValidUser(userResponse);
});
```

#### Feature-Based Organization
- **Code**: Organized in `features/{feature}/`
- **Tests**: Organized in `scenarios/{feature}/`
- Each feature is self-contained with its client and fixtures

#### Playwright Fixtures
Playwright fixtures in `fixtures/api.ts` provide reusable client instances. All fixtures are organized according to [Playwright best practices](https://playwright.dev/docs/test-fixtures#introduction):

```typescript
import { test } from '../../fixtures';

// Use authenticated client fixture
test('my test', async ({ authenticatedAuthClient }) => {
  // authenticatedAuthClient is already logged in
});

// Use regular client fixture
test('my test', async ({ authClient }) => {
  // authClient is not authenticated
});
```

Fixtures automatically handle setup and teardown, making tests cleaner and more maintainable. The `authenticatedAuthClient` fixture demonstrates fixture composition - it depends on `authClient` and automatically logs in before the test.

### Helper Functions

Custom assertion helpers in `features/shared/test-helpers.ts` provide:
- **DRY**: Eliminate repeated assertion code
- **Readability**: Self-documenting test assertions
- **Maintainability**: Centralized assertion logic

Available helpers:
- `expectSuccess(response)`: Asserts 2xx status
- `expectStatus(response, code)`: Asserts specific status code
- `expectError(response, code?)`: Asserts error status
- `expectValidUser(response, email?)`: Asserts valid user object
- `expectValidationErrors(response)`: Asserts 422 with errors
- `expectUnauthenticated(response)`: Asserts 401 status
- `expectCookiesSet(response)`: Asserts cookies are present

## Best Practices

### 1. **Separation of Concerns**
- Code in `features/` - clients, fixtures, utilities
- Tests in `scenarios/` - test files only
- Shared utilities in `features/shared/`
- Shared test data in `fixtures/`

### 2. **Reusability**
- Factory pattern for client creation
- Feature fixtures for common test scenarios
- Helper functions for common assertions

### 3. **Maintainability**
- Single responsibility per class
- Clear naming conventions
- Comprehensive documentation
- Type safety with TypeScript

### 4. **Testability**
- Dependency injection
- Isolated components
- Mockable interfaces
- Clear test structure

## Running Tests

```bash
# Run all API tests
pnpm test:api

# Run with UI
pnpm test:api:ui

# Run in debug mode
pnpm test:api:debug

# Run specific feature
pnpm test:api scenarios/auth

# Run specific test file
pnpm test:api scenarios/auth/login.spec.ts
```

## Adding New Features

See [SCALABILITY.md](./SCALABILITY.md) for detailed guide on adding new features.

### Quick Start

1. **Create feature code folder**: `features/{feature-name}/`
2. **Create client**: `features/{feature-name}/{feature}-client.ts` (extends BaseClient)
3. **Add to factory**: Update `features/shared/client-factory.ts`
4. **Create fixtures** (if needed): `features/{feature-name}/fixtures.ts`
5. **Create test folder**: `scenarios/{feature-name}/`
6. **Create tests**: `scenarios/{feature-name}/{feature}.spec.ts`

### Example: Adding a Products Feature

```typescript
// features/products/products-client.ts
export class ProductsClient extends BaseClient {
  async getAll() {
    return this.get('/api/products');
  }
  
  async create(data: ProductData) {
    await this.ensureCsrfCookie();
    return this.post('/api/products', data);
  }
}
```

```typescript
// fixtures/api.ts
export const test = base.extend<ApiFixtures>({
  // ... existing fixtures ...
  
  productsClient: async ({ request }, use) => {
    const productsClient = new ProductsClient(request);
    await use(productsClient);
  },
  
  authenticatedProductsClient: async ({ authenticatedAuthClient, request }, use) => {
    // Products client that requires authentication
    const productsClient = new ProductsClient(request);
    await use(productsClient);
  },
});
```

```typescript
// scenarios/products/products.spec.ts
import { test } from '../../features/shared/fixtures';

test('should get all products', async ({ authenticatedProductsClient }) => {
  // Arrange & Act
  const response = await authenticatedProductsClient.getAll();

  // Assert
  expectSuccess(response);
});
```

## File Structure Summary

- **`features/`** - All code organized by feature
  - `{feature}/` - Feature-specific clients and fixtures
  - `shared/` - Shared utilities and base classes
- **`scenarios/`** - All test files organized by feature
- **`fixtures/`** - Shared test data

This structure promotes:
- **Clear Separation**: Code and tests are clearly separated
- **Scalability**: Easy to add new features
- **Maintainability**: Clear organization by feature
- **Reusability**: Clients and utilities shared across features
- **Testability**: Isolated, focused test files

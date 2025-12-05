# Scalability Guide

This guide explains how to add new API endpoints and clients while maintaining code quality and consistency.

## Structure Overview

- **Code**: All code goes in `features/` organized by feature
- **Tests**: All tests go in `scenarios/` organized by feature
- **Shared**: Shared utilities in `features/shared/`

## Adding a New Feature

### Step 1: Create the Feature Code Folder

Create a new folder in `features/` following the naming convention: `{feature-name}/`

```typescript
// features/products/products-client.ts
import { APIResponse } from '@playwright/test';
import { BaseClient } from '../shared/core/base-client';
import type { Product } from '../shared/types';

export class ProductsClient extends BaseClient {
  /**
   * Get all products
   */
  async getAll(): Promise<APIResponse> {
    return this.get('/api/products');
  }

  /**
   * Get product by ID
   */
  async getById(id: number): Promise<APIResponse> {
    return this.get(`/api/products/${id}`);
  }

  /**
   * Create new product
   */
  async create(data: Partial<Product>): Promise<APIResponse> {
    await this.ensureCsrfCookie();
    return this.post('/api/products', data);
  }

  /**
   * Update product
   */
  async update(id: number, data: Partial<Product>): Promise<APIResponse> {
    await this.ensureCsrfCookie();
    return this.put(`/api/products/${id}`, data);
  }

  /**
   * Delete product
   */
  async delete(id: number): Promise<APIResponse> {
    await this.ensureCsrfCookie();
    return this.delete(`/api/products/${id}`);
  }
}
```

### Step 2: Add Types (if needed)

Add type definitions to `features/shared/types.ts`:

```typescript
export interface Product {
  id: number;
  name: string;
  description: string;
  price: number;
  created_at: string;
  updated_at: string;
}
```

### Step 3: Register in Factory

Add a factory method to `features/shared/client-factory.ts`:

```typescript
import { ProductsClient } from './products-client';

export class ClientFactory {
  // ... existing methods

  static createProductsClient(request: APIRequestContext): ProductsClient {
    return new ProductsClient(request);
  }
}
```

### Step 4: Add to Facade (optional)

If you want the client accessible via the unified `ApiClient`:

```typescript
// features/shared/api-client.ts
export class ApiClient {
  public readonly auth: AuthClient;
  public readonly health: HealthClient;
  public readonly products: ProductsClient; // Add this

  constructor(request: APIRequestContext) {
    this.auth = new AuthClient(request);
    this.health = new HealthClient(request);
    this.products = new ProductsClient(request); // Add this
  }
}
```

### Step 5: Add Fixture (if needed)

Add a fixture to `fixtures/api.ts`:

```typescript
// fixtures/api.ts
export const test = base.extend<ApiFixtures>({
  // ... existing fixtures ...
  
  productsClient: async ({ request }, use) => {
    const productsClient = new ProductsClient(request);
    await use(productsClient);
  },
  
  // If authentication is required:
  authenticatedProductsClient: async ({ authenticatedAuthClient, request }, use) => {
    const productsClient = new ProductsClient(request);
    await use(productsClient);
  },
});
```

### Step 6: Create Test Folder and Test File

Create a test folder in `scenarios/` and test file:

```typescript
// scenarios/products/products.spec.ts
import { test } from '../../fixtures';
import { expectSuccess, expectStatus } from '../../features/shared/helpers/test-helpers';

test.describe('Products', () => {
  test.describe('GET /api/products', () => {
test('should get all products', async ({ authenticatedProductsClient }) => {
  // Arrange & Act
  const response = await authenticatedProductsClient.getAll();

  // Assert
  expectSuccess(response);
});
  });

  test.describe('POST /api/products', () => {
    test('should create product', async ({ request }) => {
      // Arrange
      const auth = await AuthFixtures.createAuthenticatedClient(request);
      const products = ClientFactory.createProductsClient(request);

      // Act
      const response = await products.create({
        name: 'Test Product',
        description: 'Test Description',
        price: 99.99,
      });

      // Assert
      expectStatus(response, 201);
    });
  });
});
```

## BaseClient Methods

All clients extending `BaseClient` have access to these protected methods:

### HTTP Methods

- `get(endpoint, options?)` - GET request
- `post(endpoint, data, options?)` - POST request
- `put(endpoint, data, options?)` - PUT request
- `patch(endpoint, data, options?)` - PATCH request
- `delete(endpoint, options?)` - DELETE request

### Options

All methods accept an `options` parameter:

```typescript
{
  requireCsrf?: boolean; // Default: true for POST/PUT/PATCH/DELETE, false for GET
}
```

### Helper Methods

- `ensureCsrfCookie()` - Ensures CSRF cookie is obtained before making requests

## Best Practices

### 1. Always Extend BaseClient

```typescript
// ✅ Good
export class ProductsClient extends BaseClient {
  async getAll() {
    return this.get('/api/products');
  }
}

// ❌ Bad - Don't create clients from scratch
export class ProductsClient {
  constructor(private request: APIRequestContext) {}
  // Manual CSRF handling, header building, etc.
}
```

### 2. Use Typed Responses

```typescript
// ✅ Good - Type safety
async getProductTyped(id: number): Promise<Product> {
  const response = await this.getById(id);
  return response.json() as Promise<Product>;
}

// ❌ Bad - No type safety
async getProduct(id: number) {
  const response = await this.getById(id);
  return response.json();
}
```

### 3. Follow AAA Pattern in Tests

```typescript
test('should create product', async ({ request }) => {
  // Arrange
  const auth = await AuthFixtures.createAuthenticatedClient(request);
  const products = ClientFactory.createProductsClient(request);

  // Act
  const response = await products.create({ name: 'Test' });

  // Assert
  expectStatus(response, 201);
});
```

### 4. Use Helper Functions

```typescript
// ✅ Good - Use helpers
expectSuccess(response);
await expectValidUser(response);

// ❌ Bad - Manual assertions
expect(response.status()).toBeGreaterThanOrEqual(200);
const user = await response.json();
expect(user).toHaveProperty('id');
```

### 5. Organize Tests by Feature

```
tests/api/
├── auth.spec.ts
├── health.spec.ts
├── products.spec.ts      # New feature
├── orders.spec.ts        # Another feature
└── ...
```

## Feature Organization

### Creating a New Feature

1. **Create feature code folder**: `features/{feature-name}/`
2. **Create client**: `features/{feature-name}/{feature}-client.ts`
3. **Create fixtures** (if needed): `features/{feature-name}/fixtures.ts`
4. **Create test folder**: `scenarios/{feature-name}/`
5. **Create test file**: `scenarios/{feature-name}/{feature}.spec.ts`

Example structure:
```
features/
└── products/
    ├── products-client.ts
    └── fixtures.ts (optional)
scenarios/
└── products/
    └── products.spec.ts
```

## Common Patterns

### Paginated Endpoints

```typescript
async getAll(page = 1, perPage = 15): Promise<APIResponse> {
  return this.get(`/api/products?page=${page}&per_page=${perPage}`);
}
```

### Filtered Endpoints

```typescript
async search(query: string, filters?: Record<string, unknown>): Promise<APIResponse> {
  const params = new URLSearchParams({ q: query });
  if (filters) {
    Object.entries(filters).forEach(([key, value]) => {
      params.append(key, String(value));
    });
  }
  return this.get(`/api/products/search?${params.toString()}`);
}
```

### Nested Resources

```typescript
async getProductReviews(productId: number): Promise<APIResponse> {
  return this.get(`/api/products/${productId}/reviews`);
}
```

## Error Handling

Use `ErrorHandler` for consistent error handling:

```typescript
import { ErrorHandler } from './helpers/error-handler';

const response = await products.create(data);
if (!response.ok()) {
  const error = await ErrorHandler.parseError(response);
  console.error('Validation errors:', error.errors);
}
```

## File Organization

### Current Structure
```
tests/api/
├── features/          # All code organized by feature
│   ├── auth/         # Authentication feature code
│   │   ├── auth-client.ts
│   │   └── fixtures.ts
│   ├── health/       # Health check feature code
│   │   └── health-client.ts
│   └── shared/       # Shared utilities
│       ├── base-client.ts
│       ├── client-factory.ts
│       ├── test-helpers.ts
│       └── ...
├── scenarios/        # All test files organized by feature
│   ├── auth/
│   │   ├── login.spec.ts
│   │   ├── logout.spec.ts
│   │   └── user.spec.ts
│   └── health/
│       └── health.spec.ts
└── fixtures/         # Shared test data
    └── user.ts
```

### Naming Conventions

- **Feature folders**: `kebab-case` (e.g., `user-management/`)
- **Test files**: `kebab-case.spec.ts` (e.g., `login.spec.ts`)
- **Client files**: `kebab-case-client.ts` (e.g., `auth-client.ts`)
- **Fixture files**: `fixtures.ts` (within feature folder)

## Testing Checklist

When adding a new client, ensure you test:

- [ ] GET endpoints (list, show)
- [ ] POST endpoints (create)
- [ ] PUT/PATCH endpoints (update)
- [ ] DELETE endpoints (delete)
- [ ] Authentication requirements
- [ ] Validation errors
- [ ] Not found errors (404)
- [ ] Authorization errors (403)
- [ ] Edge cases (empty results, pagination, etc.)

## Example: Complete Feature Implementation

See `features/shared/examples/example-client.ts` for a complete client template you can copy and modify.

### Complete Example Structure

```
features/products/
└── products-client.ts

scenarios/products/
└── products.spec.ts

fixtures/
└── api.ts (add productsClient fixture)

features/shared/
└── factories/client-factory.ts (optional, for non-test usage)
```

### Benefits of Using Fixtures

1. **Automatic Setup/Teardown**: Fixtures handle setup and teardown automatically
2. **Composability**: Fixtures can depend on each other (e.g., `authenticatedProductsClient` depends on `authenticatedAuthClient`)
3. **On-Demand**: Only fixtures used by a test are set up
4. **Reusability**: Define once, use everywhere
5. **Type Safety**: TypeScript knows exactly what fixtures are available

### When to Use Fixtures vs Factory

- **Use Fixtures** for tests (recommended): Cleaner, more maintainable, follows Playwright best practices
- **Use Factory** for non-test code: When you need clients outside of test context


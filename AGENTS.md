# Development Agent - Backend-Frontend Integration

This agent ensures perfect communication between the Laravel backend API and Vue.js frontend. It acts as a proxy between backend and frontend development, enforcing working code with mandatory testing and verification.

## Core Principles

1. **Backend First**: Always implement backend API endpoint before frontend
2. **Test Everything**: No feature is complete without passing tests on both sides
3. **Type Safety**: TypeScript interfaces must match backend resources exactly
4. **API Contract**: Response format must be consistent (`IResponse<T>`)
5. **Verification**: Always verify end-to-end before marking complete

## Development Workflow

### Step 1: Backend Implementation

**MUST** follow this order:

1. **Database & Model** (if needed):
   ```bash
   cd api
   php artisan make:migration create_{resource}_table
   php artisan make:model {Resource}
   php artisan migrate
   ```

2. **API Resource**:
   ```bash
   php artisan make:resource {Resource}Resource
   ```
   - Transform model to API format
   - Include relationships when loaded
   - Use consistent field naming

3. **Controller**:
   ```bash
   php artisan make:controller Api/{Resource}Controller
   ```
   - Use `ApiResponse::success()` for all responses
   - Use API Resources for data transformation
   - Follow existing patterns

4. **Routes**:
   - Add to `routes/api/{resource}.php`
   - Use `auth:sanctum` middleware
   - Name routes: `api.{resource}.{action}`

5. **Feature Test**:
   ```bash
   php artisan make:test Api/{Resource}ControllerTest
   ```
   - Test all CRUD operations
   - Test authentication requirements
   - Test validation errors

6. **VERIFY Backend**:
   ```bash
   cd api
   php artisan test --filter={Resource}
   # Must pass 100%
   ```

### Step 2: Frontend Implementation

**MUST** follow this order:

1. **TypeScript Interface**:
   - Create in `client/src/services/api/{resource}.api.ts`
   - Match backend resource structure exactly
   - Use same field names and types

2. **API Service**:
   - Create `client/src/services/api/{resource}.api.ts`
   - Use `useAxios()` composable
   - Use TanStack Query hooks
   - Wrap responses in `IResponse<T>`

3. **Page/Component**:
   - Create in `client/src/pages/{resource}/`
   - Use API service hooks
   - Handle loading/error states
   - Use TypeScript types

4. **API Test**:
   - Create in `client/tests/api/{resource}.api.spec.ts`
   - Use Playwright `APIRequestContext`
   - Follow functional design patterns
   - Test authentication flow

5. **VERIFY Frontend**:
   ```bash
   cd client
   npm run test:api
   # Must pass 100%
   ```

### Step 3: Integration Verification

**MUST** verify before marking complete:

1. **Manual Testing**:
   - [ ] Backend endpoint accessible
   - [ ] Response format matches `IResponse<T>`
   - [ ] Frontend can fetch data
   - [ ] Frontend can create/update/delete
   - [ ] Authentication works
   - [ ] Error handling works

2. **Automated Testing**:
   ```bash
   # Backend
   cd api && php artisan test
   
   # Frontend
   cd client && npm run test:api
   
   # Both must pass 100%
   ```

3. **Type Safety**:
   - [ ] No TypeScript errors
   - [ ] Types match backend exactly
   - [ ] No `any` types in API code

## API Response Format (MANDATORY)

### Backend

**ALWAYS** use `ApiResponse`:

```php
use App\Http\Responses\ApiResponse;

// Success
return ApiResponse::success($data, $extra, $message, $code);

// Error
return ApiResponse::error($message, $code, $data, $extra);

// Created
return ApiResponse::created($data, $extra, $message);
```

**Response Structure**:
```json
{
  "success": true,
  "code": 200,
  "message": "Success",
  "data": { /* actual data */ },
  "extra": { /* metadata */ }
}
```

### Frontend

**ALWAYS** use `IResponse<T>`:

```typescript
import type { IResponse } from '@/services/types/response.type'

const response: IResponse<User> = await axiosInstance.get('/api/user/current')
// Access: response.data, response.success, response.code, etc.
```

## Authentication Flow (MANDATORY)

### Frontend Authentication

**ALWAYS** follow this flow:

```typescript
// 1. Get CSRF cookie (REQUIRED FIRST)
await axiosInstance.get('/sanctum/csrf-cookie')

// 2. Login
await axiosInstance.post('/login', {
  email: 'user@example.com',
  password: 'password123'
})

// 3. Subsequent requests automatically include cookies
const user = await axiosInstance.get('/api/user/current')
```

**MUST**:
- Call `/sanctum/csrf-cookie` before login/register
- Use `withCredentials: true` in axios config
- Include `Accept: application/json` header
- Include `X-Requested-With: XMLHttpRequest` header

## Type Safety Requirements

### Backend Resource → Frontend Interface

**MUST** match exactly:

**Backend** (`app/Http/Resources/UserResource.php`):
```php
return [
    'id' => $this->id,
    'name' => $this->name,
    'email' => $this->email,
    'created_at' => $this->created_at?->toIso8601String(),
];
```

**Frontend** (`client/src/services/api/users.api.ts`):
```typescript
export interface User {
  id: number
  name: string
  email: string
  created_at: string
}
```

**MUST**:
- Match field names exactly
- Match field types (number, string, boolean, etc.)
- Handle nullable fields with `| null`
- Handle optional fields with `?`

## Testing Requirements

### Backend Tests

**MUST** create feature tests for all endpoints:

```php
test('user can get current user', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->getJson('/api/user/current');
    
    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'code',
            'message',
            'data' => ['id', 'name', 'email'],
            'extra',
        ]);
});
```

**VERIFY**: `cd api && php artisan test --filter={feature}`

### Frontend API Tests

**MUST** create API tests using Playwright:

```typescript
test('should get current user', async ({ request }) => {
  // Get CSRF cookie
  await request.get('http://localhost:8000/sanctum/csrf-cookie')
  
  // Login
  await request.post('http://localhost:8000/login', {
    data: { email: 'test@example.com', password: 'password123' },
  })
  
  // Get user
  const response = await request.get('http://localhost:8000/api/user/current')
  
  expect(response.status()).toBe(200)
  const body = await response.json()
  expect(body).toHaveProperty('success', true)
  expect(body).toHaveProperty('data')
})
```

**VERIFY**: `cd client && npm run test:api`

## Naming Conventions

### Backend
- **Controller**: `{Resource}Controller` (e.g., `PostController`)
- **Resource**: `{Resource}Resource` (e.g., `PostResource`)
- **Route file**: `routes/api/{resource}.php` (e.g., `posts.php`)
- **Route name**: `api.{resource}.{action}` (e.g., `api.post.index`)

### Frontend
- **API service**: `client/src/services/api/{resource}.api.ts`
- **Interface**: `{Resource}` (e.g., `Post`)
- **Query hook**: `useGet{Resource}Query()` (e.g., `useGetPostsQuery()`)
- **Mutation hook**: `useCreate{Resource}Mutation()` (e.g., `useCreatePostMutation()`)
- **Page**: `client/src/pages/{resource}/index.vue`

## Common Patterns

### Creating a New Resource Endpoint

**Backend**:
```php
// routes/api/posts.php
Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('post', PostController::class);
});

// app/Http/Controllers/Api/PostController.php
public function index(): JsonResponse
{
    $posts = Post::paginate(15);
    return ApiResponse::success(
        PostResource::collection($posts->items()),
        ['pagination' => [
            'current_page' => $posts->currentPage(),
            'total' => $posts->total(),
        ]]
    );
}
```

**Frontend**:
```typescript
// client/src/services/api/posts.api.ts
export interface Post {
  id: number
  title: string
  content: string
  created_at: string
}

export function useGetPostsQuery() {
  const { axiosInstance } = useAxios()
  return useQuery({
    queryKey: ['posts'],
    queryFn: async (): Promise<IResponse<Post[]>> => {
      const response = await axiosInstance.get('/post')
      return response.data
    },
  })
}
```

## Error Handling

### Backend

**ALWAYS** use `ApiResponse::error()`:

```php
return ApiResponse::error('Validation failed', 422, $errors, []);
```

### Frontend

**ALWAYS** handle errors:

```typescript
try {
  const response = await axiosInstance.post('/api/users', data)
} catch (error: AxiosError) {
  if (error.response?.status === 422) {
    // Validation errors
    const errors = error.response.data.errors
  } else if (error.response?.status === 401) {
    // Unauthorized - redirect to login
    router.push('/login')
  }
}
```

## Verification Commands

### Backend Verification

```bash
cd api

# Run all tests
php artisan test

# Run specific feature tests
php artisan test --filter={Resource}

# Check code style
vendor/bin/pint --dirty

# Static analysis
vendor/bin/phpstan analyse
```

### Frontend Verification

```bash
cd client

# Run API tests
npm run test:api

# Type check
npm run type-check

# Lint
npm run lint
```

### Full Stack Verification

```bash
# Backend tests
cd api && php artisan test

# Frontend API tests
cd client && npm run test:api

# Both must pass 100%
```

## Completion Checklist

A feature is **NOT complete** until:

- [ ] Backend endpoint implemented with `ApiResponse`
- [ ] Backend feature test passes
- [ ] Frontend TypeScript interface matches backend resource
- [ ] Frontend API service implemented
- [ ] Frontend page/component implemented
- [ ] Frontend API test passes
- [ ] Manual end-to-end testing successful
- [ ] All backend tests pass: `cd api && php artisan test`
- [ ] All frontend API tests pass: `cd client && npm run test:api`
- [ ] No TypeScript errors
- [ ] No linter errors
- [ ] Response format verified
- [ ] Authentication flow verified
- [ ] Error handling verified

## Quick Reference

### Backend API Response
- File: `api/app/Http/Responses/ApiResponse.php`
- Usage: `ApiResponse::success($data, $extra, $message, $code)`

### Frontend Response Type
- File: `client/src/services/types/response.type.ts`
- Usage: `IResponse<T>`

### Backend Testing
- Location: `api/tests/Feature/Api/`
- Framework: Pest PHP
- Run: `php artisan test`

### Frontend API Testing
- Location: `client/tests/api/`
- Framework: Playwright
- Run: `npm run test:api`

### Authentication
- Backend: Sanctum SPA mode (cookie-based)
- Frontend: CSRF cookie → Login → Session cookies
- Docs: `api/.docs/sanctum.md`

## References

- Backend-Frontend Integration Rules: `.cursor/rules/backend-frontend-integration.mdc`
- Full-Stack Feature Rules: `.cursor/rules/fullstack-feature.mdc`
- Backend Testing: `api/.cursor/rules/testing-pyramid.mdc`
- Frontend API Testing: `client/.cursor/rules/api-testing.mdc`
- Authentication Setup: `api/.docs/sanctum.md`


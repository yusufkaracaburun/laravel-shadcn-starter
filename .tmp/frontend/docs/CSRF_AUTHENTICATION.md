# CSRF Authentication Issue & Solution

## The Problem: 401 Unauthorized on `/api/user`

### Why It Was Failing

When we first created the API test for "get user", it was failing with a **401 Unauthorized** error. Here's why:

1. **Laravel Sanctum SPA Authentication** requires CSRF protection for stateful requests
2. The `/api/user` endpoint is protected and requires:
   - A valid session (from login)
   - A valid CSRF token in the `X-XSRF-TOKEN` header
3. **The CSRF token** comes from the `XSRF-TOKEN` cookie, but:
   - The cookie value is **URL-encoded** by Laravel
   - It must be **URL-decoded** before sending in the header
   - This was the critical missing piece!

### The Flow

```
1. GET /sanctum/csrf-cookie
   ↓
   Response sets cookie: XSRF-TOKEN=encoded%2Bvalue%3Dhere
   
2. Extract cookie value: "encoded%2Bvalue%3Dhere"
   
3. URL-decode it: "encoded+value=here"
   
4. Send in header: X-XSRF-TOKEN: encoded+value=here
   
5. POST /api/login (with CSRF token in header)
   ↓
   Creates session, user is authenticated
   
6. GET /api/user (with CSRF token in header)
   ↓
   ✅ Success! Returns user data
```

### What Was Missing

The test was:
- ✅ Getting the CSRF cookie from `/sanctum/csrf-cookie`
- ✅ Sending cookies with requests (Playwright handles this automatically)
- ❌ **NOT extracting the CSRF token from the cookie**
- ❌ **NOT URL-decoding the token value**
- ❌ **NOT sending it in the `X-XSRF-TOKEN` header**

## The Solution

### In Tests (Playwright)

We implemented a `CsrfHandler` that:

1. **Extracts** the CSRF token from `Set-Cookie` headers
2. **URL-decodes** the cookie value (critical step!)
3. **Stores** it for use in subsequent requests
4. **Automatically includes** it in the `X-XSRF-TOKEN` header

**Key Code:**
```typescript
// Extract from Set-Cookie header
const match = cookie.match(/^XSRF-TOKEN=([^;]+)/);
if (match) {
  // URL-decode the cookie value (Laravel encodes it)
  this.csrfToken = decodeURIComponent(match[1]);
}
```

**Usage in BaseClient:**
```typescript
// Automatically extract CSRF token from all responses
this.csrfHandler.extractFromResponse(response);

// Automatically include in headers
headers['X-XSRF-TOKEN'] = this.csrfHandler.getToken();
```

### In Frontend (Vue/Axios)

The frontend needs the same implementation. Here's how:

## Frontend Implementation

### Current State

The frontend `api-client.ts` currently:
- ✅ Has `withCredentials: true` (sends cookies)
- ✅ Has proper headers (`Accept`, `X-Requested-With`)
- ❌ **Missing CSRF token extraction and header**

### Required Changes

#### 1. Create CSRF Token Helper

```typescript
// src/lib/csrf-token.ts
/**
 * Get CSRF token from XSRF-TOKEN cookie
 * Laravel stores the token in a cookie, but we need to send it in the header
 * The cookie value is URL-encoded, so we need to decode it
 */
export function getCsrfToken(): string | null {
  // Get cookie value
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (!match) {
    return null;
  }

  try {
    // URL-decode the cookie value (Laravel encodes it)
    return decodeURIComponent(match[1]);
  } catch {
    // If decoding fails, return raw value
    return match[1];
  }
}
```

#### 2. Update API Client

```typescript
// src/lib/api-client.ts
import { getCsrfToken } from './csrf-token'

// Request interceptor - add CSRF token to all requests
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // Get CSRF token from cookie
    const csrfToken = getCsrfToken()
    
    // Add CSRF token to headers for state-changing requests
    // Laravel requires this for POST, PUT, PATCH, DELETE
    // Also required for GET requests to protected endpoints
    if (csrfToken && config.method) {
      const stateChangingMethods = ['post', 'put', 'patch', 'delete']
      const isStateChanging = stateChangingMethods.includes(config.method.toLowerCase())
      
      // Always include CSRF token for state-changing requests
      // Also include for GET requests (needed for protected endpoints)
      if (isStateChanging || config.method.toLowerCase() === 'get') {
        config.headers['X-XSRF-TOKEN'] = csrfToken
      }
    }
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)
```

#### 3. Ensure CSRF Cookie is Obtained

The `auth.service.ts` already has `getCsrfCookie()` function, which is good. Make sure it's called before login:

```typescript
// src/services/auth.service.ts
export async function login(email: string, password: string): Promise<User> {
  // Step 1: Get CSRF cookie (this sets the XSRF-TOKEN cookie)
  await getCsrfCookie()
  
  // Step 2: Login (CSRF token will be automatically added by interceptor)
  const response = await apiClient.post('/api/login', { email, password })
  return response.data
}
```

### Complete Implementation

Here's the complete updated `api-client.ts`:

```typescript
import axios, { type AxiosInstance, type InternalAxiosRequestConfig, type AxiosResponse } from 'axios'
import { VITE_API_BASE_URL } from '@/utils/env'

/**
 * Get CSRF token from XSRF-TOKEN cookie
 * Laravel stores the token in a cookie, but we need to send it in the header
 * The cookie value is URL-encoded, so we need to decode it
 */
function getCsrfToken(): string | null {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (!match) {
    return null
  }

  try {
    // URL-decode the cookie value (Laravel encodes it)
    return decodeURIComponent(match[1])
  } catch {
    return match[1]
  }
}

// Create axios instance with Sanctum SPA configuration
const apiClient: AxiosInstance = axios.create({
  baseURL: VITE_API_BASE_URL,
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

// Request interceptor - automatically add CSRF token
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // Get CSRF token from cookie
    const csrfToken = getCsrfToken()
    
    // Add CSRF token to headers
    // Required for all state-changing requests (POST, PUT, PATCH, DELETE)
    // Also required for GET requests to protected endpoints
    if (csrfToken && config.headers) {
      config.headers['X-XSRF-TOKEN'] = csrfToken
    }
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
apiClient.interceptors.response.use(
  (response: AxiosResponse) => {
    return response
  },
  (error) => {
    // Handle common errors (401, 403, 500, etc.)
    if (error.response) {
      switch (error.response.status) {
        case 401:
          // Unauthorized - user needs to login
          console.error('Unauthorized: Please login')
          break
        case 403:
          // Forbidden - user doesn't have permission
          console.error('Forbidden: You do not have permission')
          break
        case 422:
          // Validation errors
          console.error('Validation errors:', error.response.data.errors)
          break
        case 500:
          // Server error
          console.error('Server error: Please try again later')
          break
      }
    }
    return Promise.reject(error)
  }
)

export default apiClient
```

## Key Points

1. **Cookie vs Header**: Laravel stores the CSRF token in a cookie (`XSRF-TOKEN`), but expects it in a header (`X-XSRF-TOKEN`)

2. **URL Encoding**: The cookie value is URL-encoded by Laravel, so it **must be decoded** before sending in the header

3. **Automatic Extraction**: Use an Axios interceptor to automatically extract and add the token to all requests

4. **Always Include**: Include the CSRF token for:
   - All state-changing requests (POST, PUT, PATCH, DELETE)
   - GET requests to protected endpoints (like `/api/user`)

5. **Cookie Handling**: `withCredentials: true` ensures cookies are sent with requests, but you still need to extract the token and put it in the header

## Testing

After implementing this in the frontend:

1. Call `getCsrfCookie()` before login
2. Login should work
3. `/api/user` should return user data (not 401)
4. All authenticated requests should work

## References

- [Laravel Sanctum SPA Authentication](https://laravel.com/docs/master/sanctum#spa-authentication)
- [Laravel CSRF Tokens and SPAs](https://laravel.com/docs/master/csrf#csrf-tokens-and-spas)
- [Playwright Fixtures Documentation](https://playwright.dev/docs/test-fixtures#introduction)


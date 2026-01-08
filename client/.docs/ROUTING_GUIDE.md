# Vue Router Guide

This guide explains how routing is handled in the client application using file-based routing with `unplugin-vue-router`.

## Overview

The application uses **file-based routing** where routes are automatically generated from the `src/pages` directory structure. This provides:

- ✅ Automatic route generation from file structure
- ✅ Type-safe route names, params, and queries
- ✅ Layout system with automatic wrapping
- ✅ Route guards for authentication
- ✅ Hot module replacement support

## Architecture

### Core Components

1. **Router Setup** (`src/router/index.ts`)
   - Creates Vue Router instance with HTML5 history mode
   - Applies layouts to routes
   - Configures scroll behavior

2. **Router Guards** (`src/router/guard/`)
   - **Common Guard**: Progress bar on navigation
   - **Auth Guard**: Authentication checks and redirects

3. **Auto-Generated Routes** (`vue-router/auto-routes`)
   - Generated from `src/pages` directory structure
   - Type definitions in `src/types/typed-router.d.ts`

## File-Based Routing

### Basic Routes

Routes are created automatically from the file structure:

```
src/pages/
├── index.vue              → /
├── dashboard/
│   └── index.vue          → /dashboard
├── auth/
│   ├── sign-in.vue        → /auth/sign-in
│   └── sign-up.vue        → /auth/sign-up
└── users/
    └── index.vue          → /users
```

### Dynamic Routes (Path Parameters)

Use brackets in filenames to create dynamic route parameters:

```
src/pages/
├── users/
│   └── [id].vue           → /users/:id
├── teams/
│   └── [team].vue         → /teams/:team
└── posts/
    └── [slug].vue         → /posts/:slug
```

**Example: Accessing Path Parameters**

```vue
<script setup lang="ts">
import { useRoute } from 'vue-router'

const route = useRoute()

// Access the dynamic parameter
const userId = computed(() => Number(route.params.id))
const teamId = computed(() => Number(route.params.team))
const slug = computed(() => route.params.slug as string)
</script>

<template>
  <div>
    <p>User ID: {{ userId }}</p>
    <p>Team ID: {{ teamId }}</p>
    <p>Slug: {{ slug }}</p>
  </div>
</template>
```

### Catch-All Routes

Use `[...path].vue` for catch-all routes (404 pages):

```
src/pages/
└── [...path].vue          → /:path(.*) (matches everything)
```

**Example: 404 Page**

```vue
<script setup lang="ts">
import Error from '@/components/custom-error.vue'
</script>

<template>
  <div class="flex items-center justify-center h-screen">
    <Error
      :code="404"
      subtitle="Page Not Found"
      error="The page you are looking for might have been removed."
    />
  </div>
</template>

<route lang="yaml">
meta:
  layout: false
</route>
```

## Query Parameters

Query parameters are accessed via `route.query`:

**Example: Reading Query Parameters**

```vue
<script setup lang="ts">
const route = useRoute()

// Access query parameters
const page = computed(() => Number(route.query.page) || 1)
const search = computed(() => (route.query.search as string) || '')
const redirect = computed(() => route.query.redirect as string)
</script>

<template>
  <div>
    <p>Current page: {{ page }}</p>
    <p>Search: {{ search }}</p>
  </div>
</template>
```

**Example: Setting Query Parameters**

```typescript
import { useRouter } from 'vue-router'

const router = useRouter()

// Navigate with query parameters
router.push({
  name: '/users',
  query: {
    page: 2,
    search: 'john',
    sort: 'name',
  },
})

// Or using path
router.push('/users?page=2&search=john')
```

**Real Example: Redirect After Login**

```typescript
// From auth-guard.ts
router.beforeEach((to, _from) => {
  if (to.meta.auth && !isLogin) {
    return {
      name: '/auth/sign-in',
      query: { redirect: to.fullPath }, // Preserve destination
    }
  }
})

// From use-auth.ts - using the redirect
async function login() {
  // ... login logic ...

  const redirect = router.currentRoute.value.query.redirect as string
  if (redirect && !redirect.startsWith('//')) {
    router.push(redirect) // Go to original destination
  }
  else {
    router.push('/dashboard') // Default
  }
}
```

## Route Meta Information

Route meta is defined using a `<route>` block at the top of page components:

### Defining Route Meta

```vue
<route lang="yaml">
meta:
  auth: true          # Requires authentication
  layout: blank       # Use 'blank' layout
  layout: false       # No layout wrapper
  title: "Page Title" # Page title (if using title plugin)
</route>

<script setup lang="ts">
// Component code
</script>
```

### Available Meta Options

- **`auth: true`** - Requires user to be authenticated
- **`layout: 'default'`** - Use default layout (default behavior)
- **`layout: 'blank'`** - Use blank layout
- **`layout: 'marketing'`** - Use marketing layout
- **`layout: 'web'`** - Use web layout
- **`layout: false`** - No layout wrapper

### Accessing Meta in Components

```vue
<script setup lang="ts">
const route = useRoute()

// Access meta information
const requiresAuth = route.meta.auth
const layout = route.meta.layout
const title = route.meta.title
</script>
```

### Using Meta in Guards

```typescript
// From auth-guard.ts
router.beforeEach((to, _from) => {
  const authStore = useAuthStore(pinia)
  const { isLogin } = storeToRefs(authStore)

  // Check if route requires authentication
  if (to.meta.auth && !unref(isLogin) && to.name !== '/auth/sign-in') {
    return {
      name: '/auth/sign-in',
      query: { redirect: to.fullPath },
    }
  }
})
```

## Router Guards

### Common Guard

Handles global navigation behavior (progress bar):

```typescript
// src/router/guard/index.ts
function setupCommonGuard(router: Router) {
  router.beforeEach(() => {
    nprogress.start() // Show progress bar
    return true
  })

  router.afterEach(() => {
    nprogress.done() // Hide progress bar
    return true
  })
}
```

### Auth Guard

Handles authentication checks:

```typescript
// src/router/guard/auth-guard.ts
export function authGuard(router: Router) {
  router.beforeEach((to, _from) => {
    const authStore = useAuthStore(pinia)
    const { isLogin } = storeToRefs(authStore)

    // Redirect to login if auth required but not logged in
    if (to.meta.auth && !unref(isLogin) && to.name !== '/auth/sign-in') {
      return {
        name: '/auth/sign-in',
        query: { redirect: to.fullPath },
      }
    }
  })
}
```

## Layouts

Routes are automatically wrapped with layouts using `vite-plugin-vue-layouts`.

### Available Layouts

- **`default.vue`** - Default layout (used by default)
- **`blank.vue`** - Minimal layout
- **`marketing.vue`** - Marketing page layout
- **`web.vue`** - Web application layout

### Specifying Layouts

**Option 1: Using Route Meta**

```vue
<route lang="yaml">
meta:
  layout: blank
</route>
```

**Option 2: Using Layout File Naming**

```
src/pages/
└── auth/
    └── sign-in.vue       # Uses default layout
    └── sign-in.blank.vue # Uses blank layout
```

### Layout Structure

```vue
<!-- src/layouts/default.vue -->
<template>
  <div class="layout-default">
    <AppSidebar />
    <main>
      <RouterView />
      <!-- Page content renders here -->
    </main>
  </div>
</template>
```

## Type Safety

The `unplugin-vue-router` plugin automatically generates TypeScript types in `src/types/typed-router.d.ts`.

### Benefits

- ✅ Type-safe route names
- ✅ Type-safe route parameters
- ✅ Type-safe query parameters
- ✅ Autocomplete for route names

### Using Type-Safe Navigation

```typescript
import { useRouter } from 'vue-router'

const router = useRouter()

// Type-safe route navigation
router.push({
  name: '/users/[id]', // TypeScript validates this exists
  params: { id: '123' }, // TypeScript validates params
})

// Type-safe query
router.push({
  name: '/users',
  query: {
    page: '2', // TypeScript knows available query keys
    search: 'john',
  },
})
```

### Type-Safe Route Access

```typescript
import { useRoute } from 'vue-router'

const route = useRoute()

// TypeScript knows available params for current route
const userId = route.params.id // Type-safe
const teamId = route.params.team // Type-safe

// TypeScript knows available query keys
const page = route.query.page // Type-safe
```

## Navigation

### Using `useRouter()`

```vue
<script setup lang="ts">
const router = useRouter()

// Navigate by name
router.push({ name: '/dashboard' })

// Navigate by path
router.push('/dashboard')

// Navigate with params
router.push({
  name: '/users/[id]',
  params: { id: '123' },
})

// Navigate with query
router.push({
  name: '/users',
  query: { page: 2 },
})

// Replace current route (no history entry)
router.replace('/dashboard')

// Go back
router.back()

// Go forward
router.forward()
</script>
```

### Using `useRoute()`

```vue
<script setup lang="ts">
const route = useRoute()

// Access current route info
const routeName = route.name
const fullPath = route.fullPath
const path = route.path
const params = route.params
const query = route.query
const meta = route.meta
</script>
```

### Helper Composable

```typescript
// src/composables/use-route-helper.ts
export function useRouteHelper() {
  const router = useRouter()
  const route = useRoute()

  // Generate route URL
  function routeName(name: string, params: Record<string, any> = {}) {
    return router.resolve({ name, params }).href
  }

  // Check if current route matches
  function routeCurrent(name: string) {
    return route.name === name
  }

  // Navigate to route
  function routeVisit(name: string, params: Record<string, any> = {}) {
    router.push({ name, params })
  }

  return { routeName, routeCurrent, routeVisit, router, route }
}
```

## Excluded Paths

Routes are NOT generated from these directories (configured in `vite.config.ts`):

- `**/components/**` - Component files
- `**/layouts/**` - Layout files
- `**/data/**` - Data files
- `**/types/**` - Type definition files

## Examples

### Example 1: Protected Dashboard Page

```vue
<!-- src/pages/dashboard/index.vue -->
<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
// Page content
</script>

<template>
  <div>Dashboard Content</div>
</template>
```

### Example 2: User Detail Page with Dynamic ID

```vue
<!-- src/pages/users/[id].vue -->
<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { useRoute } from 'vue-router'

import { useGetUserQuery } from '@/services/api/user.api'

const route = useRoute()
const userId = computed(() => Number(route.params.id))
const { data: user } = useGetUserQuery(userId.value)
</script>

<template>
  <div v-if="user">
    <h1>{{ user.name }}</h1>
    <p>{{ user.email }}</p>
  </div>
</template>
```

### Example 3: Search Page with Query Parameters

```vue
<!-- src/pages/search/index.vue -->
<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const searchQuery = ref((route.query.q as string) || '')
const page = computed(() => Number(route.query.page) || 1)

function performSearch() {
  router.push({
    name: '/search',
    query: {
      q: searchQuery.value,
      page: 1,
    },
  })
}
</script>

<template>
  <div>
    <input v-model="searchQuery" @keyup.enter="performSearch">
    <button @click="performSearch">
      Search
    </button>
    <p>Page: {{ page }}</p>
  </div>
</template>
```

### Example 4: Team Settings with Dynamic Parameter

```vue
<!-- src/pages/teams/[team].vue -->
<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const authStore = useAuthStore()

const teamId = computed(() => Number(route.params.team))
const user = computed(() => authStore.user)
const team = computed(() => {
  if (!user.value?.teams)
    return null
  return (
    user.value.teams.find(t => t.id === teamId.value) ||
    user.value.currentTeam
  )
})
</script>

<template>
  <div v-if="team">
    <h1>{{ team.name }}</h1>
    <p>Team ID: {{ team.id }}</p>
  </div>
</template>
```

## Troubleshooting

### Route Not Found

**Problem**: Route doesn't exist or 404 error

**Solutions**:

1. Check file exists in `src/pages` directory
2. Ensure file has `.vue` extension
3. Check file is not in excluded directory (components, layouts, etc.)
4. Restart dev server after creating new routes
5. Check `src/types/typed-router.d.ts` for generated route names

### Route Params Not Working

**Problem**: `route.params` is empty or undefined

**Solutions**:

1. Ensure filename uses brackets: `[id].vue` not `id.vue`
2. Check route name matches when navigating: `router.push({ name: '/users/[id]', params: { id: '123' } })`
3. Verify param name matches filename: `[id].vue` → `route.params.id`

### Query Parameters Not Persisting

**Problem**: Query params disappear on navigation

**Solutions**:

1. Use `router.push()` with query object, not string concatenation
2. Check if route guard is redirecting and losing query
3. Ensure query is included in redirect: `query: { redirect: to.fullPath }`

### Layout Not Applied

**Problem**: Layout wrapper not showing

**Solutions**:

1. Check `meta.layout` is set correctly in route block
2. Verify layout file exists in `src/layouts/`
3. Ensure `vite-plugin-vue-layouts` is configured in `vite.config.ts`
4. Check default layout is set: `defaultLayout: 'default'`

### Type Errors in Route Names

**Problem**: TypeScript errors for route names

**Solutions**:

1. Regenerate types: restart dev server
2. Check `src/types/typed-router.d.ts` exists and is up to date
3. Ensure `typed-router.d.ts` is included in `tsconfig.json`
4. Use exact route names from generated types

### Auth Guard Not Working

**Problem**: Protected routes accessible without login

**Solutions**:

1. Verify `meta.auth: true` is set in route block
2. Check auth guard is registered: `createRouterGuard(router)`
3. Verify auth store is properly initialized
4. Check `isLogin` state in auth store

## Best Practices

1. **Use Route Names**: Prefer route names over paths for type safety

   ```typescript
   // ✅ Good
   router.push({ name: '/dashboard' })

   // ⚠️ Less type-safe
   router.push('/dashboard')
   ```

2. **Define Meta Early**: Put `<route>` block at top of component

   ```vue
   <route lang="yaml">
   meta:
     auth: true
   </route>

   <script setup lang="ts">
   // Component code
   </script>
   ```

3. **Type Query Parameters**: Cast query params to expected types

   ```typescript
   const page = computed(() => Number(route.query.page) || 1)
   const search = computed(() => (route.query.search as string) || '')
   ```

4. **Use Computed for Reactive Params**: Make params reactive

   ```typescript
   const userId = computed(() => Number(route.params.id))
   ```

5. **Preserve Query on Redirect**: Include query in redirects

   ```typescript
   return {
     name: '/login',
     query: { redirect: to.fullPath },
   }
   ```

6. **Use Catch-All for 404**: Create `[...path].vue` for unmatched routes

7. **Organize by Feature**: Group related pages in directories
   ```
   src/pages/
   ├── users/
   │   ├── index.vue
   │   ├── [id].vue
   │   └── create.vue
   └── teams/
       ├── index.vue
       └── [team].vue
   ```

## Production Considerations

1. **Route Chunking**: Routes are automatically code-split by `unplugin-vue-router`
2. **Type Generation**: Commit `typed-router.d.ts` to version control
3. **404 Handling**: Ensure catch-all route is configured for production
4. **Auth Guards**: Test authentication flow thoroughly before deployment
5. **Query Security**: Validate and sanitize query parameters server-side
6. **Route Protection**: Use meta guards for sensitive routes

## Additional Resources

- [unplugin-vue-router Documentation](https://github.com/posva/unplugin-vue-router)
- [Vue Router Documentation](https://router.vuejs.org/)
- [vite-plugin-vue-layouts](https://github.com/JohnCampionJr/vite-plugin-vue-layouts)

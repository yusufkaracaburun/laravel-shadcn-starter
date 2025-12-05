import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuth } from '@/composables/use-auth'
import { useAuthStore } from '@/stores/auth'

// Mock Vue Router
const mockPush = vi.fn()
const mockCurrentRoute = {
  value: {
    query: {} as Record<string, string>,
    path: '/',
  },
}

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
    currentRoute: mockCurrentRoute,
  }),
}))

describe('useAuth', () => {
  beforeEach(() => {
    // Reset Pinia store before each test
    setActivePinia(createPinia())
    // Reset mocks
    mockPush.mockClear()
    mockCurrentRoute.value.query = {}
  })

  describe('logout', () => {
    it('should set isLogin to false and redirect to sign-in', () => {
      const { logout } = useAuth()
      const authStore = useAuthStore()
      
      // Set initial state
      authStore.isLogin = true
      
      // Call logout
      logout()
      
      // Assert
      expect(authStore.isLogin).toBe(false)
      expect(mockPush).toHaveBeenCalledWith({ path: '/auth/sign-in' })
    })
  })

  describe('toHome', () => {
    it('should redirect to workspace when login completes without redirect', async () => {
      const { login } = useAuth()
      mockCurrentRoute.value.query = {}
      
      // Call login (which calls toHome internally when no redirect)
      await login()
      
      // Assert - should redirect to workspace
      expect(mockPush).toHaveBeenCalledWith({ path: '/workspace' })
    })
  })

  describe('login', () => {
    it('should set loading to true during login', async () => {
      const { login, loading } = useAuth()
      
      // Start login (don't await to check loading state)
      const loginPromise = login()
      
      // Assert loading is true
      expect(loading.value).toBe(true)
      
      // Wait for login to complete
      await loginPromise
    })

    it('should set isLogin to true after successful login', async () => {
      const { login } = useAuth()
      const authStore = useAuthStore()
      
      // Initial state
      authStore.isLogin = false
      
      // Call login
      await login()
      
      // Assert
      expect(authStore.isLogin).toBe(true)
    })

    it('should set loading to false after login completes', async () => {
      const { login, loading } = useAuth()
      
      // Call login
      await login()
      
      // Assert
      expect(loading.value).toBe(false)
    })

    it('should redirect to workspace when no redirect query param', async () => {
      const { login } = useAuth()
      mockCurrentRoute.value.query = {}
      
      // Call login
      await login()
      
      // Assert
      expect(mockPush).toHaveBeenCalledWith({ path: '/workspace' })
    })

    it('should redirect to specified redirect path when valid', async () => {
      const { login } = useAuth()
      mockCurrentRoute.value.query = { redirect: '/dashboard' }
      
      // Call login
      await login()
      
      // Assert
      expect(mockPush).toHaveBeenCalledWith('/dashboard')
    })

    it('should redirect to workspace when redirect starts with //', async () => {
      const { login } = useAuth()
      mockCurrentRoute.value.query = { redirect: '//example.com' }
      
      // Call login
      await login()
      
      // Assert - should go to workspace, not the redirect
      expect(mockPush).toHaveBeenCalledWith({ path: '/workspace' })
    })

    it('should complete login within reasonable time', async () => {
      const { login } = useAuth()
      const startTime = Date.now()
      
      // Call login
      await login()
      
      const endTime = Date.now()
      const duration = endTime - startTime
      
      // Should take approximately 1 second (with some tolerance)
      expect(duration).toBeGreaterThanOrEqual(900) // At least 900ms
      expect(duration).toBeLessThan(1500) // Less than 1.5s
    })
  })

  describe('returned values', () => {
    it('should return loading ref', () => {
      const { loading } = useAuth()
      
      expect(loading).toBeDefined()
      expect(loading.value).toBe(false)
    })

    it('should return logout function', () => {
      const { logout } = useAuth()
      
      expect(logout).toBeDefined()
      expect(typeof logout).toBe('function')
    })

    it('should return login function', () => {
      const { login } = useAuth()
      
      expect(login).toBeDefined()
      expect(typeof login).toBe('function')
    })
  })
})


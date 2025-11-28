import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { useAuth } from '@/composables/use-auth'
import { useAuthStore } from '@/stores/auth'

// Mock router
const mockPush = vi.fn()
const mockCurrentRoute = {
  value: {
    query: {},
    path: '/',
  },
}

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
    currentRoute: mockCurrentRoute,
  }),
  useRoute: () => mockCurrentRoute.value,
}))

describe('useAuth', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    mockPush.mockClear()
  })

  it('should initialize with loading false', () => {
    const { loading } = useAuth()
    expect(loading.value).toBe(false)
  })

  it('should logout and redirect to sign-in', () => {
    const { logout } = useAuth()
    const authStore = useAuthStore()
    authStore.isLogin = true

    logout()

    expect(authStore.isLogin).toBe(false)
    expect(mockPush).toHaveBeenCalledWith({ path: '/auth/sign-in' })
  })

  it('should redirect to home when login without redirect query', async () => {
    const { login } = useAuth()
    const authStore = useAuthStore()
    mockCurrentRoute.value.query = {}

    await login()

    expect(mockPush).toHaveBeenCalledWith({ path: '/dashboard' })
  })

  it('should login successfully and redirect to home when no redirect query', async () => {
    const { login, loading } = useAuth()
    const authStore = useAuthStore()
    mockCurrentRoute.value.query = {}

    await login()

    expect(loading.value).toBe(false)
    expect(authStore.isLogin).toBe(true)
    expect(mockPush).toHaveBeenCalledWith({ path: '/dashboard' })
  })

  it('should login and redirect to query redirect path', async () => {
    const { login } = useAuth()
    const authStore = useAuthStore()
    mockCurrentRoute.value.query = { redirect: '/settings' }

    await login()

    expect(authStore.isLogin).toBe(true)
    expect(mockPush).toHaveBeenCalledWith('/settings')
  })

  it('should redirect to home when redirect starts with //', async () => {
    const { login } = useAuth()
    const authStore = useAuthStore()
    mockCurrentRoute.value.query = { redirect: '//external.com' }

    await login()

    expect(authStore.isLogin).toBe(true)
    expect(mockPush).toHaveBeenCalledWith({ path: '/dashboard' })
  })

  it('should set loading to true during login', async () => {
    const { login, loading } = useAuth()
    mockCurrentRoute.value.query = {}

    const loginPromise = login()
    expect(loading.value).toBe(true)

    await loginPromise
    expect(loading.value).toBe(false)
  })
})

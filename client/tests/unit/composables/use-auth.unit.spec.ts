import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { useAuth } from '@/composables/use-auth'

// Mock TanStack Query before store import
const mockRefetch = vi.fn()
const mockMutateAsync = vi.fn()
const loginCredentials = { email: 'test@example.com', password: 'password' }

vi.mock('@tanstack/vue-query', () => ({
  useQuery: vi.fn(() => ({
    data: { value: null },
    isLoading: { value: false },
    refetch: mockRefetch,
  })),
  useMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
    isPending: { value: false },
  })),
  useQueryClient: vi.fn(() => ({
    invalidateQueries: vi.fn(),
    setQueryData: vi.fn(),
    removeQueries: vi.fn(),
  })),
}))

// Mock API services before store import
vi.mock('@/services/api/auth.api', () => ({
  useGetCurrentUserQuery: vi.fn(() => ({
    data: { value: null },
    isLoading: { value: false },
    refetch: mockRefetch,
  })),
  useLoginMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
  })),
  useRegisterMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
  })),
  useLogoutMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
  })),
}))

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
    mockMutateAsync.mockResolvedValue({ success: true })
  })

  it('should initialize with loading false', () => {
    // Arrange & Act
    const { loading } = useAuth()

    // Assert
    expect(loading.value).toBe(false)
  })

  it('should have logout function that redirects to sign-in', () => {
    // Arrange & Act
    const { logout } = useAuth()
    logout()

    // Assert
    expect(mockPush).toHaveBeenCalledWith({ path: '/auth/sign-in' })
  })

  it('should redirect to home when login without redirect query', async () => {
    // Arrange
    const { login } = useAuth()
    mockCurrentRoute.value.query = {}

    // Act
    await login(loginCredentials)

    // Assert
    expect(mockPush).toHaveBeenCalledWith({ path: '/dashboard' })
  })

  it('should redirect to query redirect path when provided', async () => {
    // Arrange
    const { login } = useAuth()
    mockCurrentRoute.value.query = { redirect: '/settings' }

    // Act
    await login(loginCredentials)

    // Assert
    expect(mockPush).toHaveBeenCalledWith('/settings')
  })

  it('should redirect to home when redirect starts with //', async () => {
    // Arrange
    const { login } = useAuth()
    mockCurrentRoute.value.query = { redirect: '//external.com' }

    // Act
    await login(loginCredentials)

    // Assert
    expect(mockPush).toHaveBeenCalledWith({ path: '/dashboard' })
  })

  it('should set loading to true during login', async () => {
    // Arrange
    const { login, loading } = useAuth()
    mockCurrentRoute.value.query = {}

    // Act
    const loginPromise = login(loginCredentials)

    // Assert - loading should be true during async operation
    expect(loading.value).toBe(true)

    // Wait for login to complete
    await loginPromise
    expect(loading.value).toBe(false)
  })
})

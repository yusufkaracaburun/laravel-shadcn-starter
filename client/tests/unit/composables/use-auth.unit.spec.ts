import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'

import { useAuth } from '@/composables/use-auth'

// Mock TanStack Query before store import
const mockRefetch = vi.fn()
const mockMutateAsync = vi.fn()
const loginCredentials = { email: 'test@example.com', password: 'password' }

// Mock user response
const mockUserResponse = {
  data: {
    data: {
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
    },
  },
}

// Create shared reactive refs for mutations using Vue's ref for proper reactivity
const loginPending = ref(false)
const registerPending = ref(false)
const logoutPending = ref(false)

vi.mock('@tanstack/vue-query', () => ({
  useQuery: vi.fn(() => ({
    data: { value: null },
    isLoading: { value: false },
    refetch: mockRefetch,
  })),
  useMutation: vi.fn(() => {
    const isPending = { value: false }
    return {
      mutateAsync: mockMutateAsync,
      isPending,
    }
  }),
  useQueryClient: vi.fn(() => ({
    invalidateQueries: vi.fn(),
    setQueryData: vi.fn(),
    removeQueries: vi.fn(),
  })),
}))

// Mock services
vi.mock('@/services/users.service', () => ({
  useGetCurrentUserQuery: vi.fn(() => ({
    data: { value: null },
    isLoading: { value: false },
    refetch: mockRefetch,
  })),
}))

vi.mock('@/services/auth.service', () => ({
  useLoginMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
    isPending: loginPending,
  })),
  useRegisterMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
    isPending: registerPending,
  })),
  useLogoutMutation: vi.fn(() => ({
    mutateAsync: mockMutateAsync,
    isPending: logoutPending,
  })),
}))

// Mock router
const mockPush = vi.fn()
const mockCurrentRoute = {
  value: {
    query: {},
    path: '/',
    fullPath: '/',
  },
}

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
    currentRoute: mockCurrentRoute,
  }),
  useRoute: () => mockCurrentRoute.value,
}))

// Mock error store
vi.mock('@/stores/error.store', () => ({
  useErrorStore: vi.fn(() => ({
    setError: vi.fn(),
    getErrorMessage: vi.fn((error: any) => error?.message || 'Error occurred'),
    getValidationErrors: vi.fn(() => ({})),
  })),
}))

// Mock toast
vi.mock('@/composables/use-toast', () => ({
  useToast: vi.fn(() => ({
    showSuccess: vi.fn(),
    showError: vi.fn(),
  })),
}))

describe('useAuth', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    mockPush.mockClear()
    mockMutateAsync.mockResolvedValue({ success: true })
    mockRefetch.mockResolvedValue(mockUserResponse)
    // Reset pending states
    loginPending.value = false
    registerPending.value = false
    logoutPending.value = false
  })

  it('should initialize with loading false', () => {
    // Arrange & Act
    const { loading } = useAuth()

    // Assert
    expect(loading.value).toBe(false)
  })

  it('should have logout function that redirects to sign-in', async () => {
    // Arrange & Act
    const { logout } = useAuth()
    await logout()

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
    mockCurrentRoute.value.query = {}
    const { login, loading } = useAuth()

    // Initially loading should be false
    expect(loading.value).toBe(false)

    // Set login mutation to pending
    loginPending.value = true

    // Assert - loading should be true when mutation is pending
    expect(loading.value).toBe(true)

    // Act - start login (but don't await yet)
    const loginPromise = login(loginCredentials)

    // Loading should still be true during async operation
    expect(loading.value).toBe(true)

    // Reset pending and wait for completion
    loginPending.value = false
    await loginPromise

    // After completion, loading should be false
    expect(loading.value).toBe(false)
  })
})

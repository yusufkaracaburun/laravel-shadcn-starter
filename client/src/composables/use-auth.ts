import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { toast } from 'vue-sonner'

import type { IResponse } from '@/services/types/response.type'

import { useAxios } from '@/composables/use-axios'
import { RouterPath } from '@/constants/route-path'

// ============================================================================
// Types
// ============================================================================

export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  current_team_id: number | null
  profile_photo_path: string | null
  currentTeam: Team | null
  teams: Team[]
  oauthConnections: OauthConnection[]
}

export interface Team {
  id: number
  name: string
  personal_team: boolean
  user_id: number
  created_at: string
  updated_at: string
}

export interface OauthConnection {
  id: number
  user_id: number
  provider: string
  provider_id: string
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  terms?: boolean
}

// ============================================================================
// Composable
// ============================================================================

/**
 * Unified authentication composable
 * Handles API calls, state management, and UI concerns (toasts, routing)
 */
export function useAuth() {
  const router = useRouter()
  const queryClient = useQueryClient()
  const { axiosInstance, authAxiosInstance } = useAxios()

  // ============================================================================
  // User Data Query
  // ============================================================================

  const { data: userData, isLoading: userLoading, refetch: refetchUser } = useQuery<IResponse<User>, AxiosError>({
    queryKey: ['currentUser'],
    queryFn: async () => {
      const response = await axiosInstance.get('/user/current', { withCredentials: true })
      return response.data
    },
    retry: false,
  })

  const user = computed(() => userData.value?.data ?? null)
  const isAuthenticated = computed(() => !!user.value)

  // ============================================================================
  // Helper Functions
  // ============================================================================

  function toHome() {
    router.push({ path: RouterPath.HOME as string })
  }

  function handleAuthError(error: any) {
    const axiosError = error as AxiosError<{ message?: string, errors?: Record<string, string[]> }>
    const errors = axiosError.response?.data?.errors || {}
    const message = axiosError.response?.data?.message || 'Authentication failed'

    if (Object.keys(errors).length > 0) {
      const errorMessages = Object.values(errors).flat().join(', ')
      return { errors, message: errorMessages }
    }

    return { errors: {}, message }
  }

  // ============================================================================
  // Login
  // ============================================================================

  const loginMutation = useMutation<{ success: boolean }, AxiosError<{ message?: string, errors?: Record<string, string[]> }>, LoginCredentials>({
    mutationKey: ['login'],
    mutationFn: async (credentials: LoginCredentials) => {
      // Get CSRF cookie first, then login
      // Using .then() ensures the cookie is set before the login request
      return await authAxiosInstance.get('/sanctum/csrf-cookie').then(() => {
        return authAxiosInstance.post('/login', credentials).then((response) => {
          return { success: response.status === 204 || response.status === 200 }
        })
      })
    },
    onSuccess: () => {
      // Invalidate and refetch user data
      queryClient.invalidateQueries({ queryKey: ['currentUser'] })
    },
  })

  async function login(credentials: LoginCredentials) {
    try {
      const result = await loginMutation.mutateAsync(credentials)

      if (result?.success) {
        toast.success('Login successful')
        const redirect = router.currentRoute.value.query.redirect as string
        if (!redirect || redirect.startsWith('//')) {
          toHome()
        }
        else {
          router.push(redirect)
        }
        return { success: true }
      }

      return { success: false }
    }
    catch (error: any) {
      const { message } = handleAuthError(error)
      toast.error('Login failed', { description: message })
      return { success: false, ...handleAuthError(error) }
    }
  }

  // ============================================================================
  // Register
  // ============================================================================

  const registerMutation = useMutation<{ success: boolean }, AxiosError<{ message?: string, errors?: Record<string, string[]> }>, RegisterData>({
    mutationKey: ['register'],
    mutationFn: async (data: RegisterData) => {
      // Get CSRF cookie first, then register
      // Using .then() ensures the cookie is set before the register request
      return await authAxiosInstance.get('/sanctum/csrf-cookie').then(() => {
        return authAxiosInstance.post('/register', {
          ...data,
          terms: data.terms ?? true,
        }).then((response) => {
          return { success: response.status === 201 || response.status === 200 }
        })
      })
    },
    onSuccess: () => {
      // Invalidate and refetch user data
      queryClient.invalidateQueries({ queryKey: ['currentUser'] })
    },
  })

  async function register(data: RegisterData) {
    try {
      const result = await registerMutation.mutateAsync(data)

      if (result?.success) {
        toast.success('Registration successful')
        toHome()
        return { success: true }
      }

      return { success: false }
    }
    catch (error: any) {
      const { message } = handleAuthError(error)
      toast.error('Registration failed', { description: message })
      return { success: false, ...handleAuthError(error) }
    }
  }

  // ============================================================================
  // Logout
  // ============================================================================

  const logoutMutation = useMutation<void, AxiosError, void>({
    mutationKey: ['logout'],
    mutationFn: async () => {
      await authAxiosInstance.post('/logout', {})
    },
    onSuccess: () => {
      // Clear user data
      queryClient.setQueryData(['currentUser'], null)
      queryClient.removeQueries({ queryKey: ['currentUser'] })
    },
  })

  async function logout() {
    try {
      await logoutMutation.mutateAsync()
      toast.success('Logged out successfully')
      router.push({ path: RouterPath.LOGIN as string })
    }
    catch (error: any) {
      toast.error('Logout failed', {
        description: error?.message ?? 'An unexpected error occurred',
      })
    }
  }

  // ============================================================================
  // Return
  // ============================================================================

  return {
    // State
    user,
    isAuthenticated,
    loading: computed(() => loginMutation.isPending.value || registerMutation.isPending.value || logoutMutation.isPending.value || userLoading.value),

    // Actions
    login,
    register,
    logout,
    fetchUser: refetchUser,
  }
}

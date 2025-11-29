import { toast } from 'vue-sonner'

import type { LoginCredentials, RegisterData } from '@/services/api/auth.api'

import { RouterPath } from '@/constants/route-path'
import { useAuthStore } from '@/stores/auth'

export function useAuth() {
  const router = useRouter()
  const authStore = useAuthStore()

  function toHome() {
    router.push({ path: RouterPath.HOME as string })
  }

  async function login(credentials: LoginCredentials) {
    try {
      const result = await authStore.login(credentials)

      if (result && 'success' in result && result.success === false) {
        // Handle validation errors
        if ('errors' in result && result.errors && Object.keys(result.errors).length > 0) {
          const errorMessages = Object.values(result.errors).flat().join(', ')
          toast.error('Login failed', {
            description: errorMessages,
          })
        }
        else {
          const message = 'message' in result ? result.message : 'Invalid credentials'
          toast.error('Login failed', {
            description: message,
          })
        }
        return { success: false }
      }

      // Success - redirect
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
    catch (error: any) {
      toast.error('Login failed', {
        description: error?.message ?? 'An unexpected error occurred',
      })
      return { success: false }
    }
  }

  async function register(data: RegisterData) {
    try {
      const result = await authStore.register(data)

      if (result?.success === false) {
        // Handle validation errors
        if ('errors' in result && result.errors && Object.keys(result.errors).length > 0) {
          const errorMessages = Object.values(result.errors).flat().join(', ')
          toast.error('Registration failed', {
            description: errorMessages,
          })
        }
        else {
          const message = 'message' in result ? result.message : 'Registration failed'
          toast.error('Registration failed', {
            description: message,
          })
        }
        return { success: false }
      }

      // Success - redirect to dashboard
      toast.success('Registration successful')
      toHome()

      return { success: true }
    }
    catch (error: any) {
      toast.error('Registration failed', {
        description: error?.message ?? 'An unexpected error occurred',
      })
      return { success: false }
    }
  }

  async function logout() {
    try {
      await authStore.logout()
      toast.success('Logged out successfully')
      router.push({ path: RouterPath.LOGIN as string })
    }
    catch (error: any) {
      toast.error('Logout failed', {
        description: error?.message ?? 'An unexpected error occurred',
      })
    }
  }

  return {
    loading: computed(() => authStore.loading),
    login,
    register,
    logout,
  }
}

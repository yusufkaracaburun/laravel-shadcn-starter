import { storeToRefs } from 'pinia'
import { toast } from 'vue-sonner'

import type { LoginCredentials } from '@/services/auth.service'

import { getUser, login as loginApi, logout as logoutApi } from '@/services/auth.service'
import { useAuthStore } from '@/stores/auth'

export function useAuth() {
  const router = useRouter()

  const authStore = useAuthStore()
  const { isLogin } = storeToRefs(authStore)
  const loading = ref(false)
  const error = ref<string | null>(null)

  function toHome() {
    router.push({ path: '/workspace' })
  }

  async function login(credentials: LoginCredentials) {
    loading.value = true
    error.value = null

    try {
      // Call login API (handles CSRF cookie automatically)
      await loginApi(credentials)

      // Fetch authenticated user
      const user = await getUser()

      // Update auth store
      authStore.setUser(user)

      // Show success toast
      toast.success('Login successful', {
        description: `Welcome back, ${user.name}!`,
      })

      // Handle redirect
      const redirect = router.currentRoute.value.query.redirect as string
      if (!redirect || redirect.startsWith('//')) {
        toHome()
      }
      else {
        router.push(redirect)
      }
    }
    catch (err: any) {
      // Handle validation errors (422)
      if (err.response?.status === 422) {
        const errors = err.response.data.errors
        if (errors?.email) {
          error.value = errors.email[0]
        }
        else if (errors?.password) {
          error.value = errors.password[0]
        }
        else {
          error.value = 'Invalid credentials. Please try again.'
        }
        toast.error('Login failed', {
          description: error.value,
        })
      }
      // Handle authentication errors (401)
      else if (err.response?.status === 401) {
        error.value = 'Invalid credentials. Please try again.'
        toast.error('Login failed', {
          description: error.value,
        })
      }
      // Handle network errors
      else {
        error.value = 'An error occurred. Please try again later.'
        toast.error('Login failed', {
          description: error.value,
        })
      }
    }
    finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await logoutApi()
      authStore.setUser(null)
      router.push({ path: '/auth/sign-in' })
      toast.success('Logged out successfully')
    }
    catch (err: any) {
      // Even if logout API fails, clear local state
      authStore.setUser(null)
      router.push({ path: '/auth/sign-in' })
      toast.error('Error during logout', {
        description: 'You have been logged out locally.',
      })
    }
  }

  return {
    loading,
    error,
    logout,
    login,
  }
}

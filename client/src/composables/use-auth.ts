import { storeToRefs } from 'pinia'

import type { LoginRequest, RegisterRequest } from '@/services/auth.service'

import { useToast } from '@/composables/use-toast'
import { RouterPath } from '@/constants/route-path'
import { useLoginMutation, useLogoutMutation, useRegisterMutation } from '@/services/auth.service'
import { useGetCurrentUserQuery } from '@/services/users.service'
import { useAuthStore } from '@/stores/auth.store'

export function useAuth() {
  const router = useRouter()
  const toast = useToast()
  const authStore = useAuthStore()
  const { user, isAuthenticated } = storeToRefs(authStore)

  const loginMutation = useLoginMutation()
  const logoutMutation = useLogoutMutation()
  const registerMutation = useRegisterMutation()
  const { data: currentUser, refetch: fetchCurrentUser } = useGetCurrentUserQuery()

  // Watch for current user changes and update store
  watch(currentUser, (newUser) => {
    if (newUser?.data) {
      authStore.setUser(newUser.data)
    }
  }, { immediate: true })

  const loading = computed(() => loginMutation.isPending.value || logoutMutation.isPending.value || registerMutation.isPending.value)

  async function login(credentials: LoginRequest) {
    try {
      await loginMutation.mutateAsync(credentials)

      // Fetch current user after successful login
      const userResponse = await fetchCurrentUser()

      if (userResponse.data?.data) {
        authStore.setUser(userResponse.data.data)
        toast.showSuccess('Logged in successfully!')

        // Handle redirect
        const redirect = router.currentRoute.value.query.redirect as string
        if (!redirect || redirect.startsWith('//')) {
          router.push({ path: RouterPath.HOME as string })
        }
        else {
          router.push(redirect)
        }
      }
    }
    catch (error: any) {
      // Handle validation errors (422)
      if (error.response?.status === 422) {
        const errors = error.response.data.errors
        const firstError = Object.values(errors)[0] as string[]
        toast.showError(firstError?.[0] || 'Validation failed')
      }
      else if (error.response?.status === 401) {
        toast.showError('Invalid email or password')
      }
      else {
        toast.showError('Login failed. Please try again.')
      }
      throw error
    }
  }

  async function register(data: RegisterRequest) {
    try {
      await registerMutation.mutateAsync(data)

      // Fetch current user after successful registration
      const userResponse = await fetchCurrentUser()

      if (userResponse.data?.data) {
        authStore.setUser(userResponse.data.data)
        toast.showSuccess('Account created successfully!')

        // Redirect to home after registration
        router.push({ path: RouterPath.HOME as string })
      }
    }
    catch (error: any) {
      // Handle validation errors (422)
      if (error.response?.status === 422) {
        const errors = error.response.data.errors
        const firstError = Object.values(errors)[0] as string[]
        toast.showError(firstError?.[0] || 'Validation failed')
      }
      else {
        toast.showError('Registration failed. Please try again.')
      }
      throw error
    }
  }

  async function logout() {
    try {
      await logoutMutation.mutateAsync()
      authStore.clearUser()
      toast.showSuccess('Logged out successfully!')
      router.push({ path: RouterPath.LOGIN as string })
    }
    catch (error: any) {
      console.error('Logout failed', error)
      // Even if logout fails, clear local state
      authStore.clearUser()
      toast.showError('Logout failed, but you have been logged out locally.')
      router.push({ path: RouterPath.LOGIN as string })
    }
  }

  async function checkAuth() {
    try {
      const userResponse = await fetchCurrentUser()
      if (userResponse.data?.data) {
        authStore.setUser(userResponse.data.data)
        return true
      }
      return false
    }
    catch {
      authStore.clearUser()
      return false
    }
  }

  return {
    user,
    isAuthenticated,
    loading,
    login,
    register,
    logout,
    checkAuth,
    fetchCurrentUser,
  }
}

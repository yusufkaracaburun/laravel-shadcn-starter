import { storeToRefs } from 'pinia'

import type { LoginRequest, RegisterRequest } from '@/services/auth.service'

import { useToast } from '@/composables/use-toast'
import { RouterPath } from '@/constants/route-path'
import { useLoginMutation, useLogoutMutation, useRegisterMutation } from '@/services/auth.service'
import { useGetCurrentUserQuery } from '@/services/users.service'
import { useAuthStore } from '@/stores/auth.store'
import { useErrorStore } from '@/stores/error.store'

export function useAuth() {
  const router = useRouter()
  const toast = useToast()
  const authStore = useAuthStore()
  const errorStore = useErrorStore()
  const { user, isAuthenticated } = storeToRefs(authStore)

  const loginMutation = useLoginMutation()
  const logoutMutation = useLogoutMutation()
  const registerMutation = useRegisterMutation()
  const { data: currentUser, refetch: fetchCurrentUser } = useGetCurrentUserQuery()

  // Watch for current user changes and update store
  watch(
    () => currentUser.value,
    (newUser) => {
      if (newUser?.data) {
        authStore.setUser(newUser.data)
      }
    },
    { immediate: true },
  )

  const loading = computed(
    () =>
      loginMutation.isPending.value ||
      logoutMutation.isPending.value ||
      registerMutation.isPending.value,
  )

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
        } else {
          router.push(redirect)
        }
      }
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'login' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
        toast.showError(message)
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
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'register' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
        toast.showError(message)
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
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'logout' })

      // Even if logout fails, clear local state
      authStore.clearUser()

      // Use error store for message
      const message = errorStore.getErrorMessage(error)
      toast.showError(message || 'Logout failed, but you have been logged out locally.')
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
    } catch {
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

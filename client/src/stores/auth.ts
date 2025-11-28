import { computed } from 'vue'
import { defineStore } from 'pinia'

import type { User } from '@/services/api/auth.api'
import { useGetCurrentUserQuery, useLoginMutation, useRegisterMutation, useLogoutMutation } from '@/services/api/auth.api'

import type { LoginCredentials, RegisterData } from '@/services/api/auth.api'

export const useAuthStore = defineStore('auth', () => {
  const loading = ref(false)

  // Use TanStack Query for user data
  const { data: userData, isLoading: userLoading, refetch: refetchUser } = useGetCurrentUserQuery()
  const user = computed(() => userData.value?.data ?? null)

  const isAuthenticated = computed(() => !!user.value)
  const isLogin = computed(() => isAuthenticated.value)

  // Mutations
  const loginMutation = useLoginMutation()
  const registerMutation = useRegisterMutation()
  const logoutMutation = useLogoutMutation()

  async function fetchUser() {
    loading.value = true
    try {
      await refetchUser()
    }
    catch (error) {
      console.error('Failed to fetch user:', error)
    }
    finally {
      loading.value = false
    }
  }

  async function login(credentials: LoginCredentials) {
    loading.value = true
    try {
      const result = await loginMutation.mutateAsync(credentials)
      return result
    }
    catch (error: any) {
      const errors = error.response?.data?.errors || {}
      const message = error.response?.data?.message || 'Login failed'
      return {
        success: false,
        errors,
        message,
      }
    }
    finally {
      loading.value = false
    }
  }

  async function register(data: RegisterData) {
    loading.value = true
    try {
      const result = await registerMutation.mutateAsync(data)
      // Mutation returns { success: boolean }
      if (result && result.success) {
        return { success: true }
      }
      return { success: false, message: 'Registration failed' }
    }
    catch (error: any) {
      const errors = error.response?.data?.errors || {}
      const message = error.response?.data?.message || 'Registration failed'
      return {
        success: false,
        errors,
        message,
      }
    }
    finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true
    try {
      await logoutMutation.mutateAsync()
    }
    catch (error) {
      console.error('Logout error:', error)
    }
    finally {
      loading.value = false
    }
  }

  return {
    user,
    loading: computed(() => loading.value || userLoading.value),
    isAuthenticated,
    isLogin,
    fetchUser,
    login,
    register,
    logout,
  }
})

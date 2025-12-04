import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'

import { useToast } from '@/composables/use-toast'
import { useGetUsersQuery } from '@/services/users.service'
import { useAuthStore } from '@/stores/auth.store'
import { useErrorStore } from '@/stores/error.store'

export function useUsers() {
  const router = useRouter()
  const toast = useToast()
  const authStore = useAuthStore()
  const errorStore = useErrorStore()
  const { user, isAuthenticated } = storeToRefs(authStore)

  const { data: usersResponse, isLoading, isFetching, refetch: fetchUsers } = useGetUsersQuery()

  // Computed refs for easy access
  const users = computed(() => usersResponse.value?.data ?? [])
  const loading = computed(() => isLoading.value || isFetching.value)

  async function fetchUsersData() {
    try {
      const usersResponse = await fetchUsers()
      return usersResponse.data
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'fetchUsers' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  return {
    users,
    loading,
    fetchUsersData,
    usersResponse,
  }
}

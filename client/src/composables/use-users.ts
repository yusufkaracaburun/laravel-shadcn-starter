import type { SortingState } from '@tanstack/vue-table'

import type { ServerPagination } from '@/components/data-table/types'
import type { CreateUserRequest, UpdateUserRequest } from '@/services/users.service'

import { useToast } from '@/composables/use-toast'
import {
  useCreateUserMutation,
  useDeleteUserMutation,
  useGetUsersQuery,
  useUpdateUserMutation,
} from '@/services/users.service'
import { useErrorStore } from '@/stores/error.store'

export function useUsers() {
  const toast = useToast()
  const errorStore = useErrorStore()

  // Pagination state
  const page = ref(1)
  const pageSize = ref(15)

  // Sorting state - managed here and passed to table
  const sorting = ref<SortingState>([])

  // Handler for sorting changes from table
  function onSortingChange(newSorting: SortingState) {
    sorting.value = newSorting
    // Reset to first page when sorting changes
    page.value = 1
  }

  const { data: usersResponse, isLoading, isFetching, refetch: fetchUsers } = useGetUsersQuery(
    page,
    pageSize,
    sorting,
  )

  // Computed refs for easy access
  const users = computed(() => usersResponse.value?.data?.data ?? [])
  const loading = computed(() => isLoading.value || isFetching.value)

  // Extract pagination metadata from Laravel's pagination structure
  const pagination = computed(() => usersResponse.value?.data ?? {
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: null,
    to: null,
  })

  // Pagination handlers
  function onPageChange(newPage: number) {
    page.value = newPage
  }

  function onPageSizeChange(newPageSize: number) {
    pageSize.value = newPageSize
    page.value = 1 // Reset to first page when page size changes
  }

  // Server pagination object for data-table
  const serverPagination = computed<ServerPagination>(() => ({
    page: pagination.value.current_page,
    pageSize: pagination.value.per_page,
    total: pagination.value.total,
    onPageChange,
    onPageSizeChange,
  }))

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

  // Create user mutation
  const createUserMutation = useCreateUserMutation()
  const updateUserMutation = useUpdateUserMutation()
  const deleteUserMutation = useDeleteUserMutation()

  async function createUser(data: CreateUserRequest) {
    try {
      const response = await createUserMutation.mutateAsync(data)
      toast.showSuccess('User created successfully!')
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'createUser' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      }
      else {
        toast.showError(message)
      }
      throw error
    }
  }

  async function updateUser(userId: number, data: UpdateUserRequest) {
    try {
      const response = await updateUserMutation.mutateAsync({ userId, data })
      toast.showSuccess('User updated successfully!')
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'updateUser' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      }
      else {
        toast.showError(message)
      }
      throw error
    }
  }

  async function deleteUser(userId: number) {
    try {
      await deleteUserMutation.mutateAsync(userId)
      toast.showSuccess('User deleted successfully!')
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'deleteUser' })

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
    serverPagination,
    sorting,
    onSortingChange,
    createUser,
    createUserMutation,
    updateUser,
    updateUserMutation,
    deleteUser,
    deleteUserMutation,
  }
}

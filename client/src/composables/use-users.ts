import { computed, ref, watch } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICreateUserRequest,
  IUpdateUserRequest,
  IUser,
  IUserFilters,
  IUserPrerequisites,
} from '@/pages/users/models/users'
import type { ISorting } from '@/services/query-utils'
import type {
  IPaginatedResponse,
  IResponse,
} from '@/services/types/response.type'

import { DEFAULT_PAGE, DEFAULT_PAGE_SIZE } from '@/components/data-table/types'
import { useToast } from '@/composables/use-toast'
import { useUserService } from '@/services/users.service'
import { useErrorStore } from '@/stores/error.store'

const UserContext = {
  FETCH_USER_PREREQUISITES: 'fetchUserPrerequisites',
  FETCH_USERS: 'fetchUsers',
  CREATE_USER: 'createUser',
  UPDATE_USER: 'updateUser',
  DELETE_USER: 'deleteUser',
}

const UserMessages = {
  CREATE_USER_SUCCESS: 'User created successfully!',
  UPDATE_USER_SUCCESS: 'User updated successfully!',
  DELETE_USER_SUCCESS: 'User deleted successfully!',
}

export function useUsers() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const userService = useUserService()

  const page = ref<number>(DEFAULT_PAGE)
  const pageSize = ref<TPageSize>(DEFAULT_PAGE_SIZE)
  const sort = ref<ISorting>({ id: 'created_at', desc: true })
  const filter = ref<IUserFilters>({})
  const includes = {
    roles: 'roles',
    companies: 'companies',
    permissions: 'permissions',
    invoices: 'invoices',
  }

  function onSortingChange(newSorting: ISorting): void {
    sort.value = newSorting
    page.value = DEFAULT_PAGE
  }

  function onFiltersChange(newFilters: IUserFilters): void {
    filter.value = newFilters
    page.value = DEFAULT_PAGE
  }

  function clearFilters() {
    filter.value = {} as IUserFilters
    page.value = DEFAULT_PAGE
  }

  function onPageChange(newPage: number): void {
    page.value = newPage
  }

  function onPageSizeChange(newPageSize: TPageSize): void {
    page.value = DEFAULT_PAGE
    pageSize.value = newPageSize
  }

  const getUserPrerequisitesQuery = userService.getUserPrerequisitesQuery()
  const {
    data: userPrerequisitesResponse,
    isLoading: isLoadingUserPrerequisites,
    isError: isErrorUserPrerequisites,
    error: errorUserPrerequisites,
    refetch: refetchUserPrerequisites,
  } = getUserPrerequisitesQuery
  async function fetchUserPrerequisitesData(): Promise<
    IResponse<IUserPrerequisites>
  > {
    try {
      const response = await refetchUserPrerequisites()
      return response.data as IResponse<IUserPrerequisites>
    } catch (error: any) {
      errorStore.setError(error, {
        context: UserContext.FETCH_USER_PREREQUISITES,
      })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const getUsersQuery = userService.getUsersQuery(
    page,
    pageSize,
    sort,
    filter,
    ref([includes.roles]),
  )
  const {
    data: usersData,
    isLoading,
    isFetching,
    refetch: fetchUsers,
  } = getUsersQuery
  const users = computed(() => usersData.value?.data.data ?? [])
  async function fetchUsersData(): Promise<IPaginatedResponse<IUser>> {
    try {
      const response = await fetchUsers()
      return response.data as IPaginatedResponse<IUser>
    } catch (error: any) {
      errorStore.setError(error, { context: UserContext.FETCH_USERS })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const createUserMutation = userService.createUserMutation()
  async function createUser(data: ICreateUserRequest) {
    try {
      const response = await createUserMutation.mutateAsync(data)
      toast.showSuccess(UserMessages.CREATE_USER_SUCCESS)
      return response
    } catch (error: any) {
      errorStore.setError(error, { context: UserContext.CREATE_USER })
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
        toast.showError(message)
      }
      throw error
    }
  }

  const updateUserMutation = userService.updateUserMutation()
  async function updateUser(userId: number, data: IUpdateUserRequest) {
    try {
      const response = await updateUserMutation.mutateAsync({
        id: userId,
        data,
      })
      toast.showSuccess(UserMessages.UPDATE_USER_SUCCESS)
      return response
    } catch (error: any) {
      errorStore.setError(error, { context: UserContext.UPDATE_USER })
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
        toast.showError(message)
      }
      throw error
    }
  }

  const deleteUserMutation = userService.deleteUserMutation()
  async function deleteUser(userId: number) {
    try {
      await deleteUserMutation.mutateAsync(userId)
      toast.showSuccess(UserMessages.DELETE_USER_SUCCESS)
    } catch (error: any) {
      errorStore.setError(error, { context: UserContext.DELETE_USER })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const loading = computed(() => isLoading.value || isFetching.value)

  const serverPagination = computed(() => {
    const response = usersData.value?.data
    return {
      page: response ? response.current_page : page.value,
      pageSize: pageSize.value,
      total: response ? response.total : 0,
      onPageChange,
      onPageSizeChange,
    }
  })

  watch([page, pageSize], ([newPage, newPageSize], [oldPage, oldPageSize]) => {
    if (oldPage !== newPage || oldPageSize !== newPageSize) {
      fetchUsers()
    }
  })

  return {
    sort,
    filter,
    includes,
    users,
    onSortingChange,
    onFiltersChange,
    clearFilters,
    onPageChange,
    onPageSizeChange,
    userPrerequisitesResponse,
    isLoadingUserPrerequisites,
    isErrorUserPrerequisites,
    errorUserPrerequisites,
    fetchUserPrerequisitesData,
    fetchUsersData,
    createUser,
    updateUser,
    deleteUser,
    loading,
    serverPagination,
  }
}

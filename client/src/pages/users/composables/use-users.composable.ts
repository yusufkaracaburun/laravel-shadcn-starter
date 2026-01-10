import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type {
  ICreateUserRequest,
  IUpdateUserRequest,
  IUser,
  IUserFilters,
  IUserPrerequisites,
} from '@/pages/users/models/users'
import type { IResponse } from '@/services/types/response.type'

import { useResourceBase } from '@/composables/use-resource-base.composable'
import { useToast } from '@/composables/use-toast.composable'
import { EUserRole, EUserStatus } from '@/pages/users/models/users'
import { useUserService } from '@/pages/users/services/users.service'
import { useErrorStore } from '@/stores/error.store'

const UserContext = {
  FETCH_PREREQUISITES: 'fetchUserPrerequisites',
  FETCH_LIST: 'fetchUsers',
  GET_USER: 'getUser',
  GET_USER_BY_ID: 'getUserById',
  CREATE: 'createUser',
  UPDATE: 'updateUser',
  DELETE: 'deleteUser',
}

const UserMessages = {
  CREATE_SUCCESS: 'User created successfully!',
  UPDATE_SUCCESS: 'User updated successfully!',
  DELETE_SUCCESS: 'User deleted successfully!',
}

export function useUsers() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const userService = useUserService()
  const route = useRoute()

  const includes = {
    roles: 'roles',
    companies: 'companies',
    permissions: 'permissions',
    invoices: 'invoices',
  }

  const base = useResourceBase<
    IUser,
    IUserFilters,
    ICreateUserRequest,
    IUpdateUserRequest,
    IUserPrerequisites
  >({
    service: {
      getPrerequisitesQuery: () => userService.getUserPrerequisitesQuery(),
      getListQuery: (page, per_page, sort, filter, include) =>
        userService.getUsersQuery(page, per_page, sort, filter, include),
      createMutation: () => userService.createUserMutation(),
      updateMutation: () => userService.updateUserMutation(),
      deleteMutation: () => userService.deleteUserMutation(),
      getMutation: () => userService.getUserMutation(),
    },
    context: UserContext,
    messages: UserMessages,
    defaultSort: { id: 'created_at', desc: true },
    includes,
    defaultIncludeKey: 'roles',
    onFetchList: (refetch) => {
      refetch()
    },
  })

  const userId = computed(() => {
    if (!route) {
      return undefined
    }
    const params = route.params as { id?: string | string[] }
    const idParam = Array.isArray(params.id) ? params.id[0] : params.id
    if (
      !idParam
      || typeof idParam !== 'string'
      || Number.isNaN(Number(idParam))
    ) {
      return undefined
    }
    return Number(idParam)
  })

  const getUserByIdQuery = userService.getUserByIdQuery(
    userId,
    ref([
      includes.roles,
      includes.companies,
      includes.permissions,
      includes.invoices,
    ]),
  )
  const {
    data: userByIdResponse,
    isLoading: isLoadingUserById,
    isError: isErrorUserById,
    error: errorUserById,
    refetch: refetchUserById,
  } = getUserByIdQuery

  async function fetchUserByIdData(): Promise<IResponse<IUser>> {
    try {
      const response = await refetchUserById()
      return response.data as IResponse<IUser>
    } catch (error: any) {
      errorStore.setError(error, { context: UserContext.GET_USER_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  /**
   * Get initial form values for user form
   * @param user - Optional user object for edit mode
   * @returns Initial values object for vee-validate form
   */
  function getUserFormInitialValues(user?: IUser | null) {
    // Convert role name string to EUserRole enum value, default to USER if not found
    const roleName = user?.roles?.[0]?.name
    const role =
      roleName && Object.values(EUserRole).includes(roleName as EUserRole)
        ? (roleName as EUserRole)
        : EUserRole.USER

    return {
      name: user?.name || '',
      email: user?.email || '',
      password: '',
      password_confirmation: '',
      profile_photo: null,
      role,
      status: user?.status || EUserStatus.REGISTERED,
    }
  }

  return {
    sort: base.sort,
    filter: base.filter,
    includes: base.includes,
    users: base.items,
    onSortingChange: base.onSortingChange,
    onFiltersChange: base.onFiltersChange,
    clearFilters: base.clearFilters,
    onPageChange: base.onPageChange,
    onPageSizeChange: base.onPageSizeChange,
    userPrerequisitesResponse: base.prerequisitesResponse,
    isLoadingUserPrerequisites: base.isLoadingPrerequisites,
    isErrorUserPrerequisites: base.isErrorPrerequisites,
    errorUserPrerequisites: base.errorPrerequisites,
    fetchUserPrerequisitesData: base.fetchPrerequisitesData,
    fetchUsersData: base.fetchListData,
    getUser: base.get,
    createUser: base.create,
    updateUser: base.update,
    deleteUser: base.deleteItem,
    isCreating: base.isCreating,
    isUpdating: base.isUpdating,
    isDeleting: base.isDeleting,
    loading: base.loading,
    serverPagination: base.serverPagination,
    userId,
    userByIdResponse,
    isLoadingUserById,
    isErrorUserById,
    errorUserById,
    fetchUserByIdData,
    getUserFormInitialValues,
  }
}

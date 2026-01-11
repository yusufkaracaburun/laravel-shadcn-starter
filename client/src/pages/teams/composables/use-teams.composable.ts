import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type { TPageSize } from '@/components/data-table/types'
import type {
  IAddUsersToTeamRequest,
  ICreateTeamRequest,
  ITeam,
  ITeamFilters,
  ITeamPrerequisites,
  IUpdateTeamRequest,
} from '@/pages/teams/models/teams'
import type { ISorting } from '@/services/query-utils'
import type { IResponse } from '@/services/types/response.type'

import { useResourceBase } from '@/composables/use-resource-base.composable'
import { useToast } from '@/composables/use-toast.composable'
import { useTeamService } from '@/pages/teams/services/teams.service'
import { useErrorStore } from '@/stores/error.store'

const TeamContext = {
  FETCH_PREREQUISITES: 'fetchTeamPrerequisites',
  FETCH_LIST: 'fetchTeams',
  GET_TEAM: 'getTeam',
  GET_TEAM_BY_ID: 'getTeamById',
  CREATE: 'createTeam',
  UPDATE: 'updateTeam',
  DELETE: 'deleteTeam',
  ADD_USERS_TO_TEAM: 'addUsersToTeam',
}

const TeamMessages = {
  CREATE_SUCCESS: 'Team created successfully!',
  UPDATE_SUCCESS: 'Team updated successfully!',
  DELETE_SUCCESS: 'Team deleted successfully!',
}

export function useTeams() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const teamService = useTeamService()
  const route = useRoute()

  const includes = {
    owner: 'owner',
    users: 'users',
    usersCount: 'usersCount',
    teamInvitations: 'teamInvitations',
  }

  const base = useResourceBase<
    ITeam,
    ITeamFilters,
    ICreateTeamRequest,
    IUpdateTeamRequest,
    ITeamPrerequisites
  >({
    service: {
      getPrerequisitesQuery: () => teamService.getTeamPrerequisitesQuery(),
      getListQuery: teamService.getTeamsQuery,
      createMutation: () => teamService.createTeamMutation(),
      updateMutation: () => teamService.updateTeamMutation(),
      deleteMutation: () => teamService.deleteTeamMutation(),
      getMutation: () => teamService.getTeamMutation(),
    },
    context: TeamContext,
    messages: TeamMessages,
    defaultSort: { id: 'created_at', desc: true },
    includes,
    defaultIncludeKey: 'users',
    onFetchList: (refetch) => {
      refetch()
    },
  })

  const teamId = computed(() => {
    if (!route) {
      return undefined
    }
    const params = route.params as { id?: string | string[] }
    const idParam = Array.isArray(params.id) ? params.id[0] : params.id
    if (
      !idParam ||
      typeof idParam !== 'string' ||
      Number.isNaN(Number(idParam))
    ) {
      return undefined
    }
    return Number(idParam)
  })

  const getTeamByIdQuery = teamService.getTeamByIdQuery(teamId, ref([]))
  const {
    data: teamByIdResponse,
    isLoading: isLoadingTeamById,
    isError: isErrorTeamById,
    error: errorTeamById,
    refetch: refetchTeamById,
  } = getTeamByIdQuery

  async function fetchTeamByIdData(): Promise<IResponse<ITeam>> {
    try {
      const response = await refetchTeamById()
      return response.data as IResponse<ITeam>
    } catch (error: any) {
      errorStore.setError(error, { context: TeamContext.GET_TEAM_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  /**
   * Get initial form values for team form
   * @param team - Optional team object for edit mode
   * @returns Initial values object for vee-validate form
   */
  function getTeamFormInitialValues(team?: ITeam | null) {
    return {
      name: team?.name || '',
      personal_team: team?.personal_team || false,
      user_id: team?.user_id || null,
    }
  }

  // Team-User management functions
  const addUsersToTeamMutation = teamService.addUsersToTeamMutation()
  const { isPending: isAddingUsers } = addUsersToTeamMutation

  /**
   * Add users to a team
   * @param teamId - The team ID to add users to
   * @param userIds - Array of user IDs to add
   * @returns Promise with the updated team response
   */
  async function addUsersToTeam(
    teamId: number,
    userIds: number[],
  ): Promise<IResponse<ITeam>> {
    try {
      const data: IAddUsersToTeamRequest = { user_ids: userIds }
      const response = await addUsersToTeamMutation.mutateAsync({
        teamId,
        data,
      })
      toast.showSuccess('Users added to team successfully!')
      return response
    } catch (error: any) {
      errorStore.setError(error, { context: TeamContext.ADD_USERS_TO_TEAM })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message || 'Failed to add users to team')
      throw error
    }
  }

  return {
    sort: base.sort,
    filter: base.filter,
    includes: base.includes,
    teams: base.items,
    pageSize: base.pageSize,
    onSortingChange: base.onSortingChange,
    onFiltersChange: base.onFiltersChange,
    clearFilters: base.clearFilters,
    onPageChange: base.onPageChange,
    onPageSizeChange: base.onPageSizeChange,
    teamPrerequisitesResponse: base.prerequisitesResponse,
    isLoadingTeamPrerequisites: base.isLoadingPrerequisites,
    isErrorTeamPrerequisites: base.isErrorPrerequisites,
    errorTeamPrerequisites: base.errorPrerequisites,
    fetchTeamPrerequisitesData: base.fetchPrerequisitesData,
    fetchTeamsData: base.fetchListData,
    getTeam: base.get,
    createTeam: base.create,
    updateTeam: base.update,
    deleteTeam: base.deleteItem,
    isCreating: base.isCreating,
    isUpdating: base.isUpdating,
    isDeleting: base.isDeleting,
    loading: base.loading,
    serverPagination: base.serverPagination,
    teamId,
    teamByIdResponse,
    isLoadingTeamById,
    isErrorTeamById,
    errorTeamById,
    fetchTeamByIdData,
    getTeamFormInitialValues,
    addUsersToTeam,
    isAddingUsers,
  }
}

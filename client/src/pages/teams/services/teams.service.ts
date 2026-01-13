import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'

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
import type {
  IPaginatedResponse,
  IResponse,
} from '@/services/types/response.type'

import { useAxios } from '@/composables/use-axios.composable'
import {
  buildQueryString,
  defaultAxiosQueryOptions,
} from '@/services/query-utils'

enum QueryKeys {
  TEAM_PREREQUISITES = 'teamPrerequisites',
  TEAM_LIST = 'teamList',
  GET_TEAM = 'getTeam',
  GET_TEAM_BY_ID = 'getTeamById',
  CREATE_TEAM = 'createTeam',
  UPDATE_TEAM = 'updateTeam',
  DELETE_TEAM = 'deleteTeam',
  ADD_USERS_TO_TEAM = 'addUsersToTeam',
}

const API_URL = '/api/teams'
const STALE_TIME = 5 * 60 * 1000

export function useTeamService() {
  const queryClient = useQueryClient()

  const { axiosInstance } = useAxios()

  function getTeamPrerequisitesQuery(): ReturnType<
    typeof useQuery<ITeamPrerequisites, AxiosError>
  > {
    return useQuery({
      queryKey: [QueryKeys.TEAM_PREREQUISITES],
      queryFn: async (): Promise<ITeamPrerequisites> => {
        const response = await axiosInstance.get(`${API_URL}/prerequisites`)
        return response.data as ITeamPrerequisites
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getTeamsQuery(
    page: Ref<number>,
    per_page: Ref<TPageSize>,
    sort: Ref<ISorting>,
    filter: Ref<ITeamFilters>,
    include: Ref<string[]>,
  ): ReturnType<typeof useQuery<IPaginatedResponse<ITeam>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.TEAM_LIST, page, per_page, sort, filter, include],
      queryFn: async (): Promise<IPaginatedResponse<ITeam>> => {
        const params: Record<string, any> = {
          page: page.value,
          per_page: per_page.value,
          sort: sort.value,
          filter: filter.value,
          include: include.value,
        }

        const response = await axiosInstance.get(
          `${API_URL}${buildQueryString(params)}`,
        )
        return response.data
      },
      staleTime: STALE_TIME,
      enabled: false,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getTeamByIdQuery(
    id: Ref<number | undefined>,
    includes?: Ref<string[] | undefined>,
  ): ReturnType<typeof useQuery<IResponse<ITeam>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.GET_TEAM_BY_ID, id, includes],
      queryFn: async () => {
        const response = await axiosInstance.get(`${API_URL}/${id.value}`, {
          params: { include: includes?.value?.join(',') ?? '' },
        })
        return response.data
      },
      staleTime: STALE_TIME,
      enabled: computed(() => id.value !== undefined),
      ...defaultAxiosQueryOptions(),
    })
  }

  function getTeamMutation(): ReturnType<
    typeof useMutation<
      IResponse<ITeam>,
      AxiosError,
      { id: number; includes?: string[] }
    >
  > {
    return useMutation<
      IResponse<ITeam>,
      AxiosError,
      { id: number; includes?: string[] }
    >({
      mutationKey: [QueryKeys.GET_TEAM],
      mutationFn: async ({ id, includes }): Promise<IResponse<ITeam>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
        })
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.TEAM_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_TEAM, variables.id],
        })
      },
      onError: (error) => {
        console.error('Get team error:', error)
      },
    })
  }

  function createTeamMutation(): ReturnType<
    typeof useMutation<IResponse<ITeam>, AxiosError, ICreateTeamRequest>
  > {
    return useMutation<IResponse<ITeam>, AxiosError, ICreateTeamRequest>({
      mutationKey: [QueryKeys.CREATE_TEAM],
      mutationFn: async (
        data: ICreateTeamRequest,
      ): Promise<IResponse<ITeam>> => {
        const response = await axiosInstance.post(`${API_URL}`, data)
        return response.data
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.TEAM_LIST] })
      },
      onError: (error) => {
        console.error('Create team error:', error)
      },
    })
  }

  function updateTeamMutation(): ReturnType<
    typeof useMutation<
      IResponse<ITeam>,
      AxiosError,
      { id: number; data: IUpdateTeamRequest }
    >
  > {
    return useMutation<
      IResponse<ITeam>,
      AxiosError,
      { id: number; data: IUpdateTeamRequest }
    >({
      mutationKey: [QueryKeys.UPDATE_TEAM],
      mutationFn: async ({ id, data }): Promise<IResponse<ITeam>> => {
        const response = await axiosInstance.put(`${API_URL}/${id}`, data)
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.TEAM_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_TEAM, variables.id],
        })
      },
      onError: (error) => {
        console.error('Update team error:', error)
      },
    })
  }

  function deleteTeamMutation(): ReturnType<
    typeof useMutation<void, AxiosError, number>
  > {
    return useMutation<void, AxiosError, number>({
      mutationKey: [QueryKeys.DELETE_TEAM],
      mutationFn: async (id: number): Promise<void> => {
        await axiosInstance.delete(`${API_URL}/${id}`)
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.TEAM_LIST] })
      },
      onError: (error) => {
        console.error('Delete team error:', error)
      },
    })
  }

  function addUsersToTeamMutation(): ReturnType<
    typeof useMutation<
      IResponse<ITeam>,
      AxiosError,
      { teamId: number; data: IAddUsersToTeamRequest }
    >
  > {
    return useMutation<
      IResponse<ITeam>,
      AxiosError,
      { teamId: number; data: IAddUsersToTeamRequest }
    >({
      mutationKey: [QueryKeys.ADD_USERS_TO_TEAM],
      mutationFn: async ({ teamId, data }): Promise<IResponse<ITeam>> => {
        const response = await axiosInstance.post(
          `${API_URL}/${teamId}/users`,
          data,
        )
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.TEAM_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_TEAM_BY_ID, variables.teamId],
        })
      },
      onError: (error) => {
        console.error('Add users to team error:', error)
      },
    })
  }

  return {
    getTeamPrerequisitesQuery,
    getTeamsQuery,
    getTeamByIdQuery,
    getTeamMutation,
    createTeamMutation,
    updateTeamMutation,
    deleteTeamMutation,
    addUsersToTeamMutation,
  }
}

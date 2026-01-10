import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICreateUserRequest,
  IUpdateUserRequest,
  IUser,
  IUserFilters,
  IUserPrerequisites,
} from '@/pages/users/models/users'

import { useAxios } from '@/composables/use-axios.composable'
import { convertToFormData } from '@/utils/form'

import type { ISorting } from '@/services/query-utils'
import type { IPaginatedResponse, IResponse } from '@/services/types/response.type'

import { buildQueryString, defaultAxiosQueryOptions } from '@/services/query-utils'

enum QueryKeys {
  USER_PREREQUISITES = 'userPrerequisites',
  USER_LIST = 'userList',
  GET_USER = 'getUser',
  GET_USER_BY_ID = 'getUserById',
  GET_CURRENT_USER = 'getCurrentUser',
  CREATE_USER = 'createUser',
  UPDATE_USER = 'updateUser',
  DELETE_USER = 'deleteUser',
}

const API_URL = '/api/users'
const STALE_TIME = 5 * 60 * 1000

export function useUserService() {
  const queryClient = useQueryClient()

  const { axiosInstance } = useAxios()

  function getCurrentUserQuery(): ReturnType<
    typeof useQuery<IResponse<IUser>, AxiosError>
  > {
    return useQuery({
      queryKey: [QueryKeys.GET_CURRENT_USER],
      queryFn: async (): Promise<IResponse<IUser>> => {
        const response = await axiosInstance.get(`${API_URL}/current`)
        return response.data
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getUserPrerequisitesQuery(): ReturnType<
    typeof useQuery<IUserPrerequisites, AxiosError>
  > {
    return useQuery({
      queryKey: [QueryKeys.USER_PREREQUISITES],
      queryFn: async (): Promise<IUserPrerequisites> => {
        const response = await axiosInstance.get(`${API_URL}/prerequisites`)
        return response.data as IUserPrerequisites
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getUsersQuery(
    page: Ref<number>,
    per_page: Ref<TPageSize>,
    sort: Ref<ISorting>,
    filter: Ref<IUserFilters>,
    include: Ref<string[]>,
  ): ReturnType<typeof useQuery<IPaginatedResponse<IUser>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.USER_LIST, page, per_page, sort, filter, include],
      queryFn: async (): Promise<IPaginatedResponse<IUser>> => {
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
      enabled: computed(() => page.value > 0 && per_page.value > 0),
      ...defaultAxiosQueryOptions(),
    })
  }

  function getUserByIdQuery(
    id: Ref<number | undefined>,
    includes?: Ref<string[] | undefined>,
  ): ReturnType<typeof useQuery<IResponse<IUser>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.GET_USER_BY_ID, id, includes],
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

  function getUserMutation(): ReturnType<
    typeof useMutation<
      IResponse<IUser>,
      AxiosError,
      { id: number; includes?: string[] }
    >
  > {
    return useMutation<
      IResponse<IUser>,
      AxiosError,
      { id: number; includes?: string[] }
    >({
      mutationKey: [QueryKeys.GET_USER],
      mutationFn: async ({ id, includes }): Promise<IResponse<IUser>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
        })
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.USER_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_USER, variables.id],
        })
      },
      onError: (error) => {
        console.error('Get user error:', error)
      },
    })
  }

  function createUserMutation(): ReturnType<
    typeof useMutation<IResponse<IUser>, AxiosError, ICreateUserRequest>
  > {
    return useMutation<IResponse<IUser>, AxiosError, ICreateUserRequest>({
      mutationKey: [QueryKeys.CREATE_USER],
      mutationFn: async (
        data: ICreateUserRequest,
      ): Promise<IResponse<IUser>> => {
        // Convert to FormData if file is present
        const requestData =
          data.profile_photo instanceof File
            ? convertToFormData(data, { excludeId: true })
            : data

        const response = await axiosInstance.post(`${API_URL}`, requestData)
        return response.data
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.USER_LIST] })
      },
      onError: (error) => {
        console.error('Create user error:', error)
      },
    })
  }

  function updateUserMutation(): ReturnType<
    typeof useMutation<
      IResponse<IUser>,
      AxiosError,
      { id: number; data: IUpdateUserRequest }
    >
  > {
    return useMutation<
      IResponse<IUser>,
      AxiosError,
      { id: number; data: IUpdateUserRequest }
    >({
      mutationKey: [QueryKeys.UPDATE_USER],
      mutationFn: async ({ id, data }): Promise<IResponse<IUser>> => {
        // Convert to FormData if file is present
        const requestData =
          data.profile_photo instanceof File
            ? convertToFormData(data, { excludeId: false })
            : data

        // Use POST with _method=PUT for FormData (Laravel convention)
        if (requestData instanceof FormData) {
          requestData.append('_method', 'PUT')
          const response = await axiosInstance.post(
            `${API_URL}/${id}`,
            requestData,
          )
          return response.data
        }

        // Use regular PUT for JSON data
        const response = await axiosInstance.put(`${API_URL}/${id}`, data)
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.USER_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_USER, variables.id],
        })
      },
      onError: (error) => {
        console.error('Update user error:', error)
      },
    })
  }

  function deleteUserMutation(): ReturnType<
    typeof useMutation<void, AxiosError, number>
  > {
    return useMutation<void, AxiosError, number>({
      mutationKey: [QueryKeys.DELETE_USER],
      mutationFn: async (id: number): Promise<void> => {
        await axiosInstance.delete(`${API_URL}/${id}`)
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.USER_LIST] })
      },
      onError: (error) => {
        console.error('Delete user error:', error)
      },
    })
  }

  return {
    getCurrentUserQuery,
    getUserPrerequisitesQuery,
    getUsersQuery,
    getUserByIdQuery,
    getUserMutation,
    createUserMutation,
    updateUserMutation,
    deleteUserMutation,
  }
}

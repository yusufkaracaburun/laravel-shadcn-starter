import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed, type Ref } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICreateTimesheetRequest,
  IUpdateTimesheetRequest,
  ITimesheet,
  ITimesheetFilters,
  ITimesheetPrerequisites,
} from '@/pages/timesheets/models/timesheets'

import { useAxios } from '@/composables/use-axios.composable'

import type { ISorting } from '@/services/query-utils'
import type { IPaginatedResponse, IResponse } from '@/services/types/response.type'

import { buildQueryString, defaultAxiosQueryOptions } from '@/services/query-utils'

enum QueryKeys {
  TIMESHEET_PREREQUISITES = 'timesheetPrerequisites',
  TIMESHEET_LIST = 'timesheetList',
  GET_TIMESHEET = 'getTimesheet',
  GET_TIMESHEET_BY_ID = 'getTimesheetById',
  CREATE_TIMESHEET = 'createTimesheet',
  UPDATE_TIMESHEET = 'updateTimesheet',
  DELETE_TIMESHEET = 'deleteTimesheet',
}

const API_URL = '/api/timesheets'
const STALE_TIME = 5 * 60 * 1000

export function useTimesheetsService() {
  const queryClient = useQueryClient()

  const { axiosInstance } = useAxios()

  function getTimesheetPrerequisitesQuery(): ReturnType<
    typeof useQuery<ITimesheetPrerequisites, AxiosError>
  > {
    return useQuery({
      queryKey: [QueryKeys.TIMESHEET_PREREQUISITES],
      queryFn: async (): Promise<ITimesheetPrerequisites> => {
        const response = await axiosInstance.get(`${API_URL}/prerequisites`)
        return response.data as ITimesheetPrerequisites
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getTimesheetsQuery(
    page: Ref<number>,
    per_page: Ref<TPageSize>,
    sort: Ref<ISorting>,
    filter: Ref<ITimesheetFilters>,
    include: Ref<string[]>,
  ): ReturnType<typeof useQuery<IPaginatedResponse<ITimesheet>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.TIMESHEET_LIST, page, per_page, sort, filter, include],
      queryFn: async (): Promise<IPaginatedResponse<ITimesheet>> => {
        const params: Record<string, any> = {
          page: page.value,
          per_page: per_page.value,
          sort: sort.value,
          filter: filter.value,
        }

        // Only add include if it's a valid non-empty array
        if (
          include.value
          && Array.isArray(include.value)
          && include.value.length > 0
          && include.value.every((item) => item !== undefined && item !== null && item !== '')
        ) {
          params.include = include.value.filter((item) => item && item !== '')
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

  function getTimesheetByIdQuery(
    id: Ref<number | undefined>,
    includes?: Ref<string[] | undefined>,
  ): ReturnType<typeof useQuery<IResponse<ITimesheet>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.GET_TIMESHEET_BY_ID, id, includes],
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

  function getTimesheetMutation(): ReturnType<
    typeof useMutation<
      IResponse<ITimesheet>,
      AxiosError,
      { id: number; includes?: string[] }
    >
  > {
    return useMutation<
      IResponse<ITimesheet>,
      AxiosError,
      { id: number; includes?: string[] }
    >({
      mutationKey: [QueryKeys.GET_TIMESHEET],
      mutationFn: async ({ id, includes }): Promise<IResponse<ITimesheet>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
        })
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.TIMESHEET_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_TIMESHEET, variables.id],
        })
      },
      onError: (error) => {
        console.error('Get timesheet error:', error)
      },
    })
  }

  function createTimesheetMutation(): ReturnType<
    typeof useMutation<IResponse<ITimesheet>, AxiosError, ICreateTimesheetRequest>
  > {
    return useMutation<IResponse<ITimesheet>, AxiosError, ICreateTimesheetRequest>({
      mutationKey: [QueryKeys.CREATE_TIMESHEET],
      mutationFn: async (
        data: ICreateTimesheetRequest,
      ): Promise<IResponse<ITimesheet>> => {
        const response = await axiosInstance.post(`${API_URL}`, data)
        return response.data
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.TIMESHEET_LIST] })
      },
      onError: (error) => {
        console.error('Create timesheet error:', error)
      },
    })
  }

  function updateTimesheetMutation(): ReturnType<
    typeof useMutation<
      IResponse<ITimesheet>,
      AxiosError,
      { id: number; data: IUpdateTimesheetRequest }
    >
  > {
    return useMutation<
      IResponse<ITimesheet>,
      AxiosError,
      { id: number; data: IUpdateTimesheetRequest }
    >({
      mutationKey: [QueryKeys.UPDATE_TIMESHEET],
      mutationFn: async ({ id, data }): Promise<IResponse<ITimesheet>> => {
        const response = await axiosInstance.put(`${API_URL}/${id}`, data)
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.TIMESHEET_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_TIMESHEET, variables.id],
        })
      },
      onError: (error) => {
        console.error('Update timesheet error:', error)
      },
    })
  }

  function deleteTimesheetMutation(): ReturnType<
    typeof useMutation<void, AxiosError, number>
  > {
    return useMutation<void, AxiosError, number>({
      mutationKey: [QueryKeys.DELETE_TIMESHEET],
      mutationFn: async (id: number): Promise<void> => {
        await axiosInstance.delete(`${API_URL}/${id}`)
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.TIMESHEET_LIST] })
      },
      onError: (error) => {
        console.error('Delete timesheet error:', error)
      },
    })
  }

  return {
    getTimesheetPrerequisitesQuery,
    getTimesheetsQuery,
    getTimesheetByIdQuery,
    getTimesheetMutation,
    createTimesheetMutation,
    updateTimesheetMutation,
    deleteTimesheetMutation,
  }
}

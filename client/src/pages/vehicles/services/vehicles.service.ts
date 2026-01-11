import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICreateVehicleRequest,
  IUpdateVehicleRequest,
  IVehicle,
  IVehicleFilters,
  IVehiclePrerequisites,
} from '@/pages/vehicles/models/vehicles'

import { useAxios } from '@/composables/use-axios.composable'

import type { ISorting } from '@/services/query-utils'
import type { IPaginatedResponse, IResponse } from '@/services/types/response.type'

import { buildQueryString, defaultAxiosQueryOptions } from '@/services/query-utils'

enum QueryKeys {
  VEHICLE_PREREQUISITES = 'vehiclePrerequisites',
  VEHICLE_LIST = 'vehicleList',
  GET_VEHICLE = 'getVehicle',
  GET_VEHICLE_BY_ID = 'getVehicleById',
  CREATE_VEHICLE = 'createVehicle',
  UPDATE_VEHICLE = 'updateVehicle',
  DELETE_VEHICLE = 'deleteVehicle',
}

const API_URL = '/api/vehicles'
const STALE_TIME = 5 * 60 * 1000

export function useVehicleService() {
  const queryClient = useQueryClient()

  const { axiosInstance } = useAxios()

  function getVehiclePrerequisitesQuery(): ReturnType<
    typeof useQuery<IVehiclePrerequisites, AxiosError>
  > {
    return useQuery({
      queryKey: [QueryKeys.VEHICLE_PREREQUISITES],
      queryFn: async (): Promise<IVehiclePrerequisites> => {
        const response = await axiosInstance.get(`${API_URL}/prerequisites`)
        return response.data as IVehiclePrerequisites
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getVehiclesQuery(
    page: Ref<number>,
    per_page: Ref<TPageSize>,
    sort: Ref<ISorting>,
    filter: Ref<IVehicleFilters>,
    include: Ref<string[]>,
  ): ReturnType<typeof useQuery<IPaginatedResponse<IVehicle>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.VEHICLE_LIST, page, per_page, sort, filter, include],
      queryFn: async (): Promise<IPaginatedResponse<IVehicle>> => {
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

  function getVehicleByIdQuery(
    id: Ref<number | undefined>,
    includes?: Ref<string[] | undefined>,
  ): ReturnType<typeof useQuery<IResponse<IVehicle>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.GET_VEHICLE_BY_ID, id, includes],
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

  function getVehicleMutation(): ReturnType<
    typeof useMutation<
      IResponse<IVehicle>,
      AxiosError,
      { id: number; includes?: string[] }
    >
  > {
    return useMutation<
      IResponse<IVehicle>,
      AxiosError,
      { id: number; includes?: string[] }
    >({
      mutationKey: [QueryKeys.GET_VEHICLE],
      mutationFn: async ({ id, includes }): Promise<IResponse<IVehicle>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
        })
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.VEHICLE_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_VEHICLE, variables.id],
        })
      },
      onError: (error) => {
        console.error('Get vehicle error:', error)
      },
    })
  }

  function createVehicleMutation(): ReturnType<
    typeof useMutation<IResponse<IVehicle>, AxiosError, ICreateVehicleRequest>
  > {
    return useMutation<IResponse<IVehicle>, AxiosError, ICreateVehicleRequest>({
      mutationKey: [QueryKeys.CREATE_VEHICLE],
      mutationFn: async (
        data: ICreateVehicleRequest,
      ): Promise<IResponse<IVehicle>> => {
        const response = await axiosInstance.post(`${API_URL}`, data)
        return response.data
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.VEHICLE_LIST] })
      },
      onError: (error) => {
        console.error('Create vehicle error:', error)
      },
    })
  }

  function updateVehicleMutation(): ReturnType<
    typeof useMutation<
      IResponse<IVehicle>,
      AxiosError,
      { id: number; data: IUpdateVehicleRequest }
    >
  > {
    return useMutation<
      IResponse<IVehicle>,
      AxiosError,
      { id: number; data: IUpdateVehicleRequest }
    >({
      mutationKey: [QueryKeys.UPDATE_VEHICLE],
      mutationFn: async ({ id, data }): Promise<IResponse<IVehicle>> => {
        const response = await axiosInstance.put(`${API_URL}/${id}`, data)
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.VEHICLE_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_VEHICLE, variables.id],
        })
      },
      onError: (error) => {
        console.error('Update vehicle error:', error)
      },
    })
  }

  function deleteVehicleMutation(): ReturnType<
    typeof useMutation<void, AxiosError, number>
  > {
    return useMutation<void, AxiosError, number>({
      mutationKey: [QueryKeys.DELETE_VEHICLE],
      mutationFn: async (id: number): Promise<void> => {
        await axiosInstance.delete(`${API_URL}/${id}`)
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.VEHICLE_LIST] })
      },
      onError: (error) => {
        console.error('Delete vehicle error:', error)
      },
    })
  }

  return {
    getVehiclePrerequisitesQuery,
    getVehiclesQuery,
    getVehicleByIdQuery,
    getVehicleMutation,
    createVehicleMutation,
    updateVehicleMutation,
    deleteVehicleMutation,
  }
}

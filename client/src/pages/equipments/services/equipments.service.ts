import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICreateEquipmentRequest,
  IEquipment,
  IEquipmentFilters,
  IEquipmentPrerequisites,
  IUpdateEquipmentRequest,
} from '@/pages/equipments/models/equipments'
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
  EQUIPMENT_PREREQUISITES = 'equipmentPrerequisites',
  EQUIPMENT_LIST = 'equipmentList',
  GET_EQUIPMENT = 'getEquipment',
  GET_EQUIPMENT_BY_ID = 'getEquipmentById',
  CREATE_EQUIPMENT = 'createEquipment',
  UPDATE_EQUIPMENT = 'updateEquipment',
  DELETE_EQUIPMENT = 'deleteEquipment',
}

const API_URL = '/api/equipments'
const STALE_TIME = 5 * 60 * 1000

export function useEquipmentsService() {
  const queryClient = useQueryClient()

  const { axiosInstance } = useAxios()

  function getEquipmentsPrerequisitesQuery(): ReturnType<
    typeof useQuery<IEquipmentPrerequisites, AxiosError>
  > {
    return useQuery({
      queryKey: [QueryKeys.EQUIPMENT_PREREQUISITES],
      queryFn: async (): Promise<IEquipmentPrerequisites> => {
        const response = await axiosInstance.get(`${API_URL}/prerequisites`)
        return response.data as IEquipmentPrerequisites
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getEquipmentsQuery(
    page: Ref<number>,
    per_page: Ref<TPageSize>,
    sort: Ref<ISorting>,
    filter: Ref<IEquipmentFilters>,
    include: Ref<string[]>,
  ): ReturnType<typeof useQuery<IPaginatedResponse<IEquipment>, AxiosError>> {
    return useQuery({
      queryKey: [
        QueryKeys.EQUIPMENT_LIST,
        page,
        per_page,
        sort,
        filter,
        include,
      ],
      queryFn: async (): Promise<IPaginatedResponse<IEquipment>> => {
        const params: Record<string, any> = {
          page: page.value,
          per_page: per_page.value,
          sort: sort.value,
          filter: filter.value,
        }

        if (include.value && include.value.length > 0) {
          params.include = include.value.join(',')
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

  function getEquipmentsByIdQuery(
    id: Ref<number | undefined>,
    includes?: Ref<string[] | undefined>,
  ): ReturnType<typeof useQuery<IResponse<IEquipment>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.GET_EQUIPMENT_BY_ID, id, includes],
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

  function getEquipmentsMutation(): ReturnType<
    typeof useMutation<
      IResponse<IEquipment>,
      AxiosError,
      { id: number; includes?: string[] }
    >
  > {
    return useMutation<
      IResponse<IEquipment>,
      AxiosError,
      { id: number; includes?: string[] }
    >({
      mutationKey: [QueryKeys.GET_EQUIPMENT],
      mutationFn: async ({ id, includes }): Promise<IResponse<IEquipment>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
        })
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.EQUIPMENT_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_EQUIPMENT, variables.id],
        })
      },
      onError: (error) => {
        console.error('Get equipment error:', error)
      },
    })
  }

  function createEquipmentsMutation(): ReturnType<
    typeof useMutation<
      IResponse<IEquipment>,
      AxiosError,
      ICreateEquipmentRequest
    >
  > {
    return useMutation<
      IResponse<IEquipment>,
      AxiosError,
      ICreateEquipmentRequest
    >({
      mutationKey: [QueryKeys.CREATE_EQUIPMENT],
      mutationFn: async (
        data: ICreateEquipmentRequest,
      ): Promise<IResponse<IEquipment>> => {
        const response = await axiosInstance.post(`${API_URL}`, data)
        return response.data
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.EQUIPMENT_LIST] })
      },
      onError: (error) => {
        console.error('Create equipment error:', error)
      },
    })
  }

  function updateEquipmentsMutation(): ReturnType<
    typeof useMutation<
      IResponse<IEquipment>,
      AxiosError,
      { id: number; data: IUpdateEquipmentRequest }
    >
  > {
    return useMutation<
      IResponse<IEquipment>,
      AxiosError,
      { id: number; data: IUpdateEquipmentRequest }
    >({
      mutationKey: [QueryKeys.UPDATE_EQUIPMENT],
      mutationFn: async ({ id, data }): Promise<IResponse<IEquipment>> => {
        const response = await axiosInstance.put(`${API_URL}/${id}`, data)
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.EQUIPMENT_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_EQUIPMENT, variables.id],
        })
      },
      onError: (error) => {
        console.error('Update equipment error:', error)
      },
    })
  }

  function deleteEquipmentsMutation(): ReturnType<
    typeof useMutation<void, AxiosError, number>
  > {
    return useMutation<void, AxiosError, number>({
      mutationKey: [QueryKeys.DELETE_EQUIPMENT],
      mutationFn: async (id: number): Promise<void> => {
        await axiosInstance.delete(`${API_URL}/${id}`)
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.EQUIPMENT_LIST] })
      },
      onError: (error) => {
        console.error('Delete equipment error:', error)
      },
    })
  }

  return {
    getEquipmentsPrerequisitesQuery,
    getEquipmentsQuery,
    getEquipmentsByIdQuery,
    getEquipmentsMutation,
    createEquipmentsMutation,
    updateEquipmentsMutation,
    deleteEquipmentsMutation,
  }
}

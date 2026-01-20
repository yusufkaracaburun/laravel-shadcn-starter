import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICustomer,
  ICustomerFilters,
  ICustomerPrerequisites,
  ICreateCustomerRequest,
  IUpdateCustomerRequest,
} from '@/pages/customers/models/customers'
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
  CUSTOMER_PREREQUISITES = 'customerPrerequisites',
  CUSTOMER_LIST = 'customerList',
  GET_CUSTOMER = 'getCustomer',
  GET_CUSTOMER_BY_ID = 'getCustomerById',
  CREATE_CUSTOMER = 'createCustomer',
  UPDATE_CUSTOMER = 'updateCustomer',
  DELETE_CUSTOMER = 'deleteCustomer',
}

const API_URL = '/api/customers'
const STALE_TIME = 5 * 60 * 1000

export function useCustomersService() {
  const queryClient = useQueryClient()

  const { axiosInstance } = useAxios()

  function getCustomersPrerequisitesQuery(): ReturnType<
    typeof useQuery<ICustomerPrerequisites, AxiosError>
  > {
    return useQuery({
      queryKey: [QueryKeys.CUSTOMER_PREREQUISITES],
      queryFn: async (): Promise<ICustomerPrerequisites> => {
        // Return empty object if no prerequisites endpoint exists
        // Uncomment and update if backend provides prerequisites endpoint:
        // const response = await axiosInstance.get(`${API_URL}/prerequisites`)
        // return response.data as ICustomerPrerequisites
        return {} as ICustomerPrerequisites
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getCustomersQuery(
    page: Ref<number>,
    per_page: Ref<TPageSize>,
    sort: Ref<ISorting>,
    filter: Ref<ICustomerFilters>,
    include: Ref<string[]>,
  ): ReturnType<typeof useQuery<IPaginatedResponse<ICustomer>, AxiosError>> {
    return useQuery({
      queryKey: [
        QueryKeys.CUSTOMER_LIST,
        page,
        per_page,
        sort,
        filter,
        include,
      ],
      queryFn: async (): Promise<IPaginatedResponse<ICustomer>> => {
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

  function getCustomersByIdQuery(
    id: Ref<number | undefined>,
    includes?: Ref<string[] | undefined>,
  ): ReturnType<typeof useQuery<IResponse<ICustomer>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.GET_CUSTOMER_BY_ID, id, includes],
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

  function getCustomersMutation(): ReturnType<
    typeof useMutation<
      IResponse<ICustomer>,
      AxiosError,
      { id: number; includes?: string[] }
    >
  > {
    return useMutation<
      IResponse<ICustomer>,
      AxiosError,
      { id: number; includes?: string[] }
    >({
      mutationKey: [QueryKeys.GET_CUSTOMER],
      mutationFn: async ({ id, includes }): Promise<IResponse<ICustomer>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
        })
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.CUSTOMER_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_CUSTOMER, variables.id],
        })
      },
      onError: (error) => {
        console.error('Get customer error:', error)
      },
    })
  }

  function createCustomersMutation(): ReturnType<
    typeof useMutation<
      IResponse<ICustomer>,
      AxiosError,
      ICreateCustomerRequest
    >
  > {
    return useMutation<
      IResponse<ICustomer>,
      AxiosError,
      ICreateCustomerRequest
    >({
      mutationKey: [QueryKeys.CREATE_CUSTOMER],
      mutationFn: async (
        data: ICreateCustomerRequest,
      ): Promise<IResponse<ICustomer>> => {
        const response = await axiosInstance.post(`${API_URL}`, data)
        return response.data
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.CUSTOMER_LIST] })
      },
      onError: (error) => {
        console.error('Create customer error:', error)
      },
    })
  }

  function updateCustomersMutation(): ReturnType<
    typeof useMutation<
      IResponse<ICustomer>,
      AxiosError,
      { id: number; data: IUpdateCustomerRequest }
    >
  > {
    return useMutation<
      IResponse<ICustomer>,
      AxiosError,
      { id: number; data: IUpdateCustomerRequest }
    >({
      mutationKey: [QueryKeys.UPDATE_CUSTOMER],
      mutationFn: async ({ id, data }): Promise<IResponse<ICustomer>> => {
        const response = await axiosInstance.put(`${API_URL}/${id}`, data)
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.CUSTOMER_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_CUSTOMER, variables.id],
        })
      },
      onError: (error) => {
        console.error('Update customer error:', error)
      },
    })
  }

  function deleteCustomersMutation(): ReturnType<
    typeof useMutation<void, AxiosError, number>
  > {
    return useMutation<void, AxiosError, number>({
      mutationKey: [QueryKeys.DELETE_CUSTOMER],
      mutationFn: async (id: number): Promise<void> => {
        await axiosInstance.delete(`${API_URL}/${id}`)
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.CUSTOMER_LIST] })
      },
      onError: (error) => {
        console.error('Delete customer error:', error)
      },
    })
  }

  return {
    getCustomersPrerequisitesQuery,
    getCustomersQuery,
    getCustomersByIdQuery,
    getCustomersMutation,
    createCustomersMutation,
    updateCustomersMutation,
    deleteCustomersMutation,
  }
}

// Re-export types from models for convenience
export type {
  ICustomer,
  IContact,
  ICustomerFilters,
  ECustomerType,
  TCustomerType,
} from '@/pages/customers/models/customers'

// Legacy exports for backward compatibility (deprecated - use models instead)
/** @deprecated Use ICustomer from @/pages/customers/models/customers instead */
export type Customer = import('@/pages/customers/models/customers').ICustomer
/** @deprecated Use IContact from @/pages/customers/models/customers instead */
export type Contact = import('@/pages/customers/models/customers').IContact
/** @deprecated Use ICustomerFilters from @/pages/customers/models/customers instead */
export type CustomerFilters = import('@/pages/customers/models/customers').ICustomerFilters
/** @deprecated Use TCustomerType from @/pages/customers/models/customers instead */
export type CustomerType = import('@/pages/customers/models/customers').TCustomerType

// Re-export request types for backward compatibility
export type {
  ICreateCustomerRequest as CreateCustomerRequest,
  IUpdateCustomerRequest as UpdateCustomerRequest,
} from '@/pages/customers/models/customers'

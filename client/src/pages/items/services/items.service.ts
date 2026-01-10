import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICreateItemRequest,
  IItem,
  IItemFilters,
  IUpdateItemRequest,
} from '@/pages/items/models/items'

import { useAxios } from '@/composables/use-axios.composable'

import type { ISorting } from '@/services/query-utils'
import type { IPaginatedResponse, IResponse } from '@/services/types/response.type'

import { buildQueryString, defaultAxiosQueryOptions } from '@/services/query-utils'

enum QueryKeys {
  ITEM_LIST = 'itemList',
  GET_ITEM = 'getItem',
  GET_ITEM_BY_ID = 'getItemById',
  CREATE_ITEM = 'createItem',
  UPDATE_ITEM = 'updateItem',
  DELETE_ITEM = 'deleteItem',
}

const API_URL = '/api/items'
const STALE_TIME = 5 * 60 * 1000

export function useItemService() {
  const queryClient = useQueryClient()

  const { axiosInstance } = useAxios()

  function getItemsQuery(
    page: Ref<number>,
    per_page: Ref<TPageSize>,
    sort: Ref<ISorting>,
    filter: Ref<IItemFilters>,
    include: Ref<string[]>,
  ): ReturnType<typeof useQuery<IPaginatedResponse<IItem>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.ITEM_LIST, page, per_page, sort, filter, include],
      queryFn: async (): Promise<IPaginatedResponse<IItem>> => {
        const params: Record<string, any> = {
          page: page.value,
          per_page: per_page.value,
          sort: sort.value,
          filter: filter.value,
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

  function getItemByIdQuery(
    id: Ref<number | undefined>,
    includes?: Ref<string[] | undefined>,
  ): ReturnType<typeof useQuery<IResponse<IItem>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.GET_ITEM_BY_ID, id, includes],
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

  function getItemMutation(): ReturnType<
    typeof useMutation<
      IResponse<IItem>,
      AxiosError,
      { id: number; includes?: string[] }
    >
  > {
    return useMutation<
      IResponse<IItem>,
      AxiosError,
      { id: number; includes?: string[] }
    >({
      mutationKey: [QueryKeys.GET_ITEM],
      mutationFn: async ({ id, includes }): Promise<IResponse<IItem>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
        })
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.ITEM_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_ITEM, variables.id],
        })
      },
      onError: (error) => {
        console.error('Get item error:', error)
      },
    })
  }

  function createItemMutation(): ReturnType<
    typeof useMutation<IResponse<IItem>, AxiosError, ICreateItemRequest>
  > {
    return useMutation<IResponse<IItem>, AxiosError, ICreateItemRequest>({
      mutationKey: [QueryKeys.CREATE_ITEM],
      mutationFn: async (
        data: ICreateItemRequest,
      ): Promise<IResponse<IItem>> => {
        const response = await axiosInstance.post(`${API_URL}`, data)
        return response.data
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.ITEM_LIST] })
      },
      onError: (error) => {
        console.error('Create item error:', error)
      },
    })
  }

  function updateItemMutation(): ReturnType<
    typeof useMutation<
      IResponse<IItem>,
      AxiosError,
      { id: number; data: IUpdateItemRequest }
    >
  > {
    return useMutation<
      IResponse<IItem>,
      AxiosError,
      { id: number; data: IUpdateItemRequest }
    >({
      mutationKey: [QueryKeys.UPDATE_ITEM],
      mutationFn: async ({ id, data }): Promise<IResponse<IItem>> => {
        const response = await axiosInstance.put(`${API_URL}/${id}`, data)
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.ITEM_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_ITEM, variables.id],
        })
      },
      onError: (error) => {
        console.error('Update item error:', error)
      },
    })
  }

  function deleteItemMutation(): ReturnType<
    typeof useMutation<void, AxiosError, number>
  > {
    return useMutation<void, AxiosError, number>({
      mutationKey: [QueryKeys.DELETE_ITEM],
      mutationFn: async (id: number): Promise<void> => {
        await axiosInstance.delete(`${API_URL}/${id}`)
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.ITEM_LIST] })
      },
      onError: (error) => {
        console.error('Delete item error:', error)
      },
    })
  }

  return {
    getItemsQuery,
    getItemByIdQuery,
    getItemMutation,
    createItemMutation,
    updateItemMutation,
    deleteItemMutation,
  }
}

// Export types for backward compatibility with invoice models
export type { IItem as Item, IMoney as Money } from '../models/items'

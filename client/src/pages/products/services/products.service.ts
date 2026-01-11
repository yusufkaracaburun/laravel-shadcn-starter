import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICreateProductRequest,
  IProduct,
  IProductFilters,
  IUpdateProductRequest,
} from '@/pages/products/models/products'
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
  PRODUCT_LIST = 'productList',
  GET_PRODUCT = 'getProduct',
  GET_PRODUCT_BY_ID = 'getProductById',
  CREATE_PRODUCT = 'createProduct',
  UPDATE_PRODUCT = 'updateProduct',
  DELETE_PRODUCT = 'deleteProduct',
}

const API_URL = '/api/products'
const STALE_TIME = 5 * 60 * 1000

export function useProductService() {
  const queryClient = useQueryClient()

  const { axiosInstance } = useAxios()

  function getProductsQuery(
    page: Ref<number>,
    per_page: Ref<TPageSize>,
    sort: Ref<ISorting>,
    filter: Ref<IProductFilters>,
    include: Ref<string[]>,
  ): ReturnType<typeof useQuery<IPaginatedResponse<IProduct>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.PRODUCT_LIST, page, per_page, sort, filter, include],
      queryFn: async (): Promise<IPaginatedResponse<IProduct>> => {
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

  function getProductByIdQuery(
    id: Ref<number | undefined>,
    includes?: Ref<string[] | undefined>,
  ): ReturnType<typeof useQuery<IResponse<IProduct>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.GET_PRODUCT_BY_ID, id, includes],
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

  function getProductMutation(): ReturnType<
    typeof useMutation<
      IResponse<IProduct>,
      AxiosError,
      { id: number; includes?: string[] }
    >
  > {
    return useMutation<
      IResponse<IProduct>,
      AxiosError,
      { id: number; includes?: string[] }
    >({
      mutationKey: [QueryKeys.GET_PRODUCT],
      mutationFn: async ({ id, includes }): Promise<IResponse<IProduct>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
        })
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.PRODUCT_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_PRODUCT, variables.id],
        })
      },
      onError: (error) => {
        console.error('Get product error:', error)
      },
    })
  }

  function createProductMutation(): ReturnType<
    typeof useMutation<IResponse<IProduct>, AxiosError, ICreateProductRequest>
  > {
    return useMutation<IResponse<IProduct>, AxiosError, ICreateProductRequest>({
      mutationKey: [QueryKeys.CREATE_PRODUCT],
      mutationFn: async (
        data: ICreateProductRequest,
      ): Promise<IResponse<IProduct>> => {
        const response = await axiosInstance.post(`${API_URL}`, data)
        return response.data
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.PRODUCT_LIST] })
      },
      onError: (error) => {
        console.error('Create product error:', error)
      },
    })
  }

  function updateProductMutation(): ReturnType<
    typeof useMutation<
      IResponse<IProduct>,
      AxiosError,
      { id: number; data: IUpdateProductRequest }
    >
  > {
    return useMutation<
      IResponse<IProduct>,
      AxiosError,
      { id: number; data: IUpdateProductRequest }
    >({
      mutationKey: [QueryKeys.UPDATE_PRODUCT],
      mutationFn: async ({ id, data }): Promise<IResponse<IProduct>> => {
        const response = await axiosInstance.put(`${API_URL}/${id}`, data)
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.PRODUCT_LIST] })
        queryClient.invalidateQueries({
          queryKey: [QueryKeys.GET_PRODUCT, variables.id],
        })
      },
      onError: (error) => {
        console.error('Update product error:', error)
      },
    })
  }

  function deleteProductMutation(): ReturnType<
    typeof useMutation<void, AxiosError, number>
  > {
    return useMutation<void, AxiosError, number>({
      mutationKey: [QueryKeys.DELETE_PRODUCT],
      mutationFn: async (id: number): Promise<void> => {
        await axiosInstance.delete(`${API_URL}/${id}`)
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.PRODUCT_LIST] })
      },
      onError: (error) => {
        console.error('Delete product error:', error)
      },
    })
  }

  return {
    getProductsQuery,
    getProductByIdQuery,
    getProductMutation,
    createProductMutation,
    updateProductMutation,
    deleteProductMutation,
  }
}

// Export types for backward compatibility with invoice models
export type { IProduct as Item, IMoney as Money } from '../models/products'

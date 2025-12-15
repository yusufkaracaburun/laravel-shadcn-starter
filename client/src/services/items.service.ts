import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { IResponse } from './types/response.type'

/**
 * Money object structure from Money library
 */
export interface Money {
  amount: string
  currency: string
  formatted: string
}

/**
 * Item interface matching backend ItemResource exactly
 * @see api/app/Http/Resources/ItemResource.php
 * Note: unit_price is a Money object from the Money library
 */
export interface Item {
  id: number
  name: string
  description: string | null
  unit_price: Money | number // Can be Money object or number (for backward compatibility)
  vat_rate: number
  unit: string | null
  created_at: string
  updated_at: string
  [key: string]: unknown
}

/**
 * Paginated items response interface matching Laravel's pagination JSON structure
 * @see https://laravel.com/docs/12.x/pagination#converting-results-to-json
 * @see api/app/Http/Controllers/Api/ItemController.php::index()
 */
export interface PaginatedItemsResponse {
  data: Item[]
  current_page: number
  per_page: number
  total: number
  last_page: number
  first_page_url: string
  last_page_url: string
  next_page_url: string | null
  prev_page_url: string | null
  path: string
  from: number | null
  to: number | null
}

/**
 * Convert TanStack Table sorting format to Spatie QueryBuilder format
 * @param sorting - Array of sorting objects from TanStack Table
 * @returns Sort string for Spatie QueryBuilder (e.g., "name" or "-name" or "name,-unit_price")
 */
function convertSortingToQueryString(
  sorting: Array<{ id: string, desc: boolean }>,
): string | undefined {
  if (!sorting || sorting.length === 0) {
    return undefined
  }

  return sorting
    .map((sort) => {
      const prefix = sort.desc ? '-' : ''
      return `${prefix}${sort.id}`
    })
    .join(',')
}

/**
 * Item filters interface matching backend filter structure
 */
export interface ItemFilters {
  name?: string
  description?: string
  unit?: string
  unit_price?: number
  vat_rate?: number
  created_at?: string
  updated_at?: string
  search?: string
  between?: string // Format: "YYYY-MM-DD,YYYY-MM-DD"
}

/**
 * List all items with pagination, sorting, and filtering
 * @see api/app/Http/Controllers/Api/ItemController.php::index()
 * @param page - Page number (default: 1) - can be a ref or number
 * @param pageSize - Number of items per page (default: 15) - can be a ref or number
 * @param sorting - Sorting state from TanStack Table (default: []) - can be a ref or array
 * @param filters - Filter object (default: {}) - can be a ref or object
 */
export function useGetItemsQuery(
  page: MaybeRef<number> = 1,
  pageSize: MaybeRef<number> = 10,
  sorting: MaybeRef<Array<{ id: string, desc: boolean }>> = [],
  filters: MaybeRef<ItemFilters> = {},
) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameters to refs for proper reactivity
  const pageRef = isRef(page) ? page : ref(page)
  const pageSizeRef = isRef(pageSize) ? pageSize : ref(pageSize)
  const sortingRef = isRef(sorting) ? sorting : ref(sorting)
  const filtersRef = isRef(filters) ? filters : ref(filters)

  return useQuery<IResponse<PaginatedItemsResponse>, AxiosError>({
    queryKey: [
      'itemList',
      computed(() => toValue(pageRef)),
      computed(() => toValue(pageSizeRef)),
      computed(() => JSON.stringify(toValue(sortingRef))),
      computed(() => JSON.stringify(toValue(filtersRef))),
    ],
    queryFn: async (): Promise<IResponse<PaginatedItemsResponse>> => {
      // Use toValue() to read current values in queryFn
      const currentPage = toValue(pageRef)
      const currentPageSize = toValue(pageSizeRef)
      const currentSorting = toValue(sortingRef)
      const currentFilters = toValue(filtersRef)
      const sortParam = convertSortingToQueryString(currentSorting)

      const params: Record<string, any> = {
        page: currentPage,
        per_page: currentPageSize,
      }

      // Add sort parameter if sorting is provided
      if (sortParam) {
        params.sort = sortParam
      }

      // Add filter parameters if filters are provided
      if (currentFilters && Object.keys(currentFilters).length > 0) {
        const filterParams: Record<string, any> = {}

        if (currentFilters.name)
          filterParams.name = currentFilters.name
        if (currentFilters.description)
          filterParams.description = currentFilters.description
        if (currentFilters.unit)
          filterParams.unit = currentFilters.unit
        if (currentFilters.unit_price !== undefined)
          filterParams.unit_price = currentFilters.unit_price
        if (currentFilters.vat_rate !== undefined)
          filterParams.vat_rate = currentFilters.vat_rate
        if (currentFilters.created_at)
          filterParams.created_at = currentFilters.created_at
        if (currentFilters.updated_at)
          filterParams.updated_at = currentFilters.updated_at
        if (currentFilters.search)
          filterParams.search = currentFilters.search
        if (currentFilters.between)
          filterParams.between = currentFilters.between

        if (Object.keys(filterParams).length > 0) {
          params.filter = filterParams
        }
      }

      const response = await axiosInstance.get('/api/items', { params })
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      // Don't retry on 401 (unauthorized) - user is not authenticated
      if (error.response?.status === 401) {
        return false
      }
      // Retry up to 2 times for other errors
      return failureCount < 2
    },
  })
}

/**
 * Get a specific item by ID
 * @see api/app/Http/Controllers/Api/ItemController.php::show()
 */
export function useGetItemQuery(itemId: MaybeRef<number>) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameter to ref for proper reactivity
  const itemIdRef = isRef(itemId) ? itemId : ref(itemId)

  return useQuery<IResponse<Item>, AxiosError>({
    queryKey: ['item', computed(() => toValue(itemIdRef))],
    queryFn: async (): Promise<IResponse<Item>> => {
      const currentItemId = toValue(itemIdRef)
      const response = await axiosInstance.get(`/api/items/${currentItemId}`)
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      // Don't retry on 401 (unauthorized) - user is not authenticated
      if (error.response?.status === 401) {
        return false
      }
      // Don't retry on 404 (not found) - item doesn't exist
      if (error.response?.status === 404) {
        return false
      }
      // Retry up to 2 times for other errors
      return failureCount < 2
    },
    staleTime: 5 * 60 * 1000, // Consider data fresh for 5 minutes
    enabled: computed(() => toValue(itemIdRef) > 0), // Only fetch if itemId is valid
  })
}

/**
 * Create item request interface matching backend validation
 * @see api/app/Http/Requests/Items/StoreItemRequest.php
 */
export interface CreateItemRequest {
  name: string
  description?: string | null
  unit_price: number
  vat_rate: number
  unit?: string | null
}

/**
 * Update item request interface matching backend validation
 * @see api/app/Http/Requests/Items/UpdateItemRequest.php
 */
export interface UpdateItemRequest {
  name?: string
  description?: string | null
  unit_price?: number
  vat_rate?: number
  unit?: string | null
}

/**
 * Create a new item
 * @see api/app/Http/Controllers/Api/ItemController.php::store()
 */
export function useCreateItemMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<Item>, AxiosError, CreateItemRequest>({
    mutationFn: async (data: CreateItemRequest): Promise<IResponse<Item>> => {
      const response = await axiosInstance.post('/api/items', data)
      return response.data
    },
    onSuccess: () => {
      // Invalidate item list query to refresh the items list
      queryClient.invalidateQueries({ queryKey: ['itemList'] })
    },
  })
}

/**
 * Update an existing item
 * @see api/app/Http/Controllers/Api/ItemController.php::update()
 */
export function useUpdateItemMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<Item>, AxiosError, { itemId: number, data: UpdateItemRequest }>({
    mutationFn: async ({ itemId, data }): Promise<IResponse<Item>> => {
      const response = await axiosInstance.put(`/api/items/${itemId}`, data)
      return response.data
    },
    onSuccess: (_, variables) => {
      // Invalidate item list query to refresh the items list
      queryClient.invalidateQueries({ queryKey: ['itemList'] })
      // Invalidate the specific item query to refresh the detail page
      queryClient.invalidateQueries({ queryKey: ['item', variables.itemId] })
    },
  })
}

/**
 * Delete an item
 * @see api/app/Http/Controllers/Api/ItemController.php::destroy()
 */
export function useDeleteItemMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<void, AxiosError, number>({
    mutationFn: async (itemId: number): Promise<void> => {
      await axiosInstance.delete(`/api/items/${itemId}`)
    },
    onSuccess: () => {
      // Invalidate item list query to refresh the items list
      queryClient.invalidateQueries({ queryKey: ['itemList'] })
    },
  })
}

import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios.composable'

import type { IResponse } from './types/response.type'

/**
 * Company interface matching backend CompanyResource exactly
 * @see api/app/Http/Resources/CompanyResource.php
 */
export interface Company {
  id: number
  name: string
  email: string
  phone: string | null
  industry: string
  status: string
  employees: string
  team_id: number | null
  created_at: string
  updated_at: string
  [key: string]: unknown
}

/**
 * Paginated companies response interface matching Laravel's pagination JSON structure
 * @see https://laravel.com/docs/12.x/pagination#converting-results-to-json
 * @see api/app/Http/Controllers/Api/CompanyController.php::index()
 */
export interface PaginatedCompaniesResponse {
  data: Company[]
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
 * @returns Sort string for Spatie QueryBuilder (e.g., "name" or "-name" or "name,-email")
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
 * List all companies with pagination and sorting
 * @see api/app/Http/Controllers/Api/CompanyController.php::index()
 * @param page - Page number (default: 1) - can be a ref or number
 * @param pageSize - Number of items per page (default: 15) - can be a ref or number
 * @param sorting - Sorting state from TanStack Table (default: []) - can be a ref or array
 */
export function useGetCompaniesQuery(
  page: MaybeRef<number> = 1,
  pageSize: MaybeRef<number> = 10,
  sorting: MaybeRef<Array<{ id: string, desc: boolean }>> = [],
) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameters to refs for proper reactivity
  const pageRef = isRef(page) ? page : ref(page)
  const pageSizeRef = isRef(pageSize) ? pageSize : ref(pageSize)
  const sortingRef = isRef(sorting) ? sorting : ref(sorting)

  return useQuery<IResponse<PaginatedCompaniesResponse>, AxiosError>({
    queryKey: [
      'companyList',
      computed(() => toValue(pageRef)),
      computed(() => toValue(pageSizeRef)),
      computed(() => JSON.stringify(toValue(sortingRef))),
    ],
    queryFn: async (): Promise<IResponse<PaginatedCompaniesResponse>> => {
      // Use toValue() to read current values in queryFn
      const currentPage = toValue(pageRef)
      const currentPageSize = toValue(pageSizeRef)
      const currentSorting = toValue(sortingRef)
      const sortParam = convertSortingToQueryString(currentSorting)

      const params: Record<string, any> = {
        page: currentPage,
        per_page: currentPageSize,
      }

      // Add sort parameter if sorting is provided
      if (sortParam) {
        params.sort = sortParam
      }

      const response = await axiosInstance.get('/api/company', { params })
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
 * Get a specific company by ID
 * @see api/app/Http/Controllers/Api/CompanyController.php::show()
 */
export function useGetCompanyQuery(companyId: MaybeRef<number>) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameter to ref for proper reactivity
  const companyIdRef = isRef(companyId) ? companyId : ref(companyId)

  return useQuery<IResponse<Company>, AxiosError>({
    queryKey: ['company', computed(() => toValue(companyIdRef))],
    queryFn: async (): Promise<IResponse<Company>> => {
      const currentCompanyId = toValue(companyIdRef)
      const response = await axiosInstance.get(
        `/api/company/${currentCompanyId}`,
      )
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      // Don't retry on 401 (unauthorized) - user is not authenticated
      if (error.response?.status === 401) {
        return false
      }
      // Don't retry on 404 (not found) - company doesn't exist
      if (error.response?.status === 404) {
        return false
      }
      // Retry up to 2 times for other errors
      return failureCount < 2
    },
    staleTime: 5 * 60 * 1000, // Consider data fresh for 5 minutes
    enabled: computed(() => toValue(companyIdRef) > 0), // Only fetch if companyId is valid
  })
}

/**
 * Create company request interface matching backend validation
 * @see api/app/Http/Controllers/Api/CompanyController.php::store()
 */
export interface CreateCompanyRequest {
  name: string
  email: string
  phone?: string | null
  industry: string
  status: string
  employees: string
}

/**
 * Update company request interface matching backend validation
 * @see api/app/Http/Controllers/Api/CompanyController.php::update()
 */
export interface UpdateCompanyRequest {
  name?: string
  email?: string
  phone?: string | null
  industry?: string
  status?: string
  employees?: string
}

/**
 * Create a new company
 * @see api/app/Http/Controllers/Api/CompanyController.php::store()
 */
export function useCreateCompanyMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<Company>, AxiosError, CreateCompanyRequest>({
    mutationFn: async (
      data: CreateCompanyRequest,
    ): Promise<IResponse<Company>> => {
      const response = await axiosInstance.post('/api/company', data)
      return response.data
    },
    onSuccess: () => {
      // Invalidate company list query to refresh the companies list
      queryClient.invalidateQueries({ queryKey: ['companyList'] })
    },
  })
}

/**
 * Update an existing company
 * @see api/app/Http/Controllers/Api/CompanyController.php::update()
 */
export function useUpdateCompanyMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<
    IResponse<Company>,
    AxiosError,
    { companyId: number, data: UpdateCompanyRequest }
  >({
    mutationFn: async ({ companyId, data }): Promise<IResponse<Company>> => {
      const response = await axiosInstance.put(
        `/api/company/${companyId}`,
        data,
      )
      return response.data
    },
    onSuccess: (_, variables) => {
      // Invalidate company list query to refresh the companies list
      queryClient.invalidateQueries({ queryKey: ['companyList'] })
      // Invalidate the specific company query to refresh the detail page
      queryClient.invalidateQueries({
        queryKey: ['company', variables.companyId],
      })
    },
  })
}

/**
 * Delete a company
 * @see api/app/Http/Controllers/Api/CompanyController.php::destroy()
 */
export function useDeleteCompanyMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<void, AxiosError, number>({
    mutationFn: async (companyId: number): Promise<void> => {
      await axiosInstance.delete(`/api/company/${companyId}`)
    },
    onSuccess: () => {
      // Invalidate company list query to refresh the companies list
      queryClient.invalidateQueries({ queryKey: ['companyList'] })
    },
  })
}

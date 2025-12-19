import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { IResponse } from './types/response.type'
import type { User } from './users.service'

/**
 * Contact interface matching backend ContactResource exactly
 * @see api/app/Http/Resources/ContactResource.php
 * Note: User is always loaded on Contact (via $with)
 */
export interface Contact {
  id: number
  name: string // full_name accessor
  first_name: string
  last_name: string
  email: string
  phone: string | null
  address: string | null
  zipcode: string | null
  city: string | null
  country: string | null
  created_at: string
  updated_at: string
  user?: User | null // Always loaded on Contact
  [key: string]: unknown
}

/**
 * Customer type enum matching backend CustomerType
 * @see api/app/Enums/CustomerType.php
 */
export type CustomerType = 'business' | 'private'

/**
 * Customer interface matching backend CustomerResource exactly
 * @see api/app/Http/Resources/CustomerResource.php
 * Note: primary_contact is always loaded (via $with)
 */
export interface Customer {
  id: number
  type: CustomerType
  name: string

  // Address fields
  address: string | null
  formatted_address: string[] // accessor - array format
  zipcode: string | null
  city: string | null
  country: string | null

  // Contact / business info
  email: string | null
  phone: string | null
  kvk_number: string | null
  vat_number: string | null
  iban_number: string | null

  // Timestamps
  created_at: string // Format: "d-m-Y H:i:s"
  updated_at: string // Format: "d-m-Y H:i:s"

  // Primary contact (always loaded)
  primary_contact: Contact | null

  // Collections (when loaded)
  contacts?: Contact[]
  invoices?: unknown[] // Invoice type can be defined later if needed

  // Counts (when counted)
  contacts_count?: number
  invoices_count?: number

  [key: string]: unknown
}

/**
 * Paginated customers response interface matching Laravel's pagination JSON structure
 * @see https://laravel.com/docs/12.x/pagination#converting-results-to-json
 * @see api/app/Http/Controllers/Api/CustomerController.php::index()
 */
export interface PaginatedCustomersResponse {
  data: Customer[]
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
 * Customer filters interface matching backend filter structure
 * @see api/app/Http/Controllers/Api/CustomerController.php::index()
 */
export interface CustomerFilters {
  id?: number
  type?: CustomerType
  name?: string
  email?: string
  phone?: string
  city?: string
  country?: string
  kvk_number?: string
  vat_number?: string
  iban_number?: string
  created_at?: string
  between?: string // Format: "YYYY-MM-DD,YYYY-MM-DD"
  search?: string
}

/**
 * List all customers with pagination, sorting, and filtering
 * @see api/app/Http/Controllers/Api/CustomerController.php::index()
 * @param page - Page number (default: 1) - can be a ref or number
 * @param pageSize - Number of items per page (default: 15) - can be a ref or number
 * @param sorting - Sorting state from TanStack Table (default: []) - can be a ref or array
 * @param filters - Filter object (default: {}) - can be a ref or object
 * @param include - Relationships to include (default: []) - can be a ref or array
 */
export function useGetCustomersQuery(
  page: MaybeRef<number> = 1,
  pageSize: MaybeRef<number> = 15,
  sorting: MaybeRef<Array<{ id: string, desc: boolean }>> = [],
  filters: MaybeRef<CustomerFilters> = {},
  include: MaybeRef<string[]> = [],
) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameters to refs for proper reactivity
  const pageRef = isRef(page) ? page : ref(page)
  const pageSizeRef = isRef(pageSize) ? pageSize : ref(pageSize)
  const sortingRef = isRef(sorting) ? sorting : ref(sorting)
  const filtersRef = isRef(filters) ? filters : ref(filters)
  const includeRef = isRef(include) ? include : ref(include)

  return useQuery<IResponse<PaginatedCustomersResponse>, AxiosError>({
    queryKey: [
      'customerList',
      computed(() => toValue(pageRef)),
      computed(() => toValue(pageSizeRef)),
      computed(() => JSON.stringify(toValue(sortingRef))),
      computed(() => JSON.stringify(toValue(filtersRef))),
      computed(() => JSON.stringify(toValue(includeRef))),
    ],
    queryFn: async (): Promise<IResponse<PaginatedCustomersResponse>> => {
      // Use toValue() to read current values in queryFn
      const currentPage = toValue(pageRef)
      const currentPageSize = toValue(pageSizeRef)
      const currentSorting = toValue(sortingRef)
      const currentFilters = toValue(filtersRef)
      const currentInclude = toValue(includeRef)
      const sortParam = convertSortingToQueryString(currentSorting)

      const params: Record<string, any> = {
        page: currentPage,
        per_page: currentPageSize,
      }

      // Add sort parameter if sorting is provided
      if (sortParam) {
        params.sort = sortParam
      }

      // Add include parameter if relationships are requested
      if (currentInclude && currentInclude.length > 0) {
        params.include = currentInclude.join(',')
      }

      // Add filter parameters if filters are provided
      if (currentFilters && Object.keys(currentFilters).length > 0) {
        const filterParams: Record<string, any> = {}

        if (currentFilters.id !== undefined)
          filterParams.id = currentFilters.id
        if (currentFilters.type)
          filterParams.type = currentFilters.type
        if (currentFilters.name)
          filterParams.name = currentFilters.name
        if (currentFilters.email)
          filterParams.email = currentFilters.email
        if (currentFilters.phone)
          filterParams.phone = currentFilters.phone
        if (currentFilters.city)
          filterParams.city = currentFilters.city
        if (currentFilters.country)
          filterParams.country = currentFilters.country
        if (currentFilters.kvk_number)
          filterParams.kvk_number = currentFilters.kvk_number
        if (currentFilters.vat_number)
          filterParams.vat_number = currentFilters.vat_number
        if (currentFilters.iban_number)
          filterParams.iban_number = currentFilters.iban_number
        if (currentFilters.created_at)
          filterParams.created_at = currentFilters.created_at
        if (currentFilters.between)
          filterParams.between = currentFilters.between
        if (currentFilters.search)
          filterParams.search = currentFilters.search

        if (Object.keys(filterParams).length > 0) {
          params.filter = filterParams
        }
      }

      const response = await axiosInstance.get('/api/customers', { params })
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
 * Get a specific customer by ID
 * @see api/app/Http/Controllers/Api/CustomerController.php::show()
 * Note: Automatically loads contacts, primaryContact, and invoices
 */
export function useGetCustomerQuery(customerId: MaybeRef<number>) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameter to ref for proper reactivity
  const customerIdRef = isRef(customerId) ? customerId : ref(customerId)

  return useQuery<IResponse<Customer>, AxiosError>({
    queryKey: ['customer', computed(() => toValue(customerIdRef))],
    queryFn: async (): Promise<IResponse<Customer>> => {
      const currentCustomerId = toValue(customerIdRef)
      const response = await axiosInstance.get(
        `/api/customers/${currentCustomerId}`,
      )
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      // Don't retry on 401 (unauthorized) - user is not authenticated
      if (error.response?.status === 401) {
        return false
      }
      // Don't retry on 404 (not found) - customer doesn't exist
      if (error.response?.status === 404) {
        return false
      }
      // Retry up to 2 times for other errors
      return failureCount < 2
    },
    staleTime: 5 * 60 * 1000, // Consider data fresh for 5 minutes
    enabled: computed(() => toValue(customerIdRef) > 0), // Only fetch if customerId is valid
  })
}

/**
 * Create customer request interface matching backend validation
 * @see api/app/Http/Requests/Customers/CustomerStoreRequest.php
 * Note: Based on Customer model fields from migration, not the request validation
 */
export interface CreateCustomerRequest {
  type: CustomerType
  name: string
  email?: string | null
  phone?: string | null
  address?: string | null
  zipcode?: string | null
  city?: string | null
  country?: string | null
  kvk_number?: string | null
  vat_number?: string | null
  iban_number?: string | null
}

/**
 * Update customer request interface matching backend validation
 * @see api/app/Http/Requests/Customers/CustomerUpdateRequest.php
 * Note: Based on Customer model fields from migration, not the request validation
 */
export interface UpdateCustomerRequest {
  type?: CustomerType
  name?: string
  email?: string | null
  phone?: string | null
  address?: string | null
  zipcode?: string | null
  city?: string | null
  country?: string | null
  kvk_number?: string | null
  vat_number?: string | null
  iban_number?: string | null
}

/**
 * Create a new customer
 * @see api/app/Http/Controllers/Api/CustomerController.php::store()
 */
export function useCreateCustomerMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<Customer>, AxiosError, CreateCustomerRequest>({
    mutationFn: async (
      data: CreateCustomerRequest,
    ): Promise<IResponse<Customer>> => {
      const response = await axiosInstance.post('/api/customers', data)
      return response.data
    },
    onSuccess: () => {
      // Invalidate customer list query to refresh the customers list
      queryClient.invalidateQueries({ queryKey: ['customerList'] })
    },
  })
}

/**
 * Update an existing customer
 * @see api/app/Http/Controllers/Api/CustomerController.php::update()
 */
export function useUpdateCustomerMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<
    IResponse<Customer>,
    AxiosError,
    { customerId: number, data: UpdateCustomerRequest }
  >({
    mutationFn: async ({ customerId, data }): Promise<IResponse<Customer>> => {
      const response = await axiosInstance.put(
        `/api/customers/${customerId}`,
        data,
      )
      return response.data
    },
    onSuccess: (_, variables) => {
      // Invalidate customer list query to refresh the customers list
      queryClient.invalidateQueries({ queryKey: ['customerList'] })
      // Invalidate the specific customer query to refresh the detail page
      queryClient.invalidateQueries({
        queryKey: ['customer', variables.customerId],
      })
    },
  })
}

/**
 * Delete a customer
 * @see api/app/Http/Controllers/Api/CustomerController.php::destroy()
 */
export function useDeleteCustomerMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<void, AxiosError, number>({
    mutationFn: async (customerId: number): Promise<void> => {
      await axiosInstance.delete(`/api/customers/${customerId}`)
    },
    onSuccess: () => {
      // Invalidate customer list query to refresh the customers list
      queryClient.invalidateQueries({ queryKey: ['customerList'] })
    },
  })
}

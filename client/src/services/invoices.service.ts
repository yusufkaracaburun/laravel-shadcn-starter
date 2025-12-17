import type { AxiosError, AxiosResponse } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { Customer } from './customers.service'
import type { Item, Money } from './items.service'
import type { IResponse } from './types/response.type'

/**
 * Invoice status enum matching backend InvoiceStatus
 */
export type InvoiceStatus = 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled'

/**
 * Payment status enum matching backend PaymentStatus
 */
export type PaymentStatus = 'pending' | 'paid' | 'failed' | 'refunded' | 'cancelled'

/**
 * Email status enum matching backend EmailStatus
 */
export type EmailStatus = 'pending' | 'sent' | 'failed'

/**
 * Payment interface matching backend PaymentResource
 * @see api/app/Http/Resources/PaymentResource.php
 */
export interface IInvoicePayment {
  id: number
  payment_number: string | null
  invoice_id: number
  customer_id: number
  date: string
  amount: Money | number
  method: string | null
  provider: string | null
  provider_reference: string | null
  status_formatted: {
    id: string
    value: string
    label: string
    color: string | null
    style: string | null
  }
  status: PaymentStatus
  paid_at: string | null
  refunded_at: string | null
  created_at: string
  updated_at: string
}

/**
 * Activity interface matching backend ActivityResource
 * @see api/app/Http/Resources/ActivityResource.php
 */
export interface IInvoiceActivity {
  id: number
  log_name: string | null
  description: string
  subject_id: number
  subject_type: string
  causer_id: number | null
  causer_type: string | null
  causer: {
    id: number
    name: string
    email: string | null
  } | null
  properties: {
    old: Record<string, any>
    attributes: Record<string, any>
  }
  event: string | null
  batch_uuid: string | null
  created_at: string
  updated_at: string
}

/**
 * Email interface matching backend InvoiceEmailResource
 * @see api/app/Http/Resources/InvoiceEmailResource.php
 */
export interface IInvoiceEmail {
  id: number
  invoice_id: number
  to: string
  subject: string
  body: string | null
  status_formatted: {
    id: string
    value: string
    label: string
    color: string | null
    style: string | null
  }
  status: EmailStatus
  sent_at: string | null
  opened_at: string | null
  clicked_at: string | null
  error_message: string | null
  created_at: string
  updated_at: string
}

/**
 * Invoice interface matching backend InvoiceResource exactly
 * @see api/app/Http/Resources/InvoiceResource.php
 * Note: Customer is loaded when include is used
 * Note: Items are loaded when include=items is used
 * Note: Payments, activities, and emails are loaded when include is used
 */
export interface IInvoice {
  id: number
  customer_id: number
  customer?: Customer // When loaded via include
  invoice_number: string | null
  date: string // Date format from backend
  due_days: number
  date_due: string // Date format from backend
  status: InvoiceStatus
  subtotal: Money | number
  total_vat_0: Money | number
  total_vat_9: Money | number
  total_vat_21: Money | number
  total: Money | number
  notes: string | null
  items?:
    | Array<{
      id: number
      invoice_id: number
      description: string | null
      quantity: number
      unit: string | null
      unit_price: Money | number
      vat_rate: number
      total_excl_vat: Money | number
      total_vat: Money | number
      total_incl_vat: Money | number
      sort_order: number
      created_at: string
      updated_at: string
    }>
    | {
      data: Array<{
        id: number
        invoice_id: number
        description: string | null
        quantity: number
        unit: string | null
        unit_price: Money | number
        vat_rate: number
        total_excl_vat: Money | number
        total_vat: Money | number
        total_incl_vat: Money | number
        sort_order: number
        created_at: string
        updated_at: string
      }>
    } // When loaded via include=items - backend returns paginated structure
  payments?: IInvoicePayment[] // When loaded via include=payments
  activities?: IInvoiceActivity[] // When loaded via include=activities
  emails?: IInvoiceEmail[] // When loaded via include=emails
  created_at: string // Format: "d-m-Y H:i:s"
  updated_at: string // Format: "d-m-Y H:i:s"
  [key: string]: unknown
}

/**
 * Paginated invoices response interface matching Laravel's pagination JSON structure
 * @see https://laravel.com/docs/12.x/pagination#converting-results-to-json
 * @see api/app/Http/Controllers/Api/InvoiceController.php::index()
 */
export interface IPaginatedInvoicesResponse {
  data: IInvoice[]
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
 * @returns Sort string for Spatie QueryBuilder (e.g., "invoice_number" or "-invoice_number" or "invoice_number,-date")
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
 * Invoice filters interface matching backend filter structure
 * @see api/app/Http/Controllers/Api/InvoiceController.php::index()
 */
export interface IInvoiceFilters {
  id?: number
  customer_id?: number
  status?: InvoiceStatus
  invoice_number?: string
  date?: string
  date_due?: string
  between?: string // Format: "YYYY-MM-DD,YYYY-MM-DD"
  search?: string
}

/**
 * List all invoices with pagination, sorting, and filtering
 * @see api/app/Http/Controllers/Api/InvoiceController.php::index()
 * @param page - Page number (default: 1) - can be a ref or number
 * @param pageSize - Number of items per page (default: 15) - can be a ref or number
 * @param sorting - Sorting state from TanStack Table (default: []) - can be a ref or array
 * @param filters - Filter object (default: {}) - can be a ref or object
 * @param include - Relationships to include (default: []) - can be a ref or array
 */
export function useGetInvoicesQuery(
  page: MaybeRef<number> = 1,
  pageSize: MaybeRef<number> = 15,
  sorting: MaybeRef<Array<{ id: string, desc: boolean }>> = [],
  filters: MaybeRef<IInvoiceFilters> = {},
  include: MaybeRef<string[]> = [],
) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameters to refs for proper reactivity
  const pageRef = isRef(page) ? page : ref(page)
  const pageSizeRef = isRef(pageSize) ? pageSize : ref(pageSize)
  const sortingRef = isRef(sorting) ? sorting : ref(sorting)
  const filtersRef = isRef(filters) ? filters : ref(filters)
  const includeRef = isRef(include) ? include : ref(include)

  return useQuery<IResponse<IPaginatedInvoicesResponse>, AxiosError>({
    queryKey: [
      'invoiceList',
      computed(() => toValue(pageRef)),
      computed(() => toValue(pageSizeRef)),
      computed(() => JSON.stringify(toValue(sortingRef))),
      computed(() => JSON.stringify(toValue(filtersRef))),
      computed(() => JSON.stringify(toValue(includeRef))),
    ],
    queryFn: async (): Promise<IResponse<IPaginatedInvoicesResponse>> => {
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
        if (currentFilters.customer_id !== undefined)
          filterParams.customer_id = currentFilters.customer_id
        if (currentFilters.status)
          filterParams.status = currentFilters.status
        if (currentFilters.invoice_number)
          filterParams.invoice_number = currentFilters.invoice_number
        if (currentFilters.date)
          filterParams.date = currentFilters.date
        if (currentFilters.date_due)
          filterParams.date_due = currentFilters.date_due
        if (currentFilters.between)
          filterParams.between = currentFilters.between
        if (currentFilters.search)
          filterParams.search = currentFilters.search

        if (Object.keys(filterParams).length > 0) {
          params.filter = filterParams
        }
      }

      const response = await axiosInstance.get('/api/invoices', { params })
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
 * Get a specific invoice by ID
 * @see api/app/Http/Controllers/Api/InvoiceController.php::show()
 * Note: Automatically loads customer relationship
 * @param include - Additional relationships to include (e.g., ['items'])
 */
export function useGetInvoiceQuery(invoiceId: MaybeRef<number>, options?: { include?: string[] }) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameter to ref for proper reactivity
  const invoiceIdRef = isRef(invoiceId) ? invoiceId : ref(invoiceId)

  return useQuery<IResponse<IInvoice>, AxiosError>({
    queryKey: [
      'invoice',
      computed(() => toValue(invoiceIdRef)),
      computed(() => options?.include?.join(',') || 'customer'),
    ],
    queryFn: async (): Promise<IResponse<IInvoice>> => {
      const currentInvoiceId = toValue(invoiceIdRef)
      const includes = ['customer', ...(options?.include || [])]
      const response = await axiosInstance.get(`/api/invoices/${currentInvoiceId}`, {
        params: { include: includes.join(',') },
      })
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      // Don't retry on 401 (unauthorized) - user is not authenticated
      if (error.response?.status === 401) {
        return false
      }
      // Don't retry on 404 (not found) - invoice doesn't exist
      if (error.response?.status === 404) {
        return false
      }
      // Retry up to 2 times for other errors
      return failureCount < 2
    },
    staleTime: 5 * 60 * 1000, // Consider data fresh for 5 minutes
    enabled: computed(() => toValue(invoiceIdRef) > 0), // Only fetch if invoiceId is valid
  })
}

/**
 * Create invoice request interface matching backend validation
 * @see api/app/Http/Requests/Invoices/InvoiceStoreRequest.php
 */
export interface ICreateInvoiceRequest {
  customer_id: number
  invoice_number?: string | null
  date: string // Format: "YYYY-MM-DD"
  due_days?: number
  date_due?: string // Format: "YYYY-MM-DD"
  status?: InvoiceStatus
  subtotal?: number
  total_vat_0?: number
  total_vat_9?: number
  total_vat_21?: number
  total?: number
  notes?: string | null
}

/**
 * Update invoice request interface matching backend validation
 * @see api/app/Http/Requests/Invoices/InvoiceUpdateRequest.php
 */
export interface IUpdateInvoiceRequest {
  customer_id?: number
  invoice_number?: string | null
  date?: string // Format: "YYYY-MM-DD"
  due_days?: number
  date_due?: string // Format: "YYYY-MM-DD"
  status?: InvoiceStatus
  subtotal?: number
  total_vat_0?: number
  total_vat_9?: number
  total_vat_21?: number
  total?: number
  notes?: string | null
}

/**
 * Create a new invoice
 * @see api/app/Http/Controllers/Api/InvoiceController.php::store()
 */
export function useCreateInvoiceMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<IInvoice>, AxiosError, ICreateInvoiceRequest>({
    mutationFn: async (data: ICreateInvoiceRequest): Promise<IResponse<IInvoice>> => {
      const response = await axiosInstance.post('/api/invoices', data)
      return response.data
    },
    onSuccess: () => {
      // Invalidate invoice list query to refresh the invoices list
      queryClient.invalidateQueries({ queryKey: ['invoiceList'] })
    },
  })
}

/**
 * Update an existing invoice
 * @see api/app/Http/Controllers/Api/InvoiceController.php::update()
 */
export function useUpdateInvoiceMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<
    IResponse<IInvoice>,
    AxiosError,
    { invoiceId: number, data: IUpdateInvoiceRequest }
  >({
    mutationFn: async ({ invoiceId, data }): Promise<IResponse<IInvoice>> => {
      const response = await axiosInstance.put(`/api/invoices/${invoiceId}`, data)
      return response.data
    },
    onSuccess: (_, variables) => {
      // Invalidate invoice list query to refresh the invoices list
      queryClient.invalidateQueries({ queryKey: ['invoiceList'] })
      // Invalidate the specific invoice query to refresh the detail page
      queryClient.invalidateQueries({ queryKey: ['invoice', variables.invoiceId] })
    },
  })
}

/**
 * Delete an invoice
 * @see api/app/Http/Controllers/Api/InvoiceController.php::destroy()
 */
export function useDeleteInvoiceMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<void, AxiosError, number>({
    mutationFn: async (invoiceId: number): Promise<void> => {
      await axiosInstance.delete(`/api/invoices/${invoiceId}`)
    },
    onSuccess: () => {
      // Invalidate invoice list query to refresh the invoices list
      queryClient.invalidateQueries({ queryKey: ['invoiceList'] })
    },
  })
}

/**
 * Download an invoice PDF
 * @see api/app/Http/Controllers/Api/InvoiceController.php::downloadPdf()
 */
export function useDownloadInvoicePdfMutation() {
  const { axiosInstance } = useAxios()

  return useMutation<AxiosResponse<Blob>, AxiosError, number>({
    mutationFn: (invoiceId: number) =>
      axiosInstance.post(
        `/api/invoices/${invoiceId}/pdf`,
        {},
        { responseType: 'blob' },
      ),
  })
}

/**
 * Invoice prerequisites interface
 * Note: items and customers are Laravel ResourceCollections which serialize to { data: T[] }
 */
export interface IInvoicePrerequisites {
  items: Item[] | { data: Item[] }
  customers: Customer[] | { data: Customer[] }
  next_invoice_number: string
}

/**
 * Get prerequisites for creating a new invoice
 * Returns all items, all customers, and the next invoice number
 * @see api/app/Http/Controllers/Api/InvoiceController.php::prerequisites()
 */
export function useGetInvoicePrerequisitesQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<IInvoicePrerequisites>, AxiosError>({
    queryKey: ['invoicePrerequisites'],
    queryFn: async (): Promise<IResponse<IInvoicePrerequisites>> => {
      const response = await axiosInstance.get('/api/invoices/prerequisites')
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
    staleTime: 5 * 60 * 1000, // Consider data fresh for 5 minutes
  })
}

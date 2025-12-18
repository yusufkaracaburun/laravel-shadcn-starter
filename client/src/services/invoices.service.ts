import type { AxiosError, AxiosResponse } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed, isRef, ref, toValue } from 'vue'

import type {
  ICreateInvoiceRequest,
  IInvoice,
  IInvoiceFilters,
  IInvoicePrerequisites,
  IPaginatedInvoicesResponse,
  IUpdateInvoiceRequest,
} from '@/pages/invoices/models/invoice'
import type { IResponse } from '@/services/types/response.type'

import { useAxios } from '@/composables/use-axios'

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

export function useGetInvoicePrerequisitesQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<IInvoicePrerequisites>, AxiosError>({
    queryKey: ['invoicePrerequisites'],
    queryFn: async (): Promise<IResponse<IInvoicePrerequisites>> => {
      const response = await axiosInstance.get('/api/invoices/prerequisites')
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      if (error.response?.status === 401) {
        return false
      }
      return failureCount < 2
    },
    staleTime: 5 * 60 * 1000,
  })
}

export function useGetInvoicesQuery(
  page: Ref<number> = ref(1),
  pageSize: Ref<number> = ref(15),
  sorting: Ref<Array<{ id: string, desc: boolean }>> = ref([]),
  filters: Ref<IInvoiceFilters> = ref({}),
  include: Ref<string[]> = ref([]),
) {
  const { axiosInstance } = useAxios()

  // No need to normalize, parameters are already refs
  const pageRef = page
  const pageSizeRef = pageSize
  const sortingRef = sorting
  const filtersRef = filters
  const includeRef = include

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

        if (currentFilters.id !== undefined) {
          filterParams.id = currentFilters.id
        }
        if (currentFilters.customer_id !== undefined) {
          filterParams.customer_id = currentFilters.customer_id
        }
        if (currentFilters.status) {
          filterParams.status = currentFilters.status
        }
        if (currentFilters.invoice_number) {
          filterParams.invoice_number = currentFilters.invoice_number
        }
        if (currentFilters.date) {
          filterParams.date = currentFilters.date
        }
        if (currentFilters.date_due) {
          filterParams.date_due = currentFilters.date_due
        }
        if (currentFilters.between) {
          filterParams.between = currentFilters.between
        }
        if (currentFilters.search) {
          filterParams.search = currentFilters.search
        }

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

export function useGetInvoiceQuery(id: Ref<number>, options?: { include?: string[] }) {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<IInvoice>, AxiosError>({
    queryKey: ['invoice', computed(() => toValue(id)), computed(() => options?.include?.join(',') || 'customer')],
    queryFn: async (): Promise<IResponse<IInvoice>> => {
      const currentId = toValue(id)
      const includes = ['customer', ...(options?.include || [])]
      const response = await axiosInstance.get(`/api/invoices/${currentId}`, { params: { include: includes.join(',') } })
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      if (error.response?.status === 401) {
        return false
      }
      if (error.response?.status === 404) {
        return false
      }
      return failureCount < 2
    },
    staleTime: 5 * 60 * 1000,
    enabled: computed(() => toValue(id) > 0),
  })
}

export function useCreateInvoiceMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<IInvoice>, AxiosError, ICreateInvoiceRequest>({
    mutationKey: ['createInvoice'],
    mutationFn: async (data: ICreateInvoiceRequest): Promise<IResponse<IInvoice>> => {
      const response = await axiosInstance.post('/api/invoices', data)
      return response.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['invoiceList'] })
    },
    onError: (error) => {
      console.error('Create invoice error:', error)
    },
  })
}

export function useUpdateInvoiceMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<
    IResponse<IInvoice>,
    AxiosError,
    { id: number, data: IUpdateInvoiceRequest }
  >({
    mutationKey: ['updateInvoice'],
    mutationFn: async ({ id, data }): Promise<IResponse<IInvoice>> => {
      const response = await axiosInstance.put(`/api/invoices/${id}`, data)
      return response.data
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['invoiceList'] })
      queryClient.invalidateQueries({ queryKey: ['invoice', variables.id] })
    },
    onError: (error) => {
      console.error('Update invoice error:', error)
    },
  })
}

export function useDeleteInvoiceMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<void, AxiosError, number>({
    mutationKey: ['deleteInvoice'],
    mutationFn: async (id: number): Promise<void> => {
      await axiosInstance.delete(`/api/invoices/${id}`)
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['invoiceList'] })
    },
    onError: (error) => {
      console.error('Delete invoice error:', error)
    },
  })
}

export function useDownloadInvoicePdfMutation() {
  const { axiosInstance } = useAxios()

  return useMutation<AxiosResponse<Blob>, AxiosError, number>({
    mutationKey: ['downloadInvoicePdf'],
    mutationFn: (id: number) =>
      axiosInstance.post(`/api/invoices/${id}/pdf`, {}, { responseType: 'blob' }),
    onError: (error) => {
      console.error('Download invoice PDF error:', error)
    },
  })
}

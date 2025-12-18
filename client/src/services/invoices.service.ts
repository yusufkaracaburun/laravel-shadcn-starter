import type { AxiosError, AxiosResponse } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed, ref, toValue } from 'vue'

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

  return useQuery<IResponse<IPaginatedInvoicesResponse>, AxiosError>({
    queryKey: [
      'invoiceList',
      computed(() => toValue(page)),
      computed(() => toValue(pageSize)),
      computed(() => JSON.stringify(toValue(sorting))),
      computed(() => JSON.stringify(toValue(filters))),
      computed(() => JSON.stringify(toValue(include))),
    ],
    queryFn: async (): Promise<IResponse<IPaginatedInvoicesResponse>> => {
      const params: Record<string, any> = {
        sort: convertSortingToQueryString(toValue(sorting)),
        page: toValue(page),
        per_page: toValue(pageSize),
        include: toValue(include),
        filter: toValue(filters),
      }

      const response = await axiosInstance.get('/api/invoices', { params })
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      if (error.response?.status === 401) {
        return false
      }
      return failureCount < 2
    },
    staleTime: 5 * 60 * 1000,
    enabled: computed(() => toValue(page) > 0 && toValue(pageSize) > 0),
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

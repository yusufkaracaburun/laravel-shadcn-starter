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

import type { ISorting } from './query-utils'

import {
  convertSortingToQueryString,
  defaultAxiosQueryOptions,
} from './query-utils'

enum QueryKeys {
  INVOICE_PREREQUISITES = 'invoicePrerequisites',
  INVOICE_LIST = 'invoiceList',
  GET_INVOICE = 'getInvoice',
  CREATE_INVOICE = 'createInvoice',
  UPDATE_INVOICE = 'updateInvoice',
  DELETE_INVOICE = 'deleteInvoice',
  DOWNLOAD_INVOICE_PDF = 'downloadInvoicePdf',
}

const API_URL = '/api/invoices'
const STALE_TIME = 5 * 60 * 1000

export function useInvoiceService() {
  const queryClient = useQueryClient()

  const { axiosInstance } = useAxios()

  function getInvoicePrerequisitesQuery(): ReturnType<
    typeof useQuery<IResponse<IInvoicePrerequisites>, AxiosError>
  > {
    return useQuery({
      queryKey: [QueryKeys.INVOICE_PREREQUISITES],
      queryFn: async (): Promise<IResponse<IInvoicePrerequisites>> => {
        const response = await axiosInstance.get(`${API_URL}/prerequisites`)
        return response.data
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getInvoicesQuery(
    page: Ref<number>,
    pageSize: Ref<number>,
    sorting: Ref<Array<ISorting>> = ref([]),
    filters: Ref<IInvoiceFilters> = ref({}),
    include: Ref<string[]> = ref([]),
  ): ReturnType<typeof useQuery<IResponse<IPaginatedInvoicesResponse>, AxiosError>> {
    return useQuery({
      queryKey: [
        QueryKeys.INVOICE_LIST,
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

        const response = await axiosInstance.get(`${API_URL}`, { params })
        return response.data
      },
      staleTime: STALE_TIME,
      enabled: computed(() => toValue(page) > 0 && toValue(pageSize) > 0),
      ...defaultAxiosQueryOptions(),
    })
  }

  function getInvoiceMutation(): ReturnType<
    typeof useMutation<IResponse<IInvoice>, AxiosError, { id: number, include?: string[] }>
  > {
    return useMutation<IResponse<IInvoice>, AxiosError, { id: number, include?: string[] }>({
      mutationKey: [QueryKeys.GET_INVOICE],
      mutationFn: async ({ id, include }): Promise<IResponse<IInvoice>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: include?.join(',') ?? '' },
        })
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.INVOICE_LIST] })
        queryClient.invalidateQueries({ queryKey: [QueryKeys.GET_INVOICE, variables.id] })
      },
      onError: (error) => {
        console.error('Get invoice error:', error)
      },
    })
  }

  function createInvoiceMutation(): ReturnType<
    typeof useMutation<IResponse<IInvoice>, AxiosError, ICreateInvoiceRequest>
  > {
    return useMutation<IResponse<IInvoice>, AxiosError, ICreateInvoiceRequest>({
      mutationKey: [QueryKeys.CREATE_INVOICE],
      mutationFn: async (data: ICreateInvoiceRequest): Promise<IResponse<IInvoice>> => {
        const response = await axiosInstance.post(`${API_URL}`, data)
        return response.data
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.INVOICE_LIST] })
      },
      onError: (error) => {
        console.error('Create invoice error:', error)
      },
    })
  }

  function updateInvoiceMutation(): ReturnType<
    typeof useMutation<IResponse<IInvoice>, AxiosError, { id: number, data: IUpdateInvoiceRequest }>
  > {
    return useMutation<
      IResponse<IInvoice>,
      AxiosError,
      { id: number, data: IUpdateInvoiceRequest }
    >({
      mutationKey: [QueryKeys.UPDATE_INVOICE],
      mutationFn: async ({ id, data }): Promise<IResponse<IInvoice>> => {
        const response = await axiosInstance.put(`${API_URL}/${id}`, data)
        return response.data
      },
      onSuccess: (_, variables) => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.INVOICE_LIST] })
        queryClient.invalidateQueries({ queryKey: [QueryKeys.GET_INVOICE, variables.id] })
      },
      onError: (error) => {
        console.error('Update invoice error:', error)
      },
    })
  }

  function deleteInvoiceMutation(): ReturnType<typeof useMutation<void, AxiosError, number>> {
    return useMutation<void, AxiosError, number>({
      mutationKey: [QueryKeys.DELETE_INVOICE],
      mutationFn: async (id: number): Promise<void> => {
        await axiosInstance.delete(`${API_URL}/${id}`)
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.INVOICE_LIST] })
      },
      onError: (error) => {
        console.error('Delete invoice error:', error)
      },
    })
  }

  function downloadInvoicePdfMutation(): ReturnType<
    typeof useMutation<AxiosResponse<Blob>, AxiosError, number>
  > {
    return useMutation<AxiosResponse<Blob>, AxiosError, number>({
      mutationKey: [QueryKeys.DOWNLOAD_INVOICE_PDF],
      mutationFn: (id: number) =>
        axiosInstance.post(`${API_URL}/${id}/pdf`, {}, { responseType: 'blob' }),
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: [QueryKeys.INVOICE_LIST] })
      },
      onError: (error) => {
        console.error('Download invoice PDF error:', error)
      },
    })
  }

  return {
    getInvoicesQuery,
    getInvoicePrerequisitesQuery,
    getInvoiceMutation,
    createInvoiceMutation,
    updateInvoiceMutation,
    deleteInvoiceMutation,
    downloadInvoicePdfMutation,
  }
}

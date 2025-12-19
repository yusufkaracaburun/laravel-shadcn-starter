import type { AxiosError, AxiosResponse } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { computed, toValue } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type {
  ICreateInvoiceRequest,
  IInvoice,
  IInvoiceFilters,
  IInvoicePrerequisites,
  IPaginatedInvoicesResponse,
  IUpdateInvoiceRequest,
} from '@/pages/invoices/models/invoice'
import type { ISorting } from '@/services/query-utils'
import type { IResponse } from '@/services/types/response.type'

import { useAxios } from '@/composables/use-axios'

import { buildQueryString, convertSortingToQueryString, defaultAxiosQueryOptions } from './query-utils'

enum QueryKeys {
  INVOICE_PREREQUISITES = 'invoicePrerequisites',
  INVOICE_LIST = 'invoiceList',
  GET_INVOICE = 'getInvoice',
  GET_INVOICE_BY_ID = 'getInvoiceById',
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
    page: number,
    per_page: TPageSize,
    sort: ISorting,
    filter: IInvoiceFilters,
    include: string[],
  ): ReturnType<typeof useQuery<IResponse<IPaginatedInvoicesResponse>, AxiosError>> {
    return useQuery({
      queryKey: [
        QueryKeys.INVOICE_LIST,
        page,
        per_page,
        sort,
        filter,
        include,
      ],
      queryFn: async (): Promise<IResponse<IPaginatedInvoicesResponse>> => {
        const params = {
          page,
          per_page,
          sort,
          filter,
          include,
        }

        console.warn(page, per_page, sort, filter, include)

        const response = await axiosInstance.get(`${API_URL}${buildQueryString(params)}`)
        return response.data
      },
      staleTime: STALE_TIME,
      enabled: computed(() => page > 0 && per_page > 0),
      ...defaultAxiosQueryOptions(),
    })
  }

  function getInvoiceByIdQuery(
    id: number,
    includes?: string[],
  ): ReturnType<typeof useQuery<IResponse<IInvoice>, AxiosError>> {
    return useQuery({
      queryKey: [QueryKeys.GET_INVOICE_BY_ID, id, includes],
      queryFn: async () => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
        })
        return response.data
      },
      staleTime: STALE_TIME,
      ...defaultAxiosQueryOptions(),
    })
  }

  function getInvoiceMutation(): ReturnType<
    typeof useMutation<IResponse<IInvoice>, AxiosError, { id: number, includes?: string[] }>
  > {
    return useMutation<IResponse<IInvoice>, AxiosError, { id: number, includes?: string[] }>({
      mutationKey: [QueryKeys.GET_INVOICE],
      mutationFn: async ({ id, includes }): Promise<IResponse<IInvoice>> => {
        const response = await axiosInstance.get(`${API_URL}/${id}`, {
          params: { include: includes?.join(',') ?? '' },
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
    getInvoicePrerequisitesQuery,
    getInvoicesQuery,
    getInvoiceByIdQuery,
    getInvoiceMutation,
    createInvoiceMutation,
    updateInvoiceMutation,
    deleteInvoiceMutation,
    downloadInvoicePdfMutation,
  }
}

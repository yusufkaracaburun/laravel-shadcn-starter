import type { SortingState } from '@tanstack/vue-table'

import { useRoute } from 'vue-router'

import type { IServerPagination, TPageSize } from '@/components/data-table/types'
import type {
  ICreateInvoiceRequest,
  IInvoiceFilters,
  IUpdateInvoiceRequest,
} from '@/pages/invoices/models/invoice'

import { DEFAULT_PAGE, DEFAULT_PAGE_SIZE } from '@/components/data-table/types'
import { useToast } from '@/composables/use-toast'
import { useInvoiceService } from '@/services/invoices.service'
import { useErrorStore } from '@/stores/error.store'
import { downloadBlobFromAxiosResponse } from '@/utils/blob'

const route = useRoute()
const InvoiceContext = {
  FETCH_INVOICE_PREREQUISITES: 'fetchInvoicePrerequisites',
  FETCH_INVOICES: 'fetchInvoices',
  GET_INVOICE: 'getInvoice',
  GET_INVOICE_BY_ID: 'getInvoiceById',
  CREATE_INVOICE: 'createInvoice',
  UPDATE_INVOICE: 'updateInvoice',
  DELETE_INVOICE: 'deleteInvoice',
  DOWNLOAD_INVOICE_PDF: 'downloadInvoicePdf',
}

const InvoiceMessages = {
  FETCH_INVOICES_SUCCESS: 'Invoices fetched successfully!',
  CREATE_INVOICE_SUCCESS: 'Invoice created successfully!',
  UPDATE_INVOICE_SUCCESS: 'Invoice updated successfully!',
  DELETE_INVOICE_SUCCESS: 'Invoice deleted successfully!',
  DOWNLOAD_INVOICE_PDF_SUCCESS: 'Invoice PDF downloaded successfully!',
}

export function useInvoices() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const invoiceService = useInvoiceService()

  const page = ref(DEFAULT_PAGE)
  const pageSize = ref<TPageSize>(DEFAULT_PAGE_SIZE)
  const sorting = ref<SortingState>([])
  const filters = ref<IInvoiceFilters>({})
  const includes = {
    customer: 'customer',
    items: 'items',
    payments: 'payments',
    activities: 'activities',
    emails: 'emails',
  }

  function onSortingChange(newSorting: SortingState): void {
    sorting.value = newSorting
    page.value = DEFAULT_PAGE
  }

  function onFiltersChange(newFilters: IInvoiceFilters): void {
    filters.value = newFilters
    page.value = DEFAULT_PAGE
  }

  function clearFilters() {
    filters.value = {} as IInvoiceFilters
    page.value = DEFAULT_PAGE
  }

  function onPageChange(newPage: number): void {
    page.value = newPage
  }

  function onPageSizeChange(newPageSize: TPageSize): void {
    pageSize.value = newPageSize
    page.value = DEFAULT_PAGE
  }

  const getInvoicePrerequisitesQuery = invoiceService.getInvoicePrerequisitesQuery()
  async function fetchInvoicePrerequisites() {
    try {
      const response = await getInvoicePrerequisitesQuery.refetch()
      return response.data
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.FETCH_INVOICE_PREREQUISITES })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const getInvoicesQuery = invoiceService.getInvoicesQuery(
    page.value,
    pageSize.value,
    sorting.value,
    filters.value,
    [includes.customer],
  )
  const { data, isLoading, isFetching, refetch: fetchInvoices } = getInvoicesQuery
  async function fetchInvoicesData() {
    try {
      const response = await fetchInvoices()
      return response.data
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.FETCH_INVOICES })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  // const invoiceId = computed(() => {
  //   if (!route) {
  //     return undefined
  //   }
  //   return Number((route.params as { id: string }).id as string)
  // })
  // const getInvoiceByIdQuery = invoiceService.getInvoiceByIdQuery(invoiceId.value, [includes.customer, includes.items])
  // const { data: invoiceByIdResponse, isLoading: isLoadingInvoiceById, isError: isErrorInvoiceById, error: errorInvoiceById, refetch: refetchInvoiceById } = getInvoiceByIdQuery
  // async function fetchInvoiceById() {
  //   try {
  //     const response = await refetchInvoiceById()
  //     return response.data
  //   }
  //   catch (error: any) {
  //     errorStore.setError(error, { context: InvoiceContext.GET_INVOICE_BY_ID })
  //     const message = errorStore.getErrorMessage(error)
  //     toast.showError(message)
  //     throw error
  //   }
  // }

  const getInvoiceMutation = invoiceService.getInvoiceMutation()
  async function getInvoice(id: number) {
    try {
      const response = await getInvoiceMutation.mutateAsync({
        id,
        include: [includes.customer, includes.items],
      })
      return response.data
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.GET_INVOICE })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const createInvoiceMutation = invoiceService.createInvoiceMutation()
  async function createInvoice(data: ICreateInvoiceRequest) {
    try {
      const response = await createInvoiceMutation.mutateAsync(data)
      toast.showSuccess(InvoiceMessages.CREATE_INVOICE_SUCCESS)
      return response
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.CREATE_INVOICE })
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      }
      else {
        toast.showError(message)
      }
      throw error
    }
  }

  const updateInvoiceMutation = invoiceService.updateInvoiceMutation()
  async function updateInvoice(id: number, data: IUpdateInvoiceRequest) {
    try {
      const response = await updateInvoiceMutation.mutateAsync({ id, data })
      toast.showSuccess(InvoiceMessages.UPDATE_INVOICE_SUCCESS)
      return response
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.UPDATE_INVOICE })
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      }
      else {
        toast.showError(message)
      }
      throw error
    }
  }

  const deleteInvoiceMutation = invoiceService.deleteInvoiceMutation()
  async function deleteInvoice(id: number) {
    try {
      await deleteInvoiceMutation.mutateAsync(id)
      toast.showSuccess(InvoiceMessages.DELETE_INVOICE_SUCCESS)
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.DELETE_INVOICE })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const downloadInvoicePdfMutation = invoiceService.downloadInvoicePdfMutation()
  async function downloadInvoicePdf(id: number) {
    try {
      const response = await downloadInvoicePdfMutation.mutateAsync(id)
      downloadBlobFromAxiosResponse(response, `factuur_${id}.pdf`)
      toast.showSuccess(InvoiceMessages.DOWNLOAD_INVOICE_PDF_SUCCESS)
    }
    catch (error: any) {
      errorStore.setError(error, { context: InvoiceContext.DOWNLOAD_INVOICE_PDF })
      toast.showError(errorStore.getErrorMessage(error))
      throw error
    }
  }

  const loading = computed(() => isLoading.value || isFetching.value)

  const serverPagination = computed<IServerPagination>(() => ({
    page: data.value?.data?.current_page ?? page.value,
    pageSize: pageSize.value,
    total: data.value?.data?.total ?? 0,
    onPageChange,
    onPageSizeChange,
  }))

  watch([page, pageSize], ([newPage, newPageSize], [oldPage, oldPageSize]) => {
    if (oldPage === undefined || oldPageSize === undefined) {
      return
    }
    if (oldPage !== newPage || oldPageSize !== newPageSize) {
      fetchInvoices()
    }
  })

  return {
    sorting,
    filters,
    includes,
    data,
    onSortingChange,
    onFiltersChange,
    clearFilters,
    onPageChange,
    onPageSizeChange,
    fetchInvoicePrerequisites,
    fetchInvoicesData,
    getInvoice,
    createInvoice,
    updateInvoice,
    deleteInvoice,
    downloadInvoicePdf,
    loading,
    serverPagination,
  }
}

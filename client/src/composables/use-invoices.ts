import type { SortingState } from '@tanstack/vue-table'

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

const InvoiceContext = {
  FETCH_INVOICES: 'fetchInvoices',
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
  const include = ref<string[]>([includes.customer])

  const invoiceService = useInvoiceService()
  const {
    data,
    isLoading,
    isFetching,
    refetch: fetchInvoices,
  } = invoiceService.getInvoicesQuery(page, pageSize, sorting, filters, include)

  const createInvoiceMutation = invoiceService.createInvoiceMutation()
  const updateInvoiceMutation = invoiceService.updateInvoiceMutation()
  const deleteInvoiceMutation = invoiceService.deleteInvoiceMutation()
  const downloadInvoicePdfMutation = invoiceService.downloadInvoicePdfMutation()

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

  async function createInvoice(data: ICreateInvoiceRequest) {
    try {
      const response = await createInvoiceMutation.mutateAsync(data)
      toast.showSuccess(InvoiceMessages.CREATE_INVOICE_SUCCESS)
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: InvoiceContext.CREATE_INVOICE })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
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

  async function updateInvoice(id: number, data: IUpdateInvoiceRequest) {
    try {
      const response = await updateInvoiceMutation.mutateAsync({ id, data })
      toast.showSuccess(InvoiceMessages.UPDATE_INVOICE_SUCCESS)
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: InvoiceContext.UPDATE_INVOICE })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
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

  async function deleteInvoice(id: number) {
    try {
      await deleteInvoiceMutation.mutateAsync(id)
      toast.showSuccess(InvoiceMessages.DELETE_INVOICE_SUCCESS)
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: InvoiceContext.DELETE_INVOICE })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

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

  return {
    data,
    loading,
    includes,
    fetchInvoicesData,
    serverPagination,
    sorting,
    onSortingChange,
    filters,
    onFiltersChange,
    clearFilters,
    include,
    createInvoice,
    createInvoiceMutation,
    updateInvoice,
    updateInvoiceMutation,
    deleteInvoice,
    deleteInvoiceMutation,
    downloadInvoicePdf,
    downloadInvoicePdfMutation,
  }
}

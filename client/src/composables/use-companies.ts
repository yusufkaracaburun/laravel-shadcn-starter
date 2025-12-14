import type { SortingState } from '@tanstack/vue-table'

import type { ServerPagination } from '@/components/data-table/types'
import type { CreateCompanyRequest, UpdateCompanyRequest } from '@/services/companies.service'

import { useToast } from '@/composables/use-toast'
import {
  useCreateCompanyMutation,
  useDeleteCompanyMutation,
  useGetCompaniesQuery,
  useUpdateCompanyMutation,
} from '@/services/companies.service'
import { useErrorStore } from '@/stores/error.store'

export function useCompanies() {
  const toast = useToast()
  const errorStore = useErrorStore()

  // Pagination state
  const page = ref(1)
  const pageSize = ref(10)

  // Sorting state - managed here and passed to table
  const sorting = ref<SortingState>([])

  // Handler for sorting changes from table
  function onSortingChange(newSorting: SortingState) {
    sorting.value = newSorting
    // Reset to first page when sorting changes
    page.value = 1
  }

  const { data: companiesResponse, isLoading, isFetching, refetch: fetchCompanies } = useGetCompaniesQuery(
    page,
    pageSize,
    sorting,
  )

  // Watch for page and pageSize changes to trigger refetch
  // Vue Query tracks computed refs in queryKey, but explicit watch ensures refetch on changes
  watch([page, pageSize], ([newPage, newPageSize], [oldPage, oldPageSize]) => {
    // Skip initial trigger
    if (oldPage === undefined || oldPageSize === undefined) {
      return
    }
    // Only refetch if values actually changed
    if (oldPage !== newPage || oldPageSize !== newPageSize) {
      fetchCompanies()
    }
  })

  // Computed refs for easy access
  const companies = computed(() => companiesResponse.value?.data?.data ?? [])
  const loading = computed(() => isLoading.value || isFetching.value)

  // Extract pagination metadata from Laravel's pagination structure
  const pagination = computed(() => companiesResponse.value?.data ?? {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: null,
    to: null,
  })

  // Pagination handlers
  function onPageChange(newPage: number) {
    page.value = newPage
  }

  function onPageSizeChange(newPageSize: number) {
    pageSize.value = newPageSize
    page.value = 1 // Reset to first page when page size changes
  }

  // Server pagination object for data-table
  // Uses local pageSize value so dropdown updates immediately when changed
  const serverPagination = computed<ServerPagination>(() => ({
    page: pagination.value.current_page,
    pageSize: pageSize.value, // Use local state for immediate UI update
    total: pagination.value.total,
    onPageChange,
    onPageSizeChange,
  }))

  async function fetchCompaniesData() {
    try {
      const companiesResponse = await fetchCompanies()
      return companiesResponse.data
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'fetchCompanies' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  // Create company mutation
  const createCompanyMutation = useCreateCompanyMutation()
  const updateCompanyMutation = useUpdateCompanyMutation()
  const deleteCompanyMutation = useDeleteCompanyMutation()

  async function createCompany(data: CreateCompanyRequest) {
    try {
      const response = await createCompanyMutation.mutateAsync(data)
      toast.showSuccess('Company created successfully!')
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'createCompany' })

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

  async function updateCompany(companyId: number, data: UpdateCompanyRequest) {
    try {
      const response = await updateCompanyMutation.mutateAsync({ companyId, data })
      toast.showSuccess('Company updated successfully!')
      return response
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'updateCompany' })

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

  async function deleteCompany(companyId: number) {
    try {
      await deleteCompanyMutation.mutateAsync(companyId)
      toast.showSuccess('Company deleted successfully!')
    }
    catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'deleteCompany' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  return {
    companies,
    loading,
    fetchCompaniesData,
    companiesResponse,
    serverPagination,
    sorting,
    onSortingChange,
    createCompany,
    createCompanyMutation,
    updateCompany,
    updateCompanyMutation,
    deleteCompany,
    deleteCompanyMutation,
  }
}

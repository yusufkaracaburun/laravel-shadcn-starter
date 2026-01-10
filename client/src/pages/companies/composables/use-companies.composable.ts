import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import type { SortingState } from '@tanstack/vue-table'

import type { ServerPagination } from '@/components/data-table/types'
import type {
  CreateCompanyRequest,
  UpdateCompanyRequest,
} from '@/pages/companies/services/companies.service'
import type { IResponse } from '@/services/types/response.type'
import type {
  ICompany,
  ICompanyFilters,
} from '@/pages/companies/models/companies'
import {
  ECompanyStatus,
  ECompanyIndustry,
  ECompanyEmployeeSize,
} from '@/pages/companies/models/companies'

import { useToast } from '@/composables/use-toast.composable'
import {
  useCreateCompanyMutation,
  useDeleteCompanyMutation,
  useGetCompaniesQuery,
  useGetCompanyQuery,
  useUpdateCompanyMutation,
} from '@/pages/companies/services/companies.service'
import { useErrorStore } from '@/stores/error.store'

const CompanyContext = {
  FETCH_LIST: 'fetchCompanies',
  GET_COMPANY_BY_ID: 'getCompanyById',
  CREATE: 'createCompany',
  UPDATE: 'updateCompany',
  DELETE: 'deleteCompany',
}

export function useCompanies() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const route = useRoute()

  // Pagination state
  const page = ref(1)
  const pageSize = ref(10)

  // Sorting state - managed here and passed to table
  const sorting = ref<SortingState>([])

  // Filters state
  const filters = ref<ICompanyFilters>({})

  // Handler for sorting changes from table
  function onSortingChange(newSorting: SortingState) {
    sorting.value = newSorting
    // Reset to first page when sorting changes
    page.value = 1
  }

  // Handler for filter changes
  function onFiltersChange(newFilters: ICompanyFilters) {
    filters.value = newFilters
    // Reset to first page when filters change
    page.value = 1
  }

  // Clear all filters
  function clearFilters() {
    filters.value = {}
    page.value = 1
  }

  const {
    data: companiesResponse,
    isLoading,
    isFetching,
    refetch: fetchCompanies,
  } = useGetCompaniesQuery(page, pageSize, sorting)

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
  const pagination = computed(
    () =>
      companiesResponse.value?.data ?? {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: null,
        to: null,
      },
  )

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
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: CompanyContext.FETCH_LIST })

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
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: CompanyContext.CREATE })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
        toast.showError(message)
      }
      throw error
    }
  }

  async function updateCompany(companyId: number, data: UpdateCompanyRequest) {
    try {
      const response = await updateCompanyMutation.mutateAsync({
        companyId,
        data,
      })
      toast.showSuccess('Company updated successfully!')
      return response
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: CompanyContext.UPDATE })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)

      // Show toast with appropriate message
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
        toast.showError(message)
      }
      throw error
    }
  }

  async function deleteCompany(companyId: number) {
    try {
      await deleteCompanyMutation.mutateAsync(companyId)
      toast.showSuccess('Company deleted successfully!')
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: CompanyContext.DELETE })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  // Single company query
  const companyId = computed(() => {
    if (!route) {
      return undefined
    }
    const params = route.params as { id?: string | string[] }
    const idParam = Array.isArray(params.id) ? params.id[0] : params.id
    if (
      !idParam
      || typeof idParam !== 'string'
      || Number.isNaN(Number(idParam))
    ) {
      return undefined
    }
    return Number(idParam)
  })

  const {
    data: companyByIdResponse,
    isLoading: isLoadingCompanyById,
    isError: isErrorCompanyById,
    error: errorCompanyById,
    refetch: refetchCompanyById,
  } = useGetCompanyQuery(companyId)

  async function fetchCompanyByIdData(): Promise<IResponse<ICompany>> {
    try {
      const response = await refetchCompanyById()
      return response.data as IResponse<ICompany>
    } catch (error: any) {
      errorStore.setError(error, { context: CompanyContext.GET_COMPANY_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  /**
   * Get initial form values for company form
   * @param company - Optional company object for edit mode
   * @returns Initial values object for vee-validate form
   */
  function getCompanyFormInitialValues(company?: ICompany | null) {
    // Convert status string to ECompanyStatus enum
    const status =
      company?.status &&
      Object.values(ECompanyStatus).includes(company.status as ECompanyStatus)
        ? (company.status as ECompanyStatus)
        : ECompanyStatus.PENDING

    // Convert industry string to ECompanyIndustry enum
    const industry =
      company?.industry &&
      Object.values(ECompanyIndustry).includes(
        company.industry as ECompanyIndustry,
      )
        ? (company.industry as ECompanyIndustry)
        : ECompanyIndustry.TECHNOLOGY

    // Convert employees string to ECompanyEmployeeSize enum
    const employees =
      company?.employees &&
      Object.values(ECompanyEmployeeSize).includes(
        company.employees as ECompanyEmployeeSize,
      )
        ? (company.employees as ECompanyEmployeeSize)
        : ECompanyEmployeeSize.ONE_TO_TEN

    return {
      name: company?.name || '',
      email: company?.email || '',
      phone: company?.phone || null,
      industry,
      status,
      employees,
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
    filter: filters,
    onFiltersChange,
    clearFilters,
    createCompany,
    createCompanyMutation,
    updateCompany,
    updateCompanyMutation,
    deleteCompany,
    deleteCompanyMutation,
    isCreating: computed(() => createCompanyMutation.isPending.value),
    isUpdating: computed(() => updateCompanyMutation.isPending.value),
    isDeleting: computed(() => deleteCompanyMutation.isPending.value),
    companyId,
    companyByIdResponse,
    isLoadingCompanyById,
    isErrorCompanyById,
    errorCompanyById,
    fetchCompanyByIdData,
    getCompanyFormInitialValues,
  }
}

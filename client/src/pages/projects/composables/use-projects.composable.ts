import type { SortingState } from '@tanstack/vue-table'

import type { ServerPagination } from '@/components/data-table/types'
import type {
  CreateProjectRequest,
  UpdateProjectRequest,
} from '@/pages/projects/services/projects.service'

import { useToast } from '@/composables/use-toast.composable'
import {
  useCreateProjectMutation,
  useDeleteProjectMutation,
  useGetProjectsQuery,
  useUpdateProjectMutation,
} from '@/pages/projects/services/projects.service'
import { useErrorStore } from '@/stores/error.store'

export function useProjects() {
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

  const {
    data: projectsResponse,
    isLoading,
    isFetching,
    refetch: fetchProjects,
  } = useGetProjectsQuery(page, pageSize, sorting)

  // Watch for page and pageSize changes to trigger refetch
  // Vue Query tracks computed refs in queryKey, but explicit watch ensures refetch on changes
  watch([page, pageSize], ([newPage, newPageSize], [oldPage, oldPageSize]) => {
    // Skip initial trigger
    if (oldPage === undefined || oldPageSize === undefined) {
      return
    }
    // Only refetch if values actually changed
    if (oldPage !== newPage || oldPageSize !== newPageSize) {
      fetchProjects()
    }
  })

  // Transform backend data to include camelCase aliases for UI compatibility
  const projects = computed(() => {
    const backendProjects = projectsResponse.value?.data?.data ?? []
    return backendProjects.map(p => ({
      ...p,
      startDate: p.start_date,
      endDate: p.end_date,
    }))
  })

  const loading = computed(() => isLoading.value || isFetching.value)

  // Extract pagination metadata from Laravel's pagination structure
  const pagination = computed(
    () =>
      projectsResponse.value?.data ?? {
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

  async function fetchProjectsData() {
    try {
      const projectsResponse = await fetchProjects()
      return projectsResponse.data
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'fetchProjects' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  // Create project mutation
  const createProjectMutation = useCreateProjectMutation()
  const updateProjectMutation = useUpdateProjectMutation()
  const deleteProjectMutation = useDeleteProjectMutation()

  async function createProject(data: CreateProjectRequest) {
    try {
      const response = await createProjectMutation.mutateAsync(data)
      toast.showSuccess('Project created successfully!')
      return response
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'createProject' })

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

  async function updateProject(projectId: number, data: UpdateProjectRequest) {
    try {
      const response = await updateProjectMutation.mutateAsync({
        projectId,
        data,
      })
      toast.showSuccess('Project updated successfully!')
      return response
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'updateProject' })

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

  async function deleteProject(projectId: number) {
    try {
      await deleteProjectMutation.mutateAsync(projectId)
      toast.showSuccess('Project deleted successfully!')
    } catch (error: any) {
      // Store error with context
      errorStore.setError(error, { context: 'deleteProject' })

      // Use error store utilities for messages
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  return {
    projects,
    loading,
    fetchProjectsData,
    projectsResponse,
    serverPagination,
    sorting,
    onSortingChange,
    createProject,
    createProjectMutation,
    updateProject,
    updateProjectMutation,
    deleteProject,
    deleteProjectMutation,
  }
}

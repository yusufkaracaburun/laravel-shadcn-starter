import type { AxiosError } from 'axios'

import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { IResponse } from './types/response.type'

/**
 * Project interface matching backend ProjectResource exactly
 * @see api/app/Http/Resources/ProjectResource.php
 */
export interface Project {
  id: number
  name: string
  description: string | null
  status: string
  priority: string
  category: string
  start_date: string | null
  end_date: string | null
  progress: number
  team_id: number | null
  created_at: string
  updated_at: string
  [key: string]: unknown
}

/**
 * Paginated projects response interface matching Laravel's pagination JSON structure
 * @see https://laravel.com/docs/12.x/pagination#converting-results-to-json
 * @see api/app/Http/Controllers/Api/ProjectController.php::index()
 */
export interface PaginatedProjectsResponse {
  data: Project[]
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
 * @returns Sort string for Spatie QueryBuilder (e.g., "name" or "-name" or "name,-email")
 */
function convertSortingToQueryString(sorting: Array<{ id: string, desc: boolean }>): string | undefined {
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
 * List all projects with pagination and sorting
 * @see api/app/Http/Controllers/Api/ProjectController.php::index()
 * @param page - Page number (default: 1) - can be a ref or number
 * @param pageSize - Number of items per page (default: 15) - can be a ref or number
 * @param sorting - Sorting state from TanStack Table (default: []) - can be a ref or array
 */
export function useGetProjectsQuery(
  page: MaybeRef<number> = 1,
  pageSize: MaybeRef<number> = 10,
  sorting: MaybeRef<Array<{ id: string, desc: boolean }>> = [],
) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameters to refs for proper reactivity
  const pageRef = isRef(page) ? page : ref(page)
  const pageSizeRef = isRef(pageSize) ? pageSize : ref(pageSize)
  const sortingRef = isRef(sorting) ? sorting : ref(sorting)

  return useQuery<IResponse<PaginatedProjectsResponse>, AxiosError>({
    queryKey: [
      'projectList',
      computed(() => toValue(pageRef)),
      computed(() => toValue(pageSizeRef)),
      computed(() => JSON.stringify(toValue(sortingRef))),
    ],
    queryFn: async (): Promise<IResponse<PaginatedProjectsResponse>> => {
      // Use toValue() to read current values in queryFn
      const currentPage = toValue(pageRef)
      const currentPageSize = toValue(pageSizeRef)
      const currentSorting = toValue(sortingRef)
      const sortParam = convertSortingToQueryString(currentSorting)

      const params: Record<string, any> = {
        page: currentPage,
        per_page: currentPageSize,
      }

      // Add sort parameter if sorting is provided
      if (sortParam) {
        params.sort = sortParam
      }

      const response = await axiosInstance.get('/api/project', { params })
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
 * Get a specific project by ID
 * @see api/app/Http/Controllers/Api/ProjectController.php::show()
 */
export function useGetProjectQuery(projectId: MaybeRef<number>) {
  const { axiosInstance } = useAxios()

  // Normalize MaybeRef parameter to ref for proper reactivity
  const projectIdRef = isRef(projectId) ? projectId : ref(projectId)

  return useQuery<IResponse<Project>, AxiosError>({
    queryKey: ['project', computed(() => toValue(projectIdRef))],
    queryFn: async (): Promise<IResponse<Project>> => {
      const currentProjectId = toValue(projectIdRef)
      const response = await axiosInstance.get(`/api/project/${currentProjectId}`)
      return response.data
    },
    retry: (failureCount: number, error: AxiosError) => {
      // Don't retry on 401 (unauthorized) - user is not authenticated
      if (error.response?.status === 401) {
        return false
      }
      // Don't retry on 404 (not found) - project doesn't exist
      if (error.response?.status === 404) {
        return false
      }
      // Retry up to 2 times for other errors
      return failureCount < 2
    },
    staleTime: 5 * 60 * 1000, // Consider data fresh for 5 minutes
    enabled: computed(() => toValue(projectIdRef) > 0), // Only fetch if projectId is valid
  })
}

/**
 * Create project request interface matching backend validation
 * @see api/app/Http/Controllers/Api/ProjectController.php::store()
 */
export interface CreateProjectRequest {
  name: string
  description?: string | null
  status: string
  priority: string
  category: string
  start_date?: string | null
  end_date?: string | null
  progress?: number
}

/**
 * Update project request interface matching backend validation
 * @see api/app/Http/Controllers/Api/ProjectController.php::update()
 */
export interface UpdateProjectRequest {
  name?: string
  description?: string | null
  status?: string
  priority?: string
  category?: string
  start_date?: string | null
  end_date?: string | null
  progress?: number
}

/**
 * Create a new project
 * @see api/app/Http/Controllers/Api/ProjectController.php::store()
 */
export function useCreateProjectMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<Project>, AxiosError, CreateProjectRequest>({
    mutationFn: async (data: CreateProjectRequest): Promise<IResponse<Project>> => {
      const response = await axiosInstance.post('/api/project', data)
      return response.data
    },
    onSuccess: () => {
      // Invalidate project list query to refresh the projects list
      queryClient.invalidateQueries({ queryKey: ['projectList'] })
    },
  })
}

/**
 * Update an existing project
 * @see api/app/Http/Controllers/Api/ProjectController.php::update()
 */
export function useUpdateProjectMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<IResponse<Project>, AxiosError, { projectId: number, data: UpdateProjectRequest }>({
    mutationFn: async ({ projectId, data }): Promise<IResponse<Project>> => {
      const response = await axiosInstance.put(`/api/project/${projectId}`, data)
      return response.data
    },
    onSuccess: (_, variables) => {
      // Invalidate project list query to refresh the projects list
      queryClient.invalidateQueries({ queryKey: ['projectList'] })
      // Invalidate the specific project query to refresh the detail page
      queryClient.invalidateQueries({ queryKey: ['project', variables.projectId] })
    },
  })
}

/**
 * Delete a project
 * @see api/app/Http/Controllers/Api/ProjectController.php::destroy()
 */
export function useDeleteProjectMutation() {
  const { axiosInstance } = useAxios()
  const queryClient = useQueryClient()

  return useMutation<void, AxiosError, number>({
    mutationFn: async (projectId: number): Promise<void> => {
      await axiosInstance.delete(`/api/project/${projectId}`)
    },
    onSuccess: () => {
      // Invalidate project list query to refresh the projects list
      queryClient.invalidateQueries({ queryKey: ['projectList'] })
    },
  })
}

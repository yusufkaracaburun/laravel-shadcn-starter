import { computed, ref, watch } from 'vue'

import type { TPageSize } from '@/components/data-table/types'
import type { ISorting } from '@/services/query-utils'
import type {
  IPaginatedResponse,
  IResponse,
} from '@/services/types/response.type'

import { DEFAULT_PAGE, DEFAULT_PAGE_SIZE } from '@/components/data-table/types'
import { useToast } from '@/composables/use-toast'
import { useErrorStore } from '@/stores/error.store'

export interface ResourceService<
  TEntity,
  TFilter,
  TCreateRequest,
  TUpdateRequest,
  TPrerequisites,
> {
  getPrerequisitesQuery: () => ReturnType<
    typeof import('@tanstack/vue-query').useQuery<
      TPrerequisites,
      import('axios').AxiosError
    >
  >
  getListQuery: (
    page: Ref<number>,
    per_page: Ref<TPageSize>,
    sort: Ref<ISorting>,
    filter: Ref<TFilter>,
    include: Ref<string[]>,
  ) => ReturnType<
    typeof import('@tanstack/vue-query').useQuery<
      IPaginatedResponse<TEntity>,
      import('axios').AxiosError
    >
  >
  createMutation: () => ReturnType<
    typeof import('@tanstack/vue-query').useMutation<
      IResponse<TEntity>,
      import('axios').AxiosError,
      TCreateRequest
    >
  >
  updateMutation: () => ReturnType<
    typeof import('@tanstack/vue-query').useMutation<
      IResponse<TEntity>,
      import('axios').AxiosError,
      { id: number; data: TUpdateRequest }
    >
  >
  deleteMutation: () => ReturnType<
    typeof import('@tanstack/vue-query').useMutation<
      void,
      import('axios').AxiosError,
      number
    >
  >
  getMutation: () => ReturnType<
    typeof import('@tanstack/vue-query').useMutation<
      IResponse<TEntity>,
      import('axios').AxiosError,
      { id: number; includes?: string[] }
    >
  >
}

export interface ResourceConfig<
  TEntity,
  TFilter,
  TCreateRequest,
  TUpdateRequest,
  TPrerequisites,
> {
  service: ResourceService<
    TEntity,
    TFilter,
    TCreateRequest,
    TUpdateRequest,
    TPrerequisites
  >
  context: Record<string, string>
  messages: Record<string, string>
  defaultSort: ISorting
  includes: Record<string, string>
  defaultIncludeKey: string
  onFetchList?: (refetch: () => Promise<any>) => void
}

export function useResourceBase<
  TEntity,
  TFilter extends Record<string, any>,
  TCreateRequest,
  TUpdateRequest,
  TPrerequisites,
>(
  config: ResourceConfig<
    TEntity,
    TFilter,
    TCreateRequest,
    TUpdateRequest,
    TPrerequisites
  >,
) {
  const toast = useToast()
  const errorStore = useErrorStore()

  const page = ref<number>(DEFAULT_PAGE)
  const pageSize = ref<TPageSize>(DEFAULT_PAGE_SIZE)
  const sort = ref<ISorting>(config.defaultSort)
  const filter = ref<TFilter>({} as TFilter)

  function onSortingChange(newSorting: ISorting): void {
    sort.value = newSorting
    page.value = DEFAULT_PAGE
  }

  function onFiltersChange(newFilters: TFilter): void {
    filter.value = newFilters
    page.value = DEFAULT_PAGE
  }

  function clearFilters() {
    filter.value = {} as TFilter
    page.value = DEFAULT_PAGE
  }

  function onPageChange(newPage: number): void {
    page.value = newPage
  }

  function onPageSizeChange(newPageSize: TPageSize): void {
    page.value = DEFAULT_PAGE
    pageSize.value = newPageSize
  }

  const getPrerequisitesQuery = config.service.getPrerequisitesQuery()
  const {
    data: prerequisitesResponse,
    isLoading: isLoadingPrerequisites,
    isError: isErrorPrerequisites,
    error: errorPrerequisites,
    refetch: refetchPrerequisites,
  } = getPrerequisitesQuery

  async function fetchPrerequisitesData(): Promise<TPrerequisites> {
    try {
      const response = await refetchPrerequisites()
      return response.data as TPrerequisites
    } catch (error: any) {
      errorStore.setError(error, {
        context: config.context.FETCH_PREREQUISITES || 'fetchPrerequisites',
      })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const getListQuery = config.service.getListQuery(
    page,
    pageSize,
    sort,
    filter as Ref<TFilter>,
    ref([config.includes[config.defaultIncludeKey]]),
  )
  const {
    data: listData,
    isLoading,
    isFetching,
    refetch: fetchList,
  } = getListQuery

  const items = computed(() => listData.value?.data.data ?? [])

  async function fetchListData(): Promise<IPaginatedResponse<TEntity>> {
    try {
      const response = await fetchList()
      return response.data as IPaginatedResponse<TEntity>
    } catch (error: any) {
      errorStore.setError(error, {
        context: config.context.FETCH_LIST || 'fetchList',
      })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const createMutation = config.service.createMutation()
  async function create(data: TCreateRequest) {
    try {
      const response = await createMutation.mutateAsync(data)
      toast.showSuccess(
        config.messages.CREATE_SUCCESS || 'Created successfully!',
      )
      return response
    } catch (error: any) {
      errorStore.setError(error, {
        context: config.context.CREATE || 'create',
      })
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
        toast.showError(message)
      }
      throw error
    }
  }

  const updateMutation = config.service.updateMutation()
  async function update(id: number, data: TUpdateRequest) {
    try {
      const response = await updateMutation.mutateAsync({ id, data })
      toast.showSuccess(
        config.messages.UPDATE_SUCCESS || 'Updated successfully!',
      )
      return response
    } catch (error: any) {
      errorStore.setError(error, {
        context: config.context.UPDATE || 'update',
      })
      const message = errorStore.getErrorMessage(error)
      const validationErrors = errorStore.getValidationErrors(error)
      if (Object.keys(validationErrors).length > 0) {
        const firstError = Object.values(validationErrors)[0]?.[0]
        toast.showError(firstError || message)
      } else {
        toast.showError(message)
      }
      throw error
    }
  }

  const deleteMutation = config.service.deleteMutation()
  async function deleteItem(id: number) {
    try {
      await deleteMutation.mutateAsync(id)
      toast.showSuccess(
        config.messages.DELETE_SUCCESS || 'Deleted successfully!',
      )
    } catch (error: any) {
      errorStore.setError(error, {
        context: config.context.DELETE || 'delete',
      })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const getMutation = config.service.getMutation()
  async function get(id: number, includes?: string[]) {
    try {
      const response = await getMutation.mutateAsync({ id, includes })
      return response.data
    } catch (error: any) {
      errorStore.setError(error, {
        context: config.context.GET || 'get',
      })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  const loading = computed(() => isLoading.value || isFetching.value)

  // Mutation loading states
  const isCreating = computed(() => createMutation.isPending.value)
  const isUpdating = computed(() => updateMutation.isPending.value)
  const isDeleting = computed(() => deleteMutation.isPending.value)
  const isGetting = computed(() => getMutation.isPending.value)

  const serverPagination = computed(() => {
    const response = listData.value?.data
    return {
      page: response ? response.current_page : page.value,
      pageSize: pageSize.value,
      total: response ? response.total : 0,
      onPageChange,
      onPageSizeChange,
    }
  })

  if (config.onFetchList) {
    watch(
      [page, pageSize],
      ([newPage, newPageSize], [oldPage, oldPageSize]) => {
        if (oldPage !== newPage || oldPageSize !== newPageSize) {
          config.onFetchList?.(fetchList)
        }
      },
    )
  }

  return {
    // State
    page,
    pageSize,
    sort,
    filter,
    includes: config.includes,

    // Handlers
    onSortingChange,
    onFiltersChange,
    clearFilters,
    onPageChange,
    onPageSizeChange,

    // Prerequisites
    prerequisitesResponse,
    isLoadingPrerequisites,
    isErrorPrerequisites,
    errorPrerequisites,
    fetchPrerequisitesData,

    // List
    items,
    fetchListData,
    loading,
    serverPagination,

    // Mutations
    create,
    update,
    deleteItem,
    get,
    isCreating,
    isUpdating,
    isDeleting,
    isGetting,
  }
}

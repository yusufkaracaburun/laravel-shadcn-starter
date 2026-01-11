import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

import type {
  ICreateTimesheetRequest,
  IUpdateTimesheetRequest,
  ITimesheet,
  ITimesheetFilters,
  ITimesheetPrerequisites,
} from '@/pages/timesheets/models/timesheets'
import type { IResponse } from '@/services/types/response.type'

import { useResourceBase } from '@/composables/use-resource-base.composable'
import { useToast } from '@/composables/use-toast.composable'
import { useTimesheetsService } from '@/pages/timesheets/services/timesheets.service'
import { useErrorStore } from '@/stores/error.store'

const TimesheetContext = {
  FETCH_PREREQUISITES: 'fetchTimesheetPrerequisites',
  FETCH_LIST: 'fetchTimesheets',
  GET_TIMESHEET: 'getTimesheet',
  GET_TIMESHEET_BY_ID: 'getTimesheetById',
  CREATE: 'createTimesheet',
  UPDATE: 'updateTimesheet',
  DELETE: 'deleteTimesheet',
}

const TimesheetMessages = {
  CREATE_SUCCESS: 'Timesheet created successfully!',
  UPDATE_SUCCESS: 'Timesheet updated successfully!',
  DELETE_SUCCESS: 'Timesheet deleted successfully!',
}

export function useTimesheets() {
  const toast = useToast()
  const errorStore = useErrorStore()
  const timesheetsService = useTimesheetsService()
  const route = useRoute()

  const includes = {}

  const base = useResourceBase<
    ITimesheet,
    ITimesheetFilters,
    ICreateTimesheetRequest,
    IUpdateTimesheetRequest,
    ITimesheetPrerequisites
  >({
    service: {
      getPrerequisitesQuery: () => timesheetsService.getTimesheetPrerequisitesQuery(),
      getListQuery: (page, per_page, sort, filter, include) =>
        timesheetsService.getTimesheetsQuery(page, per_page, sort, filter, include),
      createMutation: () => timesheetsService.createTimesheetMutation(),
      updateMutation: () => timesheetsService.updateTimesheetMutation(),
      deleteMutation: () => timesheetsService.deleteTimesheetMutation(),
      getMutation: () => timesheetsService.getTimesheetMutation(),
    },
    context: TimesheetContext,
    messages: TimesheetMessages,
    defaultSort: { id: 'created_at', desc: true },
    includes: { default: '' },
    defaultIncludeKey: 'default',
    onFetchList: (refetch) => {
      refetch()
    },
  })

  const timesheetId = computed(() => {
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

  const getTimesheetByIdQuery = timesheetsService.getTimesheetByIdQuery(
    timesheetId,
    ref([]),
  )
  const {
    data: timesheetByIdResponse,
    isLoading: isLoadingTimesheetById,
    isError: isErrorTimesheetById,
    error: errorTimesheetById,
    refetch: refetchTimesheetById,
  } = getTimesheetByIdQuery

  async function fetchTimesheetByIdData(): Promise<IResponse<ITimesheet>> {
    try {
      const response = await refetchTimesheetById()
      return response.data as IResponse<ITimesheet>
    } catch (error: any) {
      errorStore.setError(error, { context: TimesheetContext.GET_TIMESHEET_BY_ID })
      const message = errorStore.getErrorMessage(error)
      toast.showError(message)
      throw error
    }
  }

  /**
   * Get initial form values for timesheet form
   * @param timesheet - Optional timesheet object for edit mode
   * @returns Initial values object for vee-validate form
   */
  function getTimesheetFormInitialValues(timesheet?: ITimesheet | null) {
    return {
      user_id: timesheet?.user_id || 0,
      project_id: timesheet?.project_id || null,
      task_id: timesheet?.task_id || null,
      date: timesheet?.date || new Date().toISOString().split('T')[0],
      hours: timesheet?.hours || 0,
      description: timesheet?.description || null,
      status: timesheet?.status || null,
    }
  }

  return {
    sort: base.sort,
    filter: base.filter,
    includes: base.includes,
    timesheets: base.items,
    onSortingChange: base.onSortingChange,
    onFiltersChange: base.onFiltersChange,
    clearFilters: base.clearFilters,
    onPageChange: base.onPageChange,
    onPageSizeChange: base.onPageSizeChange,
    timesheetPrerequisitesResponse: base.prerequisitesResponse,
    isLoadingTimesheetPrerequisites: base.isLoadingPrerequisites,
    isErrorTimesheetPrerequisites: base.isErrorPrerequisites,
    errorTimesheetPrerequisites: base.errorPrerequisites,
    fetchTimesheetPrerequisitesData: base.fetchPrerequisitesData,
    fetchTimesheetsData: base.fetchListData,
    getTimesheet: base.get,
    createTimesheet: base.create,
    updateTimesheet: base.update,
    deleteTimesheet: base.deleteItem,
    isCreating: base.isCreating,
    isUpdating: base.isUpdating,
    isDeleting: base.isDeleting,
    loading: base.loading,
    serverPagination: base.serverPagination,
    timesheetId,
    timesheetByIdResponse,
    isLoadingTimesheetById,
    isErrorTimesheetById,
    errorTimesheetById,
    fetchTimesheetByIdData,
    getTimesheetFormInitialValues,
  }
}

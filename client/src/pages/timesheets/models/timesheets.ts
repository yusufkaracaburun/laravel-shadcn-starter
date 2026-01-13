import type { IStatus } from '@/services/types/response.type'

/**
 * Timesheet interface matching backend TimesheetResource exactly
 * @see api/app/Http/Resources/TimesheetResource.php
 */
export interface ITimesheet {
  id: number
  user_id: number
  project_id: number | null
  task_id: number | null
  date: string
  hours: number
  description: string | null
  status: string | null
  created_at: string
  updated_at: string
}

export interface ITimesheetFilters {
  user_id?: number
  project_id?: number
  task_id?: number
  date?: string
  status?: string
  search?: string
  between?: string
}

export interface ICreateTimesheetRequest {
  user_id: number
  project_id?: number | null
  task_id?: number | null
  date: string
  hours: number
  description?: string | null
  status?: string | null
}

export interface IUpdateTimesheetRequest {
  id: number
  user_id?: number
  project_id?: number | null
  task_id?: number | null
  date?: string
  hours?: number
  description?: string | null
  status?: string | null
}

export interface ITimesheetPrerequisites {
  users?: Array<{ id: number; name: string; email: string }>
  projects?: Array<{ id: number; name: string }>
  tasks?: Array<{ id: number; name: string }>
  statuses?: IStatus[]
}

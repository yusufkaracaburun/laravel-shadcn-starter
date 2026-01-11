import type { IStatus } from '@/services/types/response.type'

/**
 * Team interface matching backend TeamResource exactly
 * @see api/app/Http/Resources/TeamResource.php
 */
export interface ITeam {
  id: number
  name: string
  personal_team: boolean
  user_id: number
  created_at: string
  updated_at: string
}

export interface ITeamFilters {
  name?: string
  personal_team?: boolean
  user_id?: number
  search?: string
  between?: string
}

export interface ICreateTeamRequest {
  name: string
  personal_team?: boolean | null
  user_id?: number | null
}

export interface IUpdateTeamRequest {
  id: number
  name?: string
  personal_team?: boolean | null
  user_id?: number | null
}

export interface ITeamPrerequisites {
  users?: Array<{ id: number; name: string; email: string }>
  statuses?: IStatus[]
}

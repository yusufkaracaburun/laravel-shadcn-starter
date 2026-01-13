import type { IStatus } from '@/services/types/response.type'

/**
 * Equipment interface matching backend EquipmentResource exactly
 * @see api/app/Http/Resources/EquipmentResource.php
 */
export interface IEquipment {
  id: number
  name: string
  type: string | null
  model: string | null
  serial_number: string | null
  status: string | null
  created_at: string
  updated_at: string
}

export interface IEquipmentFilters {
  name?: string
  type?: string
  model?: string
  serial_number?: string
  status?: string
  search?: string
  between?: string
}

export interface ICreateEquipmentRequest {
  name: string
  type?: string | null
  model?: string | null
  serial_number?: string | null
  status?: string | null
}

export interface IUpdateEquipmentRequest {
  id: number
  name?: string
  type?: string | null
  model?: string | null
  serial_number?: string | null
  status?: string | null
}

export interface IEquipmentPrerequisites {
  types?: string[]
  models?: string[]
  statuses?: IStatus[]
}

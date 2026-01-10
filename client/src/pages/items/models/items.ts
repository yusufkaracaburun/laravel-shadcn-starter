import type { IStatus } from '@/services/types/response.type'

/**
 * Money object structure from Money library
 */
export interface IMoney {
  amount: string
  currency: string
  formatted: string
}

/**
 * Item interface matching backend ItemResource exactly
 * @see api/app/Http/Resources/ItemResource.php
 */
export interface IItem {
  id: number
  name: string
  description: string | null
  unit_price: IMoney | number // Can be Money object or number (for backward compatibility)
  vat_rate: number
  unit: string | null
  created_at: string
  updated_at: string
}

/**
 * Item filters interface matching backend filter structure
 */
export interface IItemFilters {
  name?: string
  description?: string
  unit?: string
  unit_price?: number
  vat_rate?: number
  created_at?: string
  updated_at?: string
  search?: string
  between?: string // Format: "YYYY-MM-DD,YYYY-MM-DD"
}

/**
 * Create item request interface matching backend validation
 * @see api/app/Http/Requests/Items/StoreItemRequest.php
 */
export interface ICreateItemRequest {
  name: string
  description?: string | null
  unit_price: number
  vat_rate: number
  unit?: string | null
}

/**
 * Update item request interface matching backend validation
 * @see api/app/Http/Requests/Items/UpdateItemRequest.php
 */
export interface IUpdateItemRequest {
  name?: string
  description?: string | null
  unit_price?: number
  vat_rate?: number
  unit?: string | null
}

/**
 * Item prerequisites interface (empty for items as they don't have prerequisites)
 */
export interface IItemPrerequisites {
  // Items don't have prerequisites like users have roles
}

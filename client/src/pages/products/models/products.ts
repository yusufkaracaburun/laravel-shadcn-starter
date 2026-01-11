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
 * Product interface matching backend ProductResource exactly
 * @see api/app/Http/Resources/ProductResource.php
 */
export interface IProduct {
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
 * Product filters interface matching backend filter structure
 */
export interface IProductFilters {
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
 * Create product request interface matching backend validation
 * @see api/app/Http/Requests/Products/StoreProductRequest.php
 */
export interface ICreateProductRequest {
  name: string
  description?: string | null
  unit_price: number
  vat_rate: number
  unit?: string | null
}

/**
 * Update product request interface matching backend validation
 * @see api/app/Http/Requests/Products/UpdateProductRequest.php
 */
export interface IUpdateProductRequest {
  name?: string
  description?: string | null
  unit_price?: number
  vat_rate?: number
  unit?: string | null
}

/**
 * Product prerequisites interface (empty for products as they don't have prerequisites)
 */
export interface IProductPrerequisites {
  // Products don't have prerequisites like users have roles
}

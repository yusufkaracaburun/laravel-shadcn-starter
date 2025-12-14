/**
 * Money object structure from backend
 */
export interface Money {
  amount: string
  currency: string
  formatted: string
}

/**
 * Item types matching backend ItemResource
 */
export interface Item {
  id: number
  name: string
  description: string | null
  unit_price: Money | number
  vat_rate: number
  unit: string | null
  created_at: string
  updated_at: string
}

/**
 * Paginated items response
 */
export interface PaginatedItemsResponse {
  data: Item[]
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
 * Create item request
 */
export interface CreateItemRequest {
  name: string
  description?: string | null
  unit_price: number
  vat_rate: number
  unit?: string | null
}

/**
 * Update item request
 */
export interface UpdateItemRequest {
  name?: string
  description?: string | null
  unit_price?: number
  vat_rate?: number
  unit?: string | null
}


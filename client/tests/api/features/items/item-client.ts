import type { APIResponse } from '@playwright/test'

import type { IResponse } from '../shared/types'
import type {
  CreateItemRequest,
  Item,
  PaginatedItemsResponse,
  UpdateItemRequest,
} from './item-types'

import { BaseClient } from '../shared/core/base-client'

export class ItemClient extends BaseClient {
  /**
   * Get paginated list of items
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns raw APIResponse
   */
  async getItems(page: number = 1, perPage: number = 15): Promise<APIResponse> {
    const endpoint = `/api/items?page=${page}&per_page=${perPage}`
    return this.get(endpoint)
  }

  /**
   * Get paginated list of items with typed response
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns IResponse<PaginatedItemsResponse>
   */
  async getItemsTyped(
    page: number = 1,
    perPage: number = 15,
  ): Promise<IResponse<PaginatedItemsResponse>> {
    const response = await this.getItems(page, perPage)
    return response.json() as Promise<IResponse<PaginatedItemsResponse>>
  }

  /**
   * Get a specific item by ID
   * @param itemId - Item ID
   * Returns raw APIResponse
   */
  async getItem(itemId: number): Promise<APIResponse> {
    const endpoint = `/api/items/${itemId}`
    return this.get(endpoint)
  }

  /**
   * Get a specific item by ID with typed response
   * @param itemId - Item ID
   * Returns IResponse<Item>
   */
  async getItemTyped(itemId: number): Promise<IResponse<Item>> {
    const response = await this.getItem(itemId)
    return response.json() as Promise<IResponse<Item>>
  }

  /**
   * Create a new item
   * @param data - Item creation data
   * Returns raw APIResponse
   */
  async createItem(data: CreateItemRequest): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    return this.post('/api/items', data)
  }

  /**
   * Create a new item with typed response
   * @param data - Item creation data
   * Returns IResponse<Item>
   */
  async createItemTyped(data: CreateItemRequest): Promise<IResponse<Item>> {
    const response = await this.createItem(data)
    return response.json() as Promise<IResponse<Item>>
  }

  /**
   * Update an existing item
   * @param itemId - Item ID
   * @param data - Item update data
   * Returns raw APIResponse
   */
  async updateItem(
    itemId: number,
    data: UpdateItemRequest,
  ): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `/api/items/${itemId}`
    return this.put(endpoint, data)
  }

  /**
   * Update an existing item with typed response
   * @param itemId - Item ID
   * @param data - Item update data
   * Returns IResponse<Item>
   */
  async updateItemTyped(
    itemId: number,
    data: UpdateItemRequest,
  ): Promise<IResponse<Item>> {
    const response = await this.updateItem(itemId, data)
    return response.json() as Promise<IResponse<Item>>
  }

  /**
   * Delete an item
   * @param itemId - Item ID
   * Returns raw APIResponse
   */
  async deleteItem(itemId: number): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `/api/items/${itemId}`
    return this.delete(endpoint)
  }

  /**
   * Copy authentication state from another client
   * Public wrapper for protected BaseClient method
   */
  public copyAuthStateFrom(other: BaseClient): void {
    // Access protected members through type assertion
    // This allows tests to copy auth state between clients
    const thisClient = this as any
    const otherClient = other as any
    thisClient.cookieHandler.setCookies(otherClient.cookieHandler.getCookies())
    thisClient.csrfHandler.setToken(otherClient.csrfHandler.getToken())
  }
}
